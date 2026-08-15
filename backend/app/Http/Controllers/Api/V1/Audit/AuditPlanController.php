<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\UpdateAuditPlanRequest;
use App\Http\Resources\Audit\AuditPlanResource;
use App\Http\Responses\ApiResponse;
use App\Models\Engagement;
use App\Services\Audit\AuditPlanService;
use Illuminate\Http\JsonResponse;

/**
 * AuditPlanController — EPIC 4 PRD §6.12
 */
final class AuditPlanController extends Controller
{
    public function __construct(private readonly AuditPlanService $service) {}

    public function show(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        $plan = $this->service->getOrCreate($engagement);

        return ApiResponse::success(new AuditPlanResource($plan));
    }

    public function update(UpdateAuditPlanRequest $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        $plan = $this->service->getOrCreate($engagement);

        $updatedPlan = $this->service->update($plan, $request->validated());

        return ApiResponse::success(new AuditPlanResource($updatedPlan), 'Audit plan updated.');
    }
}
