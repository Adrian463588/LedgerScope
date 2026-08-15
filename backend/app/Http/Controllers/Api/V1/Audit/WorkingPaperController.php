<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreWorkingPaperRequest;
use App\Http\Requests\Audit\UpdateWorkingPaperRequest;
use App\Http\Resources\Audit\WorkingPaperResource;
use App\Http\Responses\ApiResponse;
use App\Models\Engagement;
use App\Models\WorkingPaper;
use App\Services\Audit\WorkingPaperService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * WorkingPaperController — EPIC 6 PRD §6.17
 */
final class WorkingPaperController extends Controller
{
    public function __construct(private readonly WorkingPaperService $service) {}

    public function index(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(WorkingPaperResource::collection(
            $engagement->workingPapers()->with(['preparedBy', 'evidenceFiles'])->orderByDesc('created_at')->get(),
        ));
    }

    public function store(StoreWorkingPaperRequest $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('create', [WorkingPaper::class, $engagement]);

        $validated = $request->validated();

        $wp = $this->service->create($validated, $engagement, $request->user());

        return ApiResponse::created(new WorkingPaperResource($wp), 'Working paper created.');
    }

    public function show(Engagement $engagement, WorkingPaper $workingPaper): JsonResponse
    {
        $this->authorize('view', $workingPaper);

        return ApiResponse::success(new WorkingPaperResource($workingPaper->load([
            'preparedBy',
            'evidenceFiles',
            'reviewNotes.createdBy',
            'reviewNotes.resolvedBy',
            'reviewNotes.replies.user',
        ])));
    }

    public function update(UpdateWorkingPaperRequest $request, Engagement $engagement, WorkingPaper $workingPaper): JsonResponse
    {
        $this->authorize('update', $workingPaper);

        $this->service->ensureNotLocked($workingPaper);

        $validated = $request->validated();

        $workingPaper->update($validated);

        return ApiResponse::success(new WorkingPaperResource($workingPaper->fresh()), 'Working paper updated.');
    }

    public function signOff(Request $request, Engagement $engagement, WorkingPaper $workingPaper): JsonResponse
    {
        $this->authorize('signoff', $workingPaper);

        $this->service->signOff($workingPaper, $request->user());

        return ApiResponse::success(new WorkingPaperResource($workingPaper->fresh()), 'Working paper signed off.');
    }

    public function lock(Request $request, Engagement $engagement, WorkingPaper $workingPaper): JsonResponse
    {
        $this->authorize('lock', $workingPaper);

        $this->service->lock($workingPaper, $request->user());

        return ApiResponse::success(new WorkingPaperResource($workingPaper->fresh()), 'Working paper locked.');
    }

    public function unlock(Request $request, Engagement $engagement, WorkingPaper $workingPaper): JsonResponse
    {
        $this->authorize('unlock', $workingPaper);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $this->service->unlock($workingPaper, $request->user(), $validated['reason']);

        return ApiResponse::success(new WorkingPaperResource($workingPaper->fresh()), 'Working paper unlocked.');
    }
}
