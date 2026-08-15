<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Report;
use App\Services\Reporting\ReportGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Report $report) {}

    public function handle(ReportGeneratorService $service): void
    {
        $service->generate($this->report);
    }
}
