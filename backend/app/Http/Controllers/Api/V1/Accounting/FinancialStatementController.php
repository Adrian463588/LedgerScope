<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\FinancialStatement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FinancialStatementController extends Controller
{
    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(
            FinancialStatement::where('company_id', $company->id)->orderByDesc('created_at')->get(),
        );
    }

    public function generate(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
            'statement_type' => ['required', 'string', 'in:balance_sheet,income_statement,cash_flow,equity_changes'],
        ]);

        return ApiResponse::success(null, 'Statement generation queued.');
    }

    public function show(Company $company, FinancialStatement $version): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success($version);
    }

    public function approve(Request $request, Company $company, FinancialStatement $version): JsonResponse
    {
        $this->authorize('update', $company);

        $version->update(['status' => 'approved', 'approved_by' => $request->user()->id, 'approved_at' => now()]);

        return ApiResponse::success($version->fresh(), 'Statement approved.');
    }

    public function lock(Request $request, Company $company, FinancialStatement $version): JsonResponse
    {
        $this->authorize('update', $company);

        $version->update(['is_locked' => true, 'locked_at' => now()]);

        return ApiResponse::success($version->fresh(), 'Statement locked.');
    }
}
