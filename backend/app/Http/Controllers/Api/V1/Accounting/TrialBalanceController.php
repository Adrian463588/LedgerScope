<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\TrialBalance;
use App\Models\AccountingPeriod;
use App\Services\Accounting\TrialBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TrialBalanceController extends Controller
{
    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(
            TrialBalance::where('company_id', $company->id)
                ->orderByDesc('created_at')->get(),
        );
    }

    public function generate(Request $request, Company $company, TrialBalanceService $service): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
        ]);

        $period = AccountingPeriod::query()->findOrFail($validated['accounting_period_id']);
        if (!($period instanceof AccountingPeriod)) {
            throw new \RuntimeException('Failed to load accounting period.');
        }
        $tb = $service->generate($company, $period, $request->user());

        return ApiResponse::success($tb, 'Trial balance generated successfully.');
    }
}
