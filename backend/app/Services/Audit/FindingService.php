<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Enums\Audit\FindingSeverity;
use App\Enums\Audit\FindingStatus;
use App\Events\Audit\FindingStatusChanged;
use App\Models\Engagement;
use App\Models\Finding;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;

final class FindingService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Create a new finding on an engagement.
     */
    public function create(array $data, Engagement $engagement, User $by): Finding
    {
        return DB::transaction(function () use ($data, $engagement, $by): Finding {
            $severity = $data['severity'] instanceof FindingSeverity
                ? $data['severity']->value
                : (string) $data['severity'];

            /** @var Finding $finding */
            $finding = Finding::create(array_merge($data, [
                'engagement_id' => $engagement->id,
                'company_id' => $engagement->company_id,
                'severity' => $severity,
                'status' => FindingStatus::Open->value,
                'created_by' => $by->id,
            ]));

            // Dispatch audit log event
            event(new FindingStatusChanged(
                userId: $by->id,
                action: 'create_finding',
                companyId: $finding->company_id,
                objectType: 'Finding',
                objectId: $finding->id,
                before: null,
                after: ['status' => FindingStatus::Open->value, 'title' => $finding->title],
            ));

            // Notify client users
            $clients = $engagement->company->users;
            $this->notificationService->notifyMany(
                $clients,
                'New Finding Raised',
                "A new audit finding '{$finding->title}' has been raised with severity: {$severity}. Please provide a management response.",
                'finding',
                '/client/evidence',
            );

            return $finding;
        });
    }

    /**
     * Record management response against a finding.
     */
    public function recordManagementResponse(Finding $finding, string $response, User $by): void
    {
        $oldResponse = $finding->management_response;

        $finding->update(['management_response' => $response]);

        event(new FindingStatusChanged(
            userId: $by->id,
            action: 'record_management_response',
            companyId: $finding->company_id,
            objectType: 'Finding',
            objectId: $finding->id,
            before: ['management_response' => $oldResponse],
            after: ['management_response' => $response],
        ));

        // Notify audit team
        $engagement = $finding->engagement;
        if ($engagement) {
            $users = [];
            if ($finding->createdBy) {
                $users[] = $finding->createdBy;
            }
            $ids = array_filter([$engagement->lead_auditor_id, $engagement->manager_id, $engagement->partner_id]);
            foreach (User::whereIn('id', $ids)->get() as $user) {
                $users[] = $user;
            }
            foreach ($engagement->members()->with('user')->get() as $member) {
                if ($member->user) {
                    $users[] = $member->user;
                }
            }
            $auditors = collect($users)->unique('id');

            $this->notificationService->notifyMany(
                $auditors,
                'Management Response Recorded',
                "A management response has been recorded for finding '{$finding->title}' by {$by->name}.",
                'finding',
                '/audit-findings',
            );
        }
    }

    /**
     * Resolve a finding.
     */
    public function resolve(Finding $finding, User $by): void
    {
        if ($finding->status === FindingStatus::Resolved) {
            throw new \DomainException('Finding is already resolved.');
        }

        $oldStatus = $finding->status->value;

        DB::transaction(function () use ($finding, $by, $oldStatus): void {
            $finding->update(['status' => FindingStatus::Resolved->value]);

            event(new FindingStatusChanged(
                userId: $by->id,
                action: 'resolve_finding',
                companyId: $finding->company_id,
                objectType: 'Finding',
                objectId: $finding->id,
                before: ['status' => $oldStatus],
                after: ['status' => FindingStatus::Resolved->value],
            ));
        });
    }

    /**
     * Re-open a resolved finding.
     */
    public function reopen(Finding $finding, User $by, string $reason): void
    {
        if ($finding->status !== FindingStatus::Resolved) {
            throw new \DomainException('Only resolved findings can be re-opened.');
        }

        $oldStatus = $finding->status->value;

        DB::transaction(function () use ($finding, $by, $reason, $oldStatus): void {
            $newDescription = $finding->description."\n[REOPENED] {$reason}";
            $finding->update([
                'status' => FindingStatus::Reopened->value,
                'description' => $newDescription,
            ]);

            event(new FindingStatusChanged(
                userId: $by->id,
                action: 'reopen_finding',
                companyId: $finding->company_id,
                objectType: 'Finding',
                objectId: $finding->id,
                before: ['status' => $oldStatus],
                after: ['status' => FindingStatus::Reopened->value, 'unlock_reason' => $reason],
            ));

            // Notify client users
            $engagement = $finding->engagement;
            if ($engagement) {
                $clients = $engagement->company->users;
                $this->notificationService->notifyMany(
                    $clients,
                    'Finding Reopened',
                    "The finding '{$finding->title}' has been reopened. Reason: {$reason}",
                    'finding',
                    '/client/evidence',
                );
            }
        });
    }
}
