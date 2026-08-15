<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Events\Audit\ReviewNoteResolved;
use App\Http\Controllers\Controller;
use App\Http\Resources\Audit\ReviewNoteReplyResource;
use App\Http\Resources\Audit\ReviewNoteResource;
use App\Http\Responses\ApiResponse;
use App\Models\Engagement;
use App\Models\ReviewNote;
use App\Models\ReviewNoteReply;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ReviewNoteController — EPIC 8 PRD §6.21
 * Review notes are associated with working papers but scoped to engagement.
 */
final class ReviewNoteController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        // Load notes via working papers belonging to this engagement
        $notes = ReviewNote::whereHas('workingPaper', fn ($q) => $q->where('engagement_id', $engagement->id))
            ->with(['workingPaper', 'createdBy', 'resolvedBy', 'replies.user'])
            ->orderByDesc('created_at')
            ->get();

        return ApiResponse::success(ReviewNoteResource::collection($notes));
    }

    public function store(Request $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validate([
            'working_paper_id' => ['required', 'integer', 'exists:working_papers,id'],
            'content' => ['required', 'string', 'min:5'],
        ]);

        $note = DB::transaction(function () use ($validated, $request): ReviewNote {
            /** @var ReviewNote $note */
            $note = ReviewNote::create([
                'working_paper_id' => $validated['working_paper_id'],
                'content' => $validated['content'],
                'created_by' => $request->user()->id,
                'status' => 'open',
            ]);

            return $note;
        });

        // Notify the preparer of the working paper (if not the creator of the note)
        $workingPaper = $note->workingPaper;
        if ($workingPaper && $workingPaper->prepared_by !== $request->user()->id && $workingPaper->preparedBy) {
            $this->notificationService->notify(
                $workingPaper->preparedBy,
                'New Review Note Assigned',
                "A new review note has been created on your working paper '{$workingPaper->title}' by {$request->user()->name}.",
                'review_note',
                "/engagements/{$engagement->id}/working-papers/{$workingPaper->id}",
            );
        }

        return ApiResponse::created(new ReviewNoteResource($note->load('createdBy')), 'Review note created.');
    }

    public function resolve(Request $request, Engagement $engagement, ReviewNote $reviewNote): JsonResponse
    {
        $this->authorize('update', $engagement);

        if ($reviewNote->status === 'resolved') {
            return ApiResponse::domainError('Review note is already resolved.');
        }

        $oldStatus = $reviewNote->status;

        DB::transaction(function () use ($reviewNote, $request, $engagement, $oldStatus): void {
            $reviewNote->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => $request->user()->id,
            ]);

            event(new ReviewNoteResolved(
                $request->user()->id,
                'resolve',
                $engagement->company_id,
                'ReviewNote',
                $reviewNote->id,
                ['status' => $oldStatus],
                ['status' => 'resolved'],
                $request->ip(),
                $request->userAgent(),
                ['content' => $reviewNote->content],
            ));
        });

        return ApiResponse::success(new ReviewNoteResource($reviewNote->fresh()), 'Review note resolved.');
    }

    public function destroy(Request $request, Engagement $engagement, ReviewNote $reviewNote): JsonResponse
    {
        $this->authorize('update', $engagement);

        // Only allow deleting own open notes
        if ($reviewNote->created_by !== $request->user()->id) {
            return ApiResponse::forbidden('You can only delete your own review notes.');
        }

        if ($reviewNote->status !== 'open') {
            return ApiResponse::domainError('Only open review notes can be deleted.');
        }

        $reviewNote->delete();

        return ApiResponse::noContent();
    }

    public function reply(Request $request, Engagement $engagement, ReviewNote $reviewNote): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2'],
        ]);

        $reply = DB::transaction(function () use ($validated, $reviewNote, $request): ReviewNoteReply {
            /** @var ReviewNoteReply $reply */
            $reply = ReviewNoteReply::create([
                'review_note_id' => $reviewNote->id,
                'user_id' => $request->user()->id,
                'message' => $validated['message'],
            ]);

            return $reply;
        });

        return ApiResponse::created(new ReviewNoteReplyResource($reply->load('user')), 'Reply added.');
    }

    public function reopen(Request $request, Engagement $engagement, ReviewNote $reviewNote): JsonResponse
    {
        $this->authorize('update', $engagement);

        if ($reviewNote->status !== 'resolved') {
            return ApiResponse::domainError('Only resolved review notes can be reopened.');
        }

        DB::transaction(function () use ($reviewNote): void {
            $reviewNote->update([
                'status' => 'open',
                'resolved_at' => null,
                'resolved_by' => null,
            ]);
        });

        return ApiResponse::success(new ReviewNoteResource($reviewNote->fresh()), 'Review note reopened.');
    }
}
