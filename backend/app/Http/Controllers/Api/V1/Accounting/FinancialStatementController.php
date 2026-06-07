<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\FinancialStatement;
use App\Models\AccountingPeriod;
use App\Services\Accounting\StatementBuilderService;
use App\Exports\FinancialStatementExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
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

    public function generate(Request $request, Company $company, StatementBuilderService $service): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
            'statement_type' => ['required', 'string', 'in:balance_sheet,income_statement,cash_flow,equity_changes'],
        ]);

        $period = AccountingPeriod::query()->findOrFail($validated['accounting_period_id']);
        if (!($period instanceof AccountingPeriod)) {
            throw new \RuntimeException('Failed to load accounting period.');
        }
        $statement = $service->build($company, $period, $validated['statement_type'], $request->user());

        return ApiResponse::success($statement, 'Statement generated successfully.');
    }

    public function show(Request $request, Company $company, FinancialStatement $version): JsonResponse
    {
        $this->authorize('view', $company);

        $comparePeriodId = $request->query('compare_with');
        $comparison = null;

        if ($comparePeriodId) {
            $comparison = FinancialStatement::where('company_id', $company->id)
                ->where('accounting_period_id', (int) $comparePeriodId)
                ->where('statement_type', $version->statement_type)
                ->latest('version')
                ->first();
        }

        if ($comparison) {
            return ApiResponse::success([
                'statement' => $version,
                'comparison' => $comparison,
            ]);
        }

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

    public function export(Request $request, Company $company, FinancialStatement $version): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('view', $company);

        $format = $request->query('format', 'pdf');

        if ($format === 'xlsx') {
            return Excel::download(
                new FinancialStatementExport($version),
                "statement-{$version->statement_type}.xlsx"
            );
        }

        $pdf = Pdf::loadView('exports.financial-statement', [
            'statement' => $version,
            'company' => $company,
        ]);

        return $pdf->download("statement-{$version->statement_type}.pdf");
    }
}
