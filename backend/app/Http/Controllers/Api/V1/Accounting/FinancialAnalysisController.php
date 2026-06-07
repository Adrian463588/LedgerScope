<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Services\Accounting\FinancialAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * FinancialAnalysisController — EPIC 5
 */
final class FinancialAnalysisController extends Controller
{
    public function __construct(private readonly FinancialAnalysisService $service) {}

    public function ratios(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $periodId = $request->query('accounting_period_id') ? (int) $request->query('accounting_period_id') : null;
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

        $periodId = $request->query('accounting_period_id') ? (int) $request->query('accounting_period_id') : null;
        $comparePeriodId = $request->query('compare_period_id') ? (int) $request->query('compare_period_id') : null;
        $filters = [
            'department' => $request->query('department'),
            'account_category' => $request->query('account_category'),
        ];

        $variance = $this->service->calculateVariance($company, $periodId, $comparePeriodId, $filters);

        return ApiResponse::success($variance);
    }
}
