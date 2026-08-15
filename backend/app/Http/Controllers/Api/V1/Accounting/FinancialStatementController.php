<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Events\AuditActionRecorded;
use App\Exports\FinancialStatementExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Accounting\FinancialStatementResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\FinancialStatement;
use App\Services\Accounting\StatementBuilderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

final class FinancialStatementController extends Controller
{
    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(FinancialStatementResource::collection(
            FinancialStatement::where('company_id', $company->id)->orderByDesc('created_at')->get(),
        ));
    }

    public function generate(Request $request, Company $company, StatementBuilderService $service): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
            'statement_type' => ['required', 'string', 'in:balance_sheet,income_statement,cash_flow,equity_changes'],
        ]);

        $period = $company->accountingPeriods()->findOrFail($validated['accounting_period_id']);
        $statement = $service->build($company, $period, $validated['statement_type'], $request->user());

        return ApiResponse::success(new FinancialStatementResource($statement), 'Statement generated successfully.');
    }

    public function show(Request $request, Company $company, FinancialStatement $financialStatement): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('view', $financialStatement);

        $comparePeriodId = $request->query('compare_with');
        $comparison = null;

        if ($comparePeriodId) {
            $comparison = FinancialStatement::where('company_id', $company->id)
                ->where('accounting_period_id', (int) $comparePeriodId)
                ->where('statement_type', $financialStatement->statement_type)
                ->latest('version')
                ->first();
        }

        if ($comparison) {
            return ApiResponse::success([
                'statement' => new FinancialStatementResource($financialStatement),
                'comparison' => new FinancialStatementResource($comparison),
            ]);
        }

        return ApiResponse::success(new FinancialStatementResource($financialStatement));
    }

    public function approve(Request $request, Company $company, FinancialStatement $financialStatement): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $financialStatement);

        DB::transaction(function () use ($financialStatement, $request, $company): void {
            /** @var FinancialStatement $lockedStatement */
            $lockedStatement = FinancialStatement::query()
                ->lockForUpdate()
                ->findOrFail($financialStatement->id);

            if ($lockedStatement->is_locked) {
                throw new \DomainException('Locked financial statements are immutable.');
            }

            $lockedStatement->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            event(new AuditActionRecorded(
                userId: $request->user()->id,
                action: 'financial_statement.approve',
                companyId: $company->id,
                objectType: 'FinancialStatement',
                objectId: $lockedStatement->id,
                after: $lockedStatement->fresh()->toArray(),
            ));
        });

        return ApiResponse::success(new FinancialStatementResource($financialStatement->fresh()), 'Statement approved.');
    }

    public function lock(Request $request, Company $company, FinancialStatement $financialStatement): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $financialStatement);

        DB::transaction(function () use ($financialStatement, $request, $company): void {
            /** @var FinancialStatement $lockedStatement */
            $lockedStatement = FinancialStatement::query()
                ->lockForUpdate()
                ->findOrFail($financialStatement->id);

            if ($lockedStatement->is_locked) {
                throw new \DomainException('Financial statement is already locked.');
            }

            if ($lockedStatement->status !== 'approved') {
                throw new \DomainException('Only approved financial statements can be locked.');
            }

            $lockedStatement->update(['is_locked' => true, 'locked_at' => now()]);

            event(new AuditActionRecorded(
                userId: $request->user()->id,
                action: 'financial_statement.lock',
                companyId: $company->id,
                objectType: 'FinancialStatement',
                objectId: $lockedStatement->id,
                after: $lockedStatement->fresh()->toArray(),
            ));
        });

        return ApiResponse::success(new FinancialStatementResource($financialStatement->fresh()), 'Statement locked.');
    }

    public function export(Request $request, Company $company, FinancialStatement $financialStatement): Response
    {
        $this->authorize('view', $company);
        $this->authorize('view', $financialStatement);

        $format = $request->query('format', 'pdf');

        if (! is_string($format) || ! in_array($format, ['pdf', 'xlsx', 'csv'], true)) {
            throw new \DomainException('Statement export format is not supported.');
        }

        if (in_array($format, ['xlsx', 'csv'], true)) {
            return Excel::download(
                new FinancialStatementExport($financialStatement),
                "statement-{$financialStatement->statement_type}.{$format}",
                $format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX,
            );
        }

        $pdf = Pdf::loadView('exports.financial-statement', [
            'statement' => $financialStatement,
            'company' => $company,
        ]);

        return $pdf->download("statement-{$financialStatement->statement_type}.pdf");
    }
}
