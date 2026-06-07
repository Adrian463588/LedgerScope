<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Events\Audit\WorkingPaperSignedOff;
use App\Models\Engagement;
use App\Models\User;
use App\Models\WorkingPaper;
use Illuminate\Support\Facades\DB;

final class WorkingPaperService
{
    /**
     * Create a draft working paper on an engagement.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, Engagement $engagement, User $preparedBy): WorkingPaper
    {
        return DB::transaction(function () use ($data, $engagement, $preparedBy): WorkingPaper {
            /** @var WorkingPaper $wp */
            $wp = WorkingPaper::create(array_merge($data, [
                'engagement_id' => $engagement->id,
                'prepared_by' => $preparedBy->id,
                'status' => 'draft',
            ]));

            return $wp;
        });
    }

    /**
     * Transition draft → in_review.
     */
    public function submitForReview(WorkingPaper $wp, User $by): void
    {
        if ($wp->status !== 'draft') {
            throw new \DomainException("Working paper is {$wp->status} — already reviewed or cannot be re-submitted.");
        }

        DB::transaction(function () use ($wp): void {
            $wp->update(['status' => 'in_review']);
        });
    }

    /**
     * Transition in_review → reviewed.
     */
    public function approve(WorkingPaper $wp, User $reviewer): void
    {
        if ($wp->status !== 'in_review') {
            throw new \DomainException('Working paper must be in_review status to approve.');
        }

        DB::transaction(function () use ($wp, $reviewer): void {
            $wp->update([
                'status' => 'reviewed',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);
        });
    }

    /**
     * Reject back to draft with a note.
     */
    public function reject(WorkingPaper $wp, User $reviewer, string $note): void
    {
        if ($wp->status !== 'in_review') {
            throw new \DomainException('Only in_review papers can be rejected.');
        }

        DB::transaction(function () use ($wp, $note): void {
            $wp->update([
                'status' => 'draft',
                'content' => $wp->content."\n[REJECTED] {$note}",
            ]);
        });
    }

    /**
     * Sign off a working paper (prepared_by or reviewed_by).
     */
    public function signOff(WorkingPaper $wp, User $by): void
    {
        $this->ensureNotLocked($wp);

        if (in_array($wp->status, ['reviewed', 'approved', 'locked'], true)) {
            throw new \DomainException("Cannot sign off a working paper with status [{$wp->status}].");
        }

        $oldStatus = $wp->status;

        DB::transaction(function () use ($wp, $by, $oldStatus): void {
            $wp->update([
                'status' => 'reviewed',
                'sign_off_by' => $by->id,
                'sign_off_at' => now(),
            ]);

            event(new WorkingPaperSignedOff(
                $by->id,
                'sign_off',
                $wp->engagement->company_id,
                'WorkingPaper',
                $wp->id,
                ['status' => $oldStatus],
                ['status' => 'reviewed'],
                request()->ip(),
                request()->userAgent(),
                ['title' => $wp->title]
            ));
        });
    }

    /**
     * Lock a working paper after final review.
     */
    public function lock(WorkingPaper $wp, User $by): void
    {
        if ($wp->is_locked) {
            throw new \DomainException('Working paper is already locked.');
        }

        DB::transaction(function () use ($wp, $by): void {
            $wp->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => $by->id,
            ]);
        });
    }

    /**
     * Unlock a working paper with a reason.
     */
    public function unlock(WorkingPaper $wp, User $by, string $reason): void
    {
        if (! $wp->is_locked) {
            throw new \DomainException('Working paper is not locked.');
        }

        if (trim($reason) === '') {
            throw new \DomainException('An unlock reason is required.');
        }

        DB::transaction(function () use ($wp): void {
            $wp->update([
                'is_locked' => false,
                'locked_at' => null,
                'locked_by' => null,
            ]);
        });
    }

    /**
     * Guard: throw if working paper is locked (prevent editing).
     */
    public function ensureNotLocked(WorkingPaper $wp): void
    {
        if ($wp->is_locked) {
            throw new \DomainException('Working paper is locked and cannot be modified.');
        }
    }
}
