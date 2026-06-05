<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\PeriodStatus;
use App\Models\AccountingPeriod;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

/**
 * PeriodLockService — lock and unlock accounting periods.
 *
 * AGENTS.md Phase 3 §3.3
 */
final class PeriodLockService
{
    public function lock(AccountingPeriod $period, User $user): void
    {
        if (! $user->hasPermission('quarter.lock')) {
            throw new AuthorizationException('You do not have permission to lock periods.');
        }

        if ($period->isLocked()) {
            throw new \DomainException("Period [{$period->period_name}] is already locked.");
        }

        DB::transaction(function () use ($period, $user): void {
            $period->forceFill([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => $user->id,
                'status' => PeriodStatus::Locked->value,
            ])->save();
        });
    }

    public function unlock(AccountingPeriod $period, User $user, string $reason): void
    {
        if (! $user->hasPermission('quarter.unlock')) {
            throw new AuthorizationException('You do not have permission to unlock periods.');
        }

        if (! $period->isLocked()) {
            throw new \DomainException("Period [{$period->period_name}] is not locked.");
        }

        if (trim($reason) === '') {
            throw new \DomainException('An unlock reason is required.');
        }

        DB::transaction(function () use ($period, $reason): void {
            $period->forceFill([
                'is_locked' => false,
                'locked_at' => null,
                'locked_by' => null,
                'unlock_reason' => $reason,
                'status' => PeriodStatus::Open->value,
            ])->save();
        });
    }
}
