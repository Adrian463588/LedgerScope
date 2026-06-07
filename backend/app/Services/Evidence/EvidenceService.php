<?php

declare(strict_types=1);

namespace App\Services\Evidence;

use App\Enums\Common\EvidenceStatus;
use App\Models\Engagement;
use App\Models\EvidenceFile;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
        'image/gif',
        'text/plain',
        'application/zip',
    ];

    private const MAX_FILE_SIZE_BYTES = 52_428_800; // 50 MB

    public function __construct(
        private readonly NotificationService $notificationService
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
        // Validate file
        if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
            throw new \DomainException('File exceeds maximum allowed size of 50 MB.');
        }

        if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new \DomainException("File type [{$file->getMimeType()}] is not allowed.");
        }

        return DB::transaction(function () use ($file, $engagement, $uploadedBy, $description): EvidenceFile {
            // Store in private disk — path: evidence/{engagement_id}/{uuid}.ext
            $path = $file->storeAs(
                "evidence/{$engagement->id}",
                Str::uuid().'.'.$file->getClientOriginalExtension(),
                'private',
            );

            /** @var EvidenceFile $evidence */
            $evidence = EvidenceFile::create([
                'engagement_id' => $engagement->id,
                'uploaded_by' => $uploadedBy->id,
                'original_name' => $file->getClientOriginalName(),
                'storage_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size_bytes' => $file->getSize(),
                'checksum' => hash_file('sha256', $file->getRealPath()),
                'status' => EvidenceStatus::Pending->value,
                'description' => $description,
            ]);

            return $evidence;
        });
    }

    /**
     * Accept an evidence file.
     */
    public function accept(EvidenceFile $evidence, User $acceptedBy): void
    {
        if ($evidence->status === EvidenceStatus::Accepted->value) {
            throw new \DomainException('Evidence is already accepted.');
        }

        DB::transaction(function () use ($evidence, $acceptedBy): void {
            $evidence->update([
                'status' => EvidenceStatus::Accepted->value,
                'accepted_by' => $acceptedBy->id,
                'accepted_at' => now(),
            ]);

            event(new \App\Events\Evidence\EvidenceAccepted(
                $acceptedBy->id,
                'evidence.accept',
                $evidence->engagement->company_id,
                'EvidenceFile',
                $evidence->id
            ));

            if ($evidence->uploadedBy) {
                $this->notificationService->notify(
                    $evidence->uploadedBy,
                    'Evidence File Accepted',
                    "The evidence file '{$evidence->original_name}' has been accepted by {$acceptedBy->name}.",
                    'evidence',
                    '/client/evidence'
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

        if ($evidence->status === EvidenceStatus::Rejected->value) {
            throw new \DomainException('Evidence is already rejected.');
        }

        DB::transaction(function () use ($evidence, $rejectedBy, $reason): void {
            $evidence->update([
                'status' => EvidenceStatus::Rejected->value,
                'rejected_by' => $rejectedBy->id,
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            event(new \App\Events\Evidence\EvidenceRejected(
                $rejectedBy->id,
                'evidence.reject',
                $evidence->engagement->company_id,
                'EvidenceFile',
                $evidence->id,
                null,
                ['rejection_reason' => $reason]
            ));

            if ($evidence->uploadedBy) {
                $this->notificationService->notify(
                    $evidence->uploadedBy,
                    'Evidence File Rejected',
                    "The evidence file '{$evidence->original_name}' has been rejected by {$rejectedBy->name}. Reason: {$reason}",
                    'evidence',
                    '/client/evidence'
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
        if ($evidence->status === EvidenceStatus::Accepted->value) {
            throw new \DomainException('Cannot delete accepted evidence.');
        }

        $evidence->delete();
    }
}
