<?php

declare(strict_types=1);

namespace App\Services\Audit;

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
                'prepared_by'   => $preparedBy->id,
                'status'        => 'draft',
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
                'status'      => 'reviewed',
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
                'status'  => 'draft',
                'content' => $wp->content . "\n[REJECTED] {$note}",
            ]);
        });
    }
}
