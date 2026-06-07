<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Report;
use App\Services\Reporting\ReportGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

final class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Report $report) {}

    public function handle(ReportGeneratorService $service): void
    {
        $service->markGenerating($this->report);

        try {
            // Generate report content mock
            $content = "Report Title: " . $this->report->title . "\nType: " . $this->report->report_type . "\nGenerated at: " . now()->toDateTimeString();
            $fileName = 'reports/' . $this->report->id . '_' . uniqid() . '.' . ($this->report->format ?? 'pdf');

            Storage::disk('private')->put($fileName, $content);

            $service->markCompleted($this->report, $fileName);
        } catch (\Throwable $e) {
            $service->markFailed($this->report, $e->getMessage());
        }
    }
}
