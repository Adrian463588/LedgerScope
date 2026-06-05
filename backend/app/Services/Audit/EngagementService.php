<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Enums\Audit\EngagementStatus;
use App\Models\Company;
use App\Models\Engagement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class EngagementService
{
    /**
     * Create engagement in Planning status.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, Company $company, User $lead): Engagement
    {
        return DB::transaction(function () use ($data, $company, $lead): Engagement {
            /** @var Engagement $engagement */
            $engagement = Engagement::create(array_merge($data, [
                'company_id'      => $company->id,
                'lead_auditor_id' => $lead->id,
                'status'          => EngagementStatus::Planning,
            ]));

            return $engagement;
        });
    }

    /**
     * Transition Planning → InProgress.
     */
    public function activate(Engagement $engagement, User $by): void
    {
        if ($engagement->status !== EngagementStatus::Planning) {
            throw new \DomainException('Engagement already active or in another state — cannot re-activate.');
        }

        DB::transaction(function () use ($engagement): void {
            $engagement->update(['status' => EngagementStatus::InProgress]);
        });
    }

    /**
     * Transition InProgress → Completed.
     */
    public function close(Engagement $engagement, User $by): void
    {
        if (! in_array($engagement->status, [EngagementStatus::InProgress, EngagementStatus::Review], true)) {
            throw new \DomainException('Engagement must be InProgress or In Review to close.');
        }

        DB::transaction(function () use ($engagement): void {
            $engagement->update([
                'status'       => EngagementStatus::Completed,
                'completed_at' => now(),
            ]);
        });
    }

    /**
     * Cancel engagement.
     */
    public function cancel(Engagement $engagement, User $by, string $reason): void
    {
        if ($engagement->status === EngagementStatus::Completed) {
            throw new \DomainException('Cannot cancel a completed engagement.');
        }

        DB::transaction(function () use ($engagement, $reason): void {
            $engagement->update([
                'status' => EngagementStatus::Cancelled,
                'scope'  => $engagement->scope . "\n[CANCELLED] {$reason}",
            ]);
        });
    }
}
