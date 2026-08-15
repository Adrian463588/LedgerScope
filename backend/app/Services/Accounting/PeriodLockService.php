<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Enums\Accounting\PeriodStatus;
use App\Events\Accounting\PeriodLocked;
use App\Events\Accounting\PeriodUnlocked;
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

        DB::transaction(function () use ($period, $user): void {
            /** @var AccountingPeriod $lockedPeriod */
            $lockedPeriod = AccountingPeriod::query()
                ->lockForUpdate()
                ->findOrFail($period->id);

            if ($lockedPeriod->isLocked()) {
                throw new \DomainException("Period [{$lockedPeriod->period_name}] is already locked.");
            }

            $lockedPeriod->forceFill([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => $user->id,
                'status' => PeriodStatus::Locked->value,
            ])->save();

            // B-07: Dispatch audit event — required by AGENTS_BACKEND §3.3
            event(new PeriodLocked(
                userId: $user->id,
                action: 'lock_period',
                companyId: $lockedPeriod->company_id,
                objectType: 'AccountingPeriod',
                objectId: $lockedPeriod->id,
                metadata: ['period_name' => $lockedPeriod->period_name],
            ));
        });
    }

    public function unlock(AccountingPeriod $period, User $user, string $reason): void
    {
        if (! $user->hasPermission('quarter.unlock')) {
            throw new AuthorizationException('You do not have permission to unlock periods.');
        }

        if (trim($reason) === '') {
            throw new \DomainException('An unlock reason is required.');
        }

        DB::transaction(function () use ($period, $user, $reason): void {
            /** @var AccountingPeriod $lockedPeriod */
            $lockedPeriod = AccountingPeriod::query()
                ->lockForUpdate()
                ->findOrFail($period->id);

            if (! $lockedPeriod->isLocked()) {
                throw new \DomainException("Period [{$lockedPeriod->period_name}] is not locked.");
            }

            $lockedPeriod->forceFill([
                'is_locked' => false,
                'locked_at' => null,
                'locked_by' => null,
                'unlock_reason' => $reason,
                'status' => PeriodStatus::Open->value,
            ])->save();

            // B-07: Dispatch audit event — required by AGENTS_BACKEND §3.3
            event(new PeriodUnlocked(
                userId: $user->id,
                action: 'unlock_period',
                companyId: $lockedPeriod->company_id,
                objectType: 'AccountingPeriod',
                objectId: $lockedPeriod->id,
                metadata: ['period_name' => $lockedPeriod->period_name, 'reason' => $reason],
            ));
        });
    }
}
