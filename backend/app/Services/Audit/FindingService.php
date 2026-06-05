<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Enums\Audit\FindingSeverity;
use App\Models\Engagement;
use App\Models\Finding;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class FindingService
{
    /**
     * Create a new finding on an engagement.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, Engagement $engagement, User $by): Finding
    {
        return DB::transaction(function () use ($data, $engagement): Finding {
            $severity = $data['severity'] instanceof FindingSeverity
                ? $data['severity']->value
                : (string) $data['severity'];

            /** @var Finding $finding */
            $finding = Finding::create(array_merge($data, [
                'engagement_id' => $engagement->id,
                'severity' => $severity,
                'status' => 'open',
            ]));

            return $finding;
        });
    }

    /**
     * Record management response against a finding.
     */
    public function recordManagementResponse(Finding $finding, string $response, User $by): void
    {
        $finding->update(['management_response' => $response]);
    }

    /**
     * Resolve a finding.
     */
    public function resolve(Finding $finding, User $by): void
    {
        if ($finding->status === 'resolved') {
            throw new \DomainException('Finding is already resolved.');
        }

        DB::transaction(function () use ($finding): void {
            $finding->update(['status' => 'resolved']);
        });
    }

    /**
     * Re-open a resolved finding.
     */
    public function reopen(Finding $finding, User $by, string $reason): void
    {
        if ($finding->status !== 'resolved') {
            throw new \DomainException('Only resolved findings can be re-opened.');
        }

        DB::transaction(function () use ($finding, $reason): void {
            $finding->update([
                'status' => 'open',
                'description' => $finding->description."\n[REOPENED] {$reason}",
            ]);
        });
    }
}
