<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Services\Accounting\FinancialAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * FinancialAnalysisController — EPIC 5
 */
final class FinancialAnalysisController extends Controller
{
    public function __construct(private readonly FinancialAnalysisService $service) {}

    public function ratios(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $periodId = $this->scopedPeriodId($request, $company, 'accounting_period_id');
        $filters = [
            'department' => $request->query('department'),
            'account_category' => $request->query('account_category'),
        ];

        $ratios = $this->service->calculateRatios($company, $periodId, $filters);

        return ApiResponse::success($ratios);
    }

    public function trends(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $filters = [
            'department' => $request->query('department'),
            'account_category' => $request->query('account_category'),
        ];

        $trends = $this->service->getTrends($company, $filters);

        return ApiResponse::success($trends);
    }

    public function variance(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $periodId = $this->scopedPeriodId($request, $company, 'accounting_period_id');
        $comparePeriodId = $this->scopedPeriodId($request, $company, 'compare_period_id');
        $filters = [
            'department' => $request->query('department'),
            'account_category' => $request->query('account_category'),
        ];

        $variance = $this->service->calculateVariance($company, $periodId, $comparePeriodId, $filters);

        return ApiResponse::success($variance);
    }

    private function scopedPeriodId(Request $request, Company $company, string $key): ?int
    {
        $raw = $request->query($key);

        if ($raw === null || $raw === '') {
            return null;
        }

        $periodId = filter_var($raw, FILTER_VALIDATE_INT);

        if ($periodId === false || $periodId < 1) {
            throw ValidationException::withMessages([
                $key => ['The selected period is invalid.'],
            ]);
        }

        $company->accountingPeriods()->findOrFail($periodId);

        return $periodId;
    }
}
