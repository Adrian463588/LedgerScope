<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Enums\Reporting\ReportStatus;
use App\Events\AuditActionRecorded;
use App\Exports\ReportDataExport;
use App\Jobs\GenerateReportJob;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\FinancialStatement;
use App\Models\Report;
use App\Models\TrialBalance;
use App\Models\User;
use App\Services\Accounting\StatementBuilderService;
use App\Services\Accounting\TrialBalanceService;
use App\Support\Decimal;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

final class ReportGeneratorService
{
    private const INTERNAL_REPORT_TYPES = [
        'trial_balance',
        'income_statement',
        'balance_sheet',
        'cash_flow',
        'equity_changes',
        'audit_report',
        'engagement_summary',
    ];

    public function __construct(
        private readonly StatementBuilderService $statementBuilder,
        private readonly TrialBalanceService $trialBalanceService,
    ) {}

    /**
     * Queue a new report generation request.
     * Returns a Pending Report record; actual generation dispatched to jobs queue.
     *
     * @param  array<string, mixed>  $data
     */
    public function queue(array $data, Company $company, User $requestedBy): Report
    {
        $reportType = (string) ($data['report_type'] ?? '');
        $format = (string) ($data['format'] ?? 'pdf');

        if (! in_array($reportType, self::INTERNAL_REPORT_TYPES, true)) {
            throw new DomainException("Report type [{$reportType}] is not supported.");
        }

        if (! in_array($format, ['pdf', 'xlsx', 'csv'], true)) {
            throw new DomainException("Report format [{$format}] is not supported.");
        }

        $parameters = is_array($data['parameters'] ?? null) ? $data['parameters'] : [];

        return DB::transaction(function () use ($data, $company, $requestedBy, $reportType, $format, $parameters): Report {
            /** @var Report $report */
            $report = Report::create([
                'company_id' => $company->id,
                'report_type' => $reportType,
                'title' => (string) $data['title'],
                'status' => ReportStatus::Queued->value,
                'format' => $format,
                'parameters' => $parameters,
                'requested_by' => $requestedBy->id,
            ]);

            event(new AuditActionRecorded(
                userId: $requestedBy->id,
                action: 'report.requested',
                companyId: $company->id,
                objectType: 'Report',
                objectId: $report->id,
                after: $report->toArray(),
            ));

            GenerateReportJob::dispatch($report)->afterCommit();

            return $report;
        });
    }

    public function generate(Report $report): void
    {
        $this->markGenerating($report);

        try {
            $freshReport = $report->fresh(['company', 'requestedBy']);
            if (! $freshReport instanceof Report) {
                throw new DomainException('Report could not be reloaded for generation.');
            }

            $artifact = $this->buildArtifact($freshReport);
            $this->markCompleted($report, $artifact);
        } catch (\Throwable $exception) {
            Log::error('Report generation failed.', [
                'report_id' => $report->id,
                'exception' => $exception,
            ]);
            $this->markFailed($report, 'Report generation failed.');

            throw $exception;
        }
    }

    public function markUnavailable(Report $report): void
    {
        $report->update([
            'status' => ReportStatus::Failed,
            'error_message' => 'Report generation is not available yet.',
        ]);
    }

    /**
     * Mark report as completed with stored file path.
     */
    public function markCompleted(Report $report, string $filePath): void
    {
        if (! Storage::disk('private')->exists($filePath)) {
            throw new DomainException('Report file is not available in private storage.');
        }

        $report->update([
            'status' => ReportStatus::Completed,
            'file_path' => $filePath,
            'generated_at' => now(),
        ]);
    }

