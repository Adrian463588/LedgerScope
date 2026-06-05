<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Enums\Reporting\ReportStatus;
use App\Models\Company;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ReportGeneratorService
{
    /**
     * Queue a new report generation request.
     * Returns a Pending Report record; actual generation dispatched to jobs queue.
     *
     * @param  array<string, mixed>  $data
     */
    public function queue(array $data, Company $company, User $requestedBy): Report
    {
        return DB::transaction(function () use ($data, $company, $requestedBy): Report {
            /** @var Report $report */
            $report = Report::create([
                'company_id' => $company->id,
                'report_type' => $data['report_type'],
                'title' => $data['title'],
                'format' => $data['format'] ?? 'pdf',
                'parameters' => $data['parameters'] ?? null,
                'status' => ReportStatus::Pending,
                'requested_by' => $requestedBy->id,
            ]);

            // Dispatch generation job (Phase 9 full impl):
            // GenerateReportJob::dispatch($report);

            return $report;
        });
    }

    /**
     * Mark report as completed with stored file path.
     */
    public function markCompleted(Report $report, string $filePath): void
    {
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
}
