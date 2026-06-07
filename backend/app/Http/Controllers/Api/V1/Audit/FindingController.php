<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreFindingRequest;
use App\Http\Requests\Audit\UpdateFindingRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Engagement;
use App\Models\Finding;
use App\Services\Audit\FindingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * FindingController — EPIC 7 PRD §6.22
 */
final class FindingController extends Controller
{
    public function __construct(private readonly FindingService $service) {}

    public function index(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(
            $engagement->findings()->with(['assignedTo', 'createdBy'])->orderByDesc('created_at')->get(),
        );
    }

    public function store(StoreFindingRequest $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('create', [Finding::class, $engagement]);

        $validated = $request->validated();

        $finding = $this->service->create($validated, $engagement, $request->user());

        return ApiResponse::created($finding, 'Finding created.');
    }

    public function show(Engagement $engagement, Finding $finding): JsonResponse
    {
        $this->authorize('view', $finding);

        return ApiResponse::success($finding->load(['assignedTo', 'createdBy', 'approvedBy']));
    }

    public function update(UpdateFindingRequest $request, Engagement $engagement, Finding $finding): JsonResponse
    {
        $this->authorize('update', $finding);

        $validated = $request->validated();

        $finding->update($validated);

        return ApiResponse::success($finding->fresh(), 'Finding updated.');
    }

    public function resolve(Request $request, Engagement $engagement, Finding $finding): JsonResponse
    {
        $this->authorize('close', $finding);

        $this->service->resolve($finding, $request->user());

        return ApiResponse::success($finding->fresh(), 'Finding resolved.');
    }

    public function reopen(Request $request, Engagement $engagement, Finding $finding): JsonResponse
    {
        $this->authorize('close', $finding);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $this->service->reopen($finding, $request->user(), $validated['reason']);

        return ApiResponse::success($finding->fresh(), 'Finding reopened.');
    }

    public function managementResponse(Request $request, Engagement $engagement, Finding $finding): JsonResponse
    {
        $this->authorize('managementResponse', $finding);

        $validated = $request->validate([
            'management_response' => ['required', 'string'],
        ]);

        $this->service->recordManagementResponse($finding, $validated['management_response'], $request->user());

        return ApiResponse::success($finding->fresh(), 'Management response recorded.');
    }
}