    /**
     * Mark report as failed with error message.
     */
    public function markFailed(Report $report, string $errorMessage): void
    {
        $report->update([
            'status' => ReportStatus::Failed,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Mark report as generating (job picked up from queue).
     */
    public function markGenerating(Report $report): void
    {
        $report->update(['status' => ReportStatus::Generating]);
    }

    public function approve(Report $report, User $approvedBy): Report
    {
        return DB::transaction(function () use ($report, $approvedBy): Report {
            /** @var Report $lockedReport */
            $lockedReport = Report::query()->lockForUpdate()->findOrFail($report->id);

            if ($lockedReport->status !== ReportStatus::Completed) {
                throw new DomainException('Only completed reports can be approved.');
            }

            if (! $lockedReport->file_path || ! Storage::disk('private')->exists($lockedReport->file_path)) {
                throw new DomainException('Report file is not available for approval.');
            }

            $lockedReport->update([
                'status' => ReportStatus::Approved,
                'approved_by' => $approvedBy->id,
                'approved_at' => now(),
            ]);

            event(new AuditActionRecorded(
                userId: $approvedBy->id,
                action: 'report.approve',
                companyId: $lockedReport->company_id,
                objectType: 'Report',
                objectId: $lockedReport->id,
                after: $lockedReport->fresh()->toArray(),
            ));

            return $lockedReport->fresh();
        });
    }

    private function buildArtifact(Report $report): string
    {
        $data = $this->buildReportData($report);
        $extension = match ($report->format) {
            'xlsx' => 'xlsx',
            'csv' => 'csv',
            default => 'pdf',
        };
        $path = "reports/{$report->company_id}/{$report->id}-".Str::uuid().".{$extension}";

        if (in_array($extension, ['xlsx', 'csv'], true)) {
            $stored = Excel::store(
                new ReportDataExport($data['headings'], $data['rows']),
                $path,
                'private',
                $extension === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX,
            );

            if (! $stored) {
                throw new DomainException('Report artifact could not be stored.');
            }
        } else {
            Storage::disk('private')->put($path, Pdf::loadView('exports.report', [
                ...$data,
                'company' => $report->company,
                'title' => $report->title,
                'generatedAt' => now()->toIso8601String(),
            ])->output());
        }

        return $path;
    }

    /**
     * @return array{headings: list<string>, rows: list<list<string|int|null>>}
     */
    private function buildReportData(Report $report): array
    {
        $parameters = is_array($report->parameters) ? $report->parameters : [];

        return match ($report->report_type) {
            'income_statement', 'balance_sheet', 'cash_flow', 'equity_changes' => $this->statementData($report, $parameters),
            'trial_balance' => $this->trialBalanceData($report, $parameters),
            'audit_report', 'engagement_summary' => $this->engagementData($report, $parameters),
            default => throw new DomainException('Unsupported report type.'),
        };
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array{headings: list<string>, rows: list<list<string|int|null>>}
     */
    private function statementData(Report $report, array $parameters): array
    {
        $company = $report->company()->firstOrFail();
        $requestedBy = $report->requestedBy()->firstOrFail();
        $period = $this->period($company, $parameters);
        $statement = isset($parameters['financial_statement_id'])
            ? FinancialStatement::query()
                ->where('company_id', $report->company_id)
                ->findOrFail((int) $parameters['financial_statement_id'])
            : $this->statementBuilder->build(
                $company,
                $period,
                match ($report->report_type) {
                    'income_statement' => 'income_statement',
                    'balance_sheet' => 'balance_sheet',
                    'cash_flow' => 'cash_flow',
                    'equity_changes' => 'equity_changes',
                    default => throw new DomainException('Unsupported statement type.'),
                },
                $requestedBy,
            );

        return [
            'headings' => ['Account Code', 'Account Name', 'Amount'],
            'rows' => $this->statementRows($statement),
        ];
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array{headings: list<string>, rows: list<list<string|int|null>>}
     */
    private function trialBalanceData(Report $report, array $parameters): array
    {
        $company = $report->company()->firstOrFail();
        $requestedBy = $report->requestedBy()->firstOrFail();
        $period = $this->period($company, $parameters);
        $trialBalance = TrialBalance::query()
            ->where('company_id', $report->company_id)
            ->where('accounting_period_id', $period->id)
            ->latest('generated_at')
            ->with('lines.account')
            ->first();

        $trialBalance ??= $this->trialBalanceService->generate($company, $period, $requestedBy);
        $trialBalance->loadMissing('lines.account');

        $rows = $trialBalance->lines->map(fn ($line): array => [
            (string) ($line->account?->account_code ?? ''),
            (string) ($line->account?->account_name ?? ''),
            Decimal::format((string) $line->closing_debit),
            Decimal::format((string) $line->closing_credit),
        ])->values()->all();

        return [
            'headings' => ['Account Code', 'Account Name', 'Closing Debit', 'Closing Credit'],
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array{headings: list<string>, rows: list<list<string|int|null>>}
     */
    private function engagementData(Report $report, array $parameters): array
    {
        $engagementId = (int) ($parameters['engagement_id'] ?? 0);
        $company = $report->company()->firstOrFail();
        $engagement = $company->engagements()->findOrFail($engagementId);

        return [
            'headings' => ['Metric', 'Value'],
            'rows' => [
                ['Engagement', $engagement->name],
                ['Status', $engagement->status->value],
                ['Start date', $engagement->start_date?->toDateString()],
                ['End date', $engagement->end_date?->toDateString()],
                ['Working papers', $engagement->workingPapers()->count()],
                ['Evidence files', $engagement->evidenceFiles()->count()],
                ['Open findings', $engagement->findings()->whereNotIn('status', ['resolved', 'closed'])->count()],
                ['Document requests', $engagement->documentRequests()->count()],
            ],
        ];
    }

    /**
     * @return list<list<string|int|null>>
     */
    private function statementRows(FinancialStatement $statement): array
    {
        /** @var array<string, mixed> $data */
        $data = is_array($statement->data) ? $statement->data : [];
        $rows = [];
        $sections = [
            'revenue',
            'cogs',
            'expenses',
            'other_income',
            'other_expenses',
            'assets',
            'liabilities_and_equity',
            'operating_activities',
            'investing_activities',
            'financing_activities',
            'equity',
        ];

        foreach ($sections as $section) {
            $sectionData = $data[$section] ?? null;
            if (! is_array($sectionData) || ! is_array($sectionData['lines'] ?? null)) {
                continue;
            }

            foreach ($sectionData['lines'] as $line) {
                if (! is_array($line)) {
                    continue;
                }

                $rows[] = [
                    (string) ($line['account_code'] ?? ''),
                    (string) ($line['account_name'] ?? ''),
                    Decimal::format((string) ($line['amount'] ?? '0.00')),
                ];
            }
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function period(Company $company, array $parameters): AccountingPeriod
    {
        $periodId = (int) ($parameters['accounting_period_id'] ?? 0);
        if ($periodId < 1) {
            throw new DomainException('An accounting_period_id is required for this report.');
        }

        return $company->accountingPeriods()->findOrFail($periodId);
    }
}
