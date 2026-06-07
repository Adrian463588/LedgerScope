<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ControlRisk;
use App\Models\Engagement;
use App\Models\InternalControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ControlRiskController — Epic 7A risks linked to a specific control
 */
final class ControlRiskController extends Controller
{
    public function index(Engagement $engagement, InternalControl $internalControl): JsonResponse
    {
        $this->authorize('view', $engagement->company);

        return ApiResponse::success(
            $internalControl->controlRisks()->orderByDesc('created_at')->get(),
        );
    }

    public function store(Request $request, Engagement $engagement, InternalControl $internalControl): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $validated = $request->validate([
            'risk_name'          => ['required', 'string', 'max:200'],
            'risk_description'   => ['nullable', 'string'],
            'likelihood'         => ['nullable', 'string', 'in:low,medium,high'],
            'impact'             => ['nullable', 'string', 'in:low,medium,high'],
            'residual_risk'      => ['nullable', 'string', 'in:low,medium,high'],
            'mitigating_factors' => ['nullable', 'string'],
        ]);

        /** @var ControlRisk $risk */
        $risk = $internalControl->controlRisks()->create(array_merge($validated, [
            'engagement_id' => $engagement->id,
        ]));

        return ApiResponse::created($risk, 'Control risk added.');
    }

    public function update(Request $request, Engagement $engagement, InternalControl $internalControl, ControlRisk $controlRisk): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $validated = $request->validate([
            'risk_name'          => ['sometimes', 'string', 'max:200'],
            'risk_description'   => ['nullable', 'string'],
            'likelihood'         => ['nullable', 'string', 'in:low,medium,high'],
            'impact'             => ['nullable', 'string', 'in:low,medium,high'],
            'residual_risk'      => ['nullable', 'string', 'in:low,medium,high'],
            'mitigating_factors' => ['nullable', 'string'],
        ]);

        $controlRisk->update($validated);

        return ApiResponse::success($controlRisk->fresh(), 'Control risk updated.');
    }

    public function destroy(Engagement $engagement, InternalControl $internalControl, ControlRisk $controlRisk): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $controlRisk->delete();

        return ApiResponse::noContent();
    }
}
