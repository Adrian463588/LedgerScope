<?php

declare(strict_types=1);

use App\Enums\Reporting\ReportStatus;
use App\Models\Company;
use App\Models\Report;
use App\Models\User;
use App\Services\Reporting\ReportGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->company = Company::factory()->create();
    $this->service = app(ReportGeneratorService::class);
});

it('queues a report and returns pending record', function (): void {
    $report = $this->service->queue([
        'report_type' => 'trial_balance',
        'title' => 'TB Jan 2024',
        'format' => 'pdf',
        'parameters' => ['accounting_period_id' => 1],
    ], $this->company, $this->user);

    expect($report)->toBeInstanceOf(Report::class);
    expect($report->status)->toBe(ReportStatus::Pending);
    expect($report->company_id)->toBe($this->company->id);
    expect($report->requested_by)->toBe($this->user->id);
});

it('pending report cannot be re-queued', function (): void {
    $report = Report::create([
        'company_id' => $this->company->id,
        'report_type' => 'trial_balance',
        'title' => 'TB Jan 2024',
        'status' => ReportStatus::Pending,
        'format' => 'pdf',
        'requested_by' => $this->user->id,
    ]);

    expect(fn () => $this->service->markFailed($report, 'Test error'))
        ->not->toThrow(Exception::class);

    expect($report->fresh()->status)->toBe(ReportStatus::Failed);
});

it('marks report completed with file path', function (): void {
    $report = Report::create([
        'company_id' => $this->company->id,
        'report_type' => 'trial_balance',
        'title' => 'TB',
        'status' => ReportStatus::Generating,
        'format' => 'pdf',
        'requested_by' => $this->user->id,
    ]);

    $this->service->markCompleted($report, 'reports/tb-jan-2024.pdf');

    $fresh = $report->fresh();
    expect($fresh->status)->toBe(ReportStatus::Completed);
    expect($fresh->file_path)->toBe('reports/tb-jan-2024.pdf');
    expect($fresh->generated_at)->not->toBeNull();
});
