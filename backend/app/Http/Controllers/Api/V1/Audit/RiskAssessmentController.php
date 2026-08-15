<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreRiskAssessmentRequest;
use App\Http\Requests\Audit\UpdateRiskAssessmentRequest;
use App\Http\Resources\Audit\RiskAssessmentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Engagement;
use App\Models\RiskAssessment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * RiskAssessmentController — EPIC 11 PRD §6.20
 */
final class RiskAssessmentController extends Controller
{
    public function index(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(RiskAssessmentResource::collection(
            RiskAssessment::where('engagement_id', $engagement->id)
                ->orderByDesc('created_at')
                ->get(),
        ));
    }

    public function store(StoreRiskAssessmentRequest $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validated();

        $risk = DB::transaction(function () use ($validated, $engagement): RiskAssessment {
            /** @var RiskAssessment $risk */
            $risk = RiskAssessment::create(array_merge($validated, [
                'engagement_id' => $engagement->id,
            ]));

            return $risk;
        });

        return ApiResponse::created(new RiskAssessmentResource($risk), 'Risk assessment created.');
    }

    public function show(Engagement $engagement, RiskAssessment $riskAssessment): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(new RiskAssessmentResource($riskAssessment));
    }

    public function update(UpdateRiskAssessmentRequest $request, Engagement $engagement, RiskAssessment $riskAssessment): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validated();

        DB::transaction(function () use ($riskAssessment, $validated): void {
            $riskAssessment->update($validated);
        });

        return ApiResponse::success(new RiskAssessmentResource($riskAssessment->fresh()), 'Risk assessment updated.');
    }

    public function destroy(Engagement $engagement, RiskAssessment $riskAssessment): JsonResponse
    {
        $this->authorize('update', $engagement);

        DB::transaction(function () use ($riskAssessment): void {
            $riskAssessment->delete();
        });

        return ApiResponse::noContent();
    }
}
