<?php

declare(strict_types=1);

namespace App\Services\Evidence;

use App\Enums\Common\EvidenceStatus;
use App\Events\AuditActionRecorded;
use App\Events\Evidence\EvidenceAccepted;
use App\Events\Evidence\EvidenceRejected;
use App\Models\Engagement;
use App\Models\EvidenceFile;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * EvidenceService — manages evidence file lifecycle.
 *
 * PRD §6.16 | AGENTS_BACKEND §4
 *
 * Rules:
 * - Files stored in private disk — never public (AGENTS_BACKEND Rule 5)
 * - Downloads via signed temporary URL (15-minute expiry)
 * - status: pending → accepted | rejected
 */
final class EvidenceService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Upload and store an evidence file in private storage.
     */
    public function upload(
        UploadedFile $file,
        Engagement $engagement,
        User $uploadedBy,
        ?string $description = null,
    ): EvidenceFile {
        $size = $file->getSize();
        $maxSizeMb = (int) config('ledgerscope.max_file_size_mb', 50);
        if ($size === false || $size === null || $size > $maxSizeMb * 1_048_576) {
            throw new \DomainException("File exceeds maximum allowed size of {$maxSizeMb} MB.");
        }

        $mimeType = (string) $file->getMimeType();
        $allowedMimeTypes = config('ledgerscope.allowed_mime_types', []);
        if (! is_array($allowedMimeTypes) || ! in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \DomainException("File type [{$file->getMimeType()}] is not allowed.");
        }

        $directory = str_replace(
            ['{company_id}', '{engagement_id}'],
            [(string) $engagement->company_id, (string) $engagement->id],
            (string) config('ledgerscope.storage.evidence_path', 'evidence/{engagement_id}'),
        );
        $path = $file->storeAs(
            $directory,
            Str::uuid().'.'.$file->getClientOriginalExtension(),
            'private',
        );

        if ($path === false) {
            throw new \RuntimeException('Evidence file could not be stored.');
        }

        try {
            return DB::transaction(function () use ($file, $engagement, $uploadedBy, $description, $path, $mimeType, $size): EvidenceFile {

                /** @var EvidenceFile $evidence */
                $evidence = EvidenceFile::create([
                    'engagement_id' => $engagement->id,
                    'uploaded_by' => $uploadedBy->id,
                    'original_name' => $file->getClientOriginalName(),
                    'storage_path' => $path,
                    'mime_type' => $mimeType,
                    'file_size_bytes' => $size,
                    'checksum' => hash_file('sha256', $file->getRealPath()),
                    'status' => EvidenceStatus::Pending->value,
                    'description' => $description,
                ]);

                event(new AuditActionRecorded(
                    userId: $uploadedBy->id,
                    action: 'evidence.upload',
                    companyId: $engagement->company_id,
                    objectType: 'EvidenceFile',
                    objectId: $evidence->id,
                    after: $evidence->toArray(),
                ));

                return $evidence;
            });
        } catch (Throwable $exception) {
            Storage::disk('private')->delete($path);

            throw $exception;
        }
    }

    /**
     * Accept an evidence file.
     */
    public function accept(EvidenceFile $evidence, User $acceptedBy): void
    {
        if ($evidence->status === EvidenceStatus::Accepted) {
            throw new \DomainException('Evidence is already accepted.');
        }

        DB::transaction(function () use ($evidence, $acceptedBy): void {
            $evidence->update([
                'status' => EvidenceStatus::Accepted->value,
                'accepted_by' => $acceptedBy->id,
                'accepted_at' => now(),
            ]);

            event(new EvidenceAccepted(
                $acceptedBy->id,
                'evidence.accept',
                $evidence->engagement->company_id,
                'EvidenceFile',
                $evidence->id,
            ));

            if ($evidence->uploadedBy) {
                $this->notificationService->notify(
                    $evidence->uploadedBy,
                    'Evidence File Accepted',
                    "The evidence file '{$evidence->original_name}' has been accepted by {$acceptedBy->name}.",
                    'evidence',
                    '/client/evidence',
                );
            }
        });
    }

    /**
     * Reject an evidence file with a required reason.
     */
    public function reject(EvidenceFile $evidence, User $rejectedBy, string $reason): void
    {
        if (trim($reason) === '') {
            throw new \DomainException('A rejection reason is required.');
        }

        if ($evidence->status === EvidenceStatus::Rejected) {
            throw new \DomainException('Evidence is already rejected.');
        }

        DB::transaction(function () use ($evidence, $rejectedBy, $reason): void {
            $evidence->update([
                'status' => EvidenceStatus::Rejected->value,
                'rejected_by' => $rejectedBy->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            event(new EvidenceRejected(
                $rejectedBy->id,
                'evidence.reject',
                $evidence->engagement->company_id,
                'EvidenceFile',
                $evidence->id,
                null,
                ['rejection_reason' => $reason],
            ));

            if ($evidence->uploadedBy) {
                $this->notificationService->notify(
                    $evidence->uploadedBy,
                    'Evidence File Rejected',
                    "The evidence file '{$evidence->original_name}' has been rejected by {$rejectedBy->name}. Reason: {$reason}",
                    'evidence',
                    '/client/evidence',
                );
            }
        });
    }

    /**
     * Generate a signed temporary URL for downloading evidence.
     * Expiry: 15 minutes (AGENTS_BACKEND Rule 5)
     *
     * @return array{url: string, expires_at: string}
     */
    public function getDownloadUrl(EvidenceFile $evidence): array
    {
        $url = Storage::disk('private')->temporaryUrl(
            $evidence->storage_path,
            now()->addMinutes(15),
        );

        return [
            'url' => $url,
            'expires_at' => now()->addMinutes(15)->toISOString(),
        ];
    }

    /**
     * Soft-delete an evidence file.
     */
    public function delete(EvidenceFile $evidence, User $deletedBy): void
    {
        if ($evidence->status === EvidenceStatus::Accepted) {
            throw new \DomainException('Cannot delete accepted evidence.');
        }

        DB::transaction(function () use ($evidence, $deletedBy): void {
            $evidence->delete();

            event(new AuditActionRecorded(
                userId: $deletedBy->id,
                action: 'evidence.delete',
                companyId: $evidence->engagement->company_id,
                objectType: 'EvidenceFile',
                objectId: $evidence->id,
            ));
        });
    }
}
