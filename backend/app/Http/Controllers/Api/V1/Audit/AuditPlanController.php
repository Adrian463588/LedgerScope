<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Engagement;
use App\Services\Audit\AuditPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return ApiResponse::success($plan);
    }

    public function update(Request $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        $plan = $this->service->getOrCreate($engagement);

        $validated = $request->validate([
            'overall_materiality' => ['nullable', 'numeric', 'min:0'],
            'performance_materiality' => ['nullable', 'numeric', 'min:0'],
            'trivial_threshold' => ['nullable', 'numeric', 'min:0'],
            'audit_strategy' => ['nullable', 'string'],
            'planning_checklist' => ['nullable', 'array'],
        ]);

        $updatedPlan = $this->service->update($plan, $validated);

        return ApiResponse::success($updatedPlan, 'Audit plan updated.');
    }
}
