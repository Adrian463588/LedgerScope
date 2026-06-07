<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\DocumentRequest;
use App\Models\Engagement;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;

/**
 * DocumentRequestService — PBC (Prepared-by-Client) Portal lifecycle.
 *
 * PRD §6.15 | EPIC 5
 */
final class DocumentRequestService
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Create a new document request (status = requested or draft).
     */
    public function create(array $data, Engagement $engagement, User $requestedBy): DocumentRequest
    {
        return DB::transaction(function () use ($data, $engagement, $requestedBy): DocumentRequest {
            /** @var DocumentRequest $request */
            $request = DocumentRequest::create(array_merge($data, [
                'engagement_id' => $engagement->id,
                'company_id' => $engagement->company_id,
                'requested_by' => $requestedBy->id,
                'status' => $data['status'] ?? 'requested',
            ]));

            if ($request->status === 'requested') {
                $dueStr = $request->due_date ? $request->due_date->toDateString() : 'N/A';
                $this->notifyClients($request, 'New Document Request', "A new document request '{$request->title}' has been created for engagement '{$engagement->name}'. Due date: {$dueStr}.");
            }

            return $request;
        });
    }

    /**
     * Set status to in_progress (requested -> in_progress).
     */
    public function startWork(DocumentRequest $request): void
    {
        if ($request->status === 'requested') {
            DB::transaction(fn () => $request->update(['status' => 'in_progress']));
        }
    }

    /**
     * Submit evidence against a document request.
     */
    public function submit(DocumentRequest $request, int $evidenceFileId, User $by): void
    {
        if (! in_array($request->status, ['requested', 'in_progress', 'rejected'], true)) {
            throw new \DomainException("Request is [{$request->status}] — only requested, in_progress, or rejected requests can be submitted.");
        }

        DB::transaction(function () use ($request, $evidenceFileId, $by): void {
            $request->update(['status' => 'under_review', 'evidence_file_id' => $evidenceFileId]);
            $engagement = $request->engagement;
            if ($engagement) {
                $this->notificationService->notifyMany(
                    $this->getAuditorsToNotify($request),
                    'Document Request Submitted',
                    "Evidence has been submitted for '{$request->title}' under engagement '{$engagement->name}' by {$by->name}.",
                    'document_request',
                    "/engagements/{$engagement->id}/document-requests"
                );
            }
        });
    }

    /**
     * Accept a submitted document request (under_review -> accepted).
     */
    public function accept(DocumentRequest $request, User $by): void
    {
        if ($request->status !== 'under_review') {
            throw new \DomainException('Only under_review requests can be accepted.');
        }

        DB::transaction(function () use ($request, $by): void {
            $request->update(['status' => 'accepted']);
            $this->notifyClients($request, 'Document Request Accepted', "The evidence submitted for '{$request->title}' has been accepted by {$by->name}.");
        });
    }

    /**
     * Reject a document request with a required reason.
     */
    public function reject(DocumentRequest $request, User $by, string $reason): void
    {
        if (trim($reason) === '') {
            throw new \DomainException('A rejection reason is required.');
        }
        if (! in_array($request->status, ['under_review', 'requested', 'in_progress'], true)) {
            throw new \DomainException('Only requested, in_progress, or under_review requests can be rejected.');
        }

        DB::transaction(function () use ($request, $reason, $by): void {
            $request->update(['status' => 'rejected', 'rejection_reason' => $reason]);
            $this->notifyClients($request, 'Document Request Rejected', "The evidence submitted for '{$request->title}' has been rejected by {$by->name}. Reason: {$reason}");
        });
    }

    /**
     * Mark overdue document requests — called by scheduler.
     */
    public function markOverdue(): int
    {
        $overdueRequests = DocumentRequest::query()
            ->whereIn('status', ['requested', 'in_progress'])
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        $count = 0;
        foreach ($overdueRequests as $request) {
            DB::transaction(fn () => $request->update(['status' => 'overdue']));
            $engagement = $request->engagement;
            if ($engagement) {
                $dueStr = $request->due_date ? $request->due_date->toDateString() : 'N/A';
                $this->notifyClients($request, 'Document Request Overdue', "The document request '{$request->title}' is overdue (due: {$dueStr}). Please upload the requested file.");

                $this->notificationService->notifyMany(
                    $this->getAuditorsToNotify($request),
                    'Document Request Overdue',
                    "The document request '{$request->title}' for engagement '{$engagement->name}' is now overdue.",
                    'document_request',
                    "/engagements/{$engagement->id}/document-requests"
                );
            }
            $count++;
        }
        return $count;
    }

    /**
     * Get all audit team members to notify.
     *
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function getAuditorsToNotify(DocumentRequest $request): \Illuminate\Support\Collection
    {
        $users = [];
        if ($request->requestedBy) {
            $users[] = $request->requestedBy;
        }
        $eng = $request->engagement;
        if ($eng) {
            $ids = array_filter([$eng->lead_auditor_id, $eng->manager_id, $eng->partner_id]);
            foreach (User::whereIn('id', $ids)->get() as $user) {
                $users[] = $user;
            }
            foreach ($eng->members()->with('user')->get() as $member) {
                if ($member->user) {
                    $users[] = $member->user;
                }
            }
        }
        return collect($users)->unique('id');
    }

    /**
     * Helper to notify client users for a request.
     */
    private function notifyClients(DocumentRequest $request, string $title, string $message): void
    {
        $users = [];
        if ($request->assignedTo) {
            $users[] = $request->assignedTo;
        }
        if ($request->engagement && $request->engagement->company) {
            foreach ($request->engagement->company->users as $user) {
                $users[] = $user;
            }
        }
        $clients = collect($users)->unique('id');

        if ($clients->isNotEmpty()) {
            $this->notificationService->notifyMany($clients, $title, $message, 'document_request', '/client/evidence');
        }
    }
}
