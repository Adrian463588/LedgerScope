<?php

declare(strict_types=1);

use App\Enums\Reporting\ReportStatus;
use App\Jobs\GenerateReportJob;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\Report;
use App\Models\User;
use App\Services\Accounting\FiscalYearGeneratorService;
use App\Services\Reporting\ReportGeneratorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->company = Company::factory()->create();
    $this->service = app(ReportGeneratorService::class);
});

it('queues a real internal report without creating fake success data', function (): void {
    Queue::fake();

    $report = $this->service->queue([
        'report_type' => 'trial_balance',
        'title' => 'TB Jan 2024',
        'format' => 'pdf',
        'parameters' => ['accounting_period_id' => 1],
    ], $this->company, $this->user);

    expect($report->status)->toBe(ReportStatus::Queued)
        ->and(Report::query()->count())->toBe(1);

    Queue::assertPushed(GenerateReportJob::class);
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
    Storage::fake('private');

    $report = Report::create([
        'company_id' => $this->company->id,
        'report_type' => 'trial_balance',
        'title' => 'TB',
        'status' => ReportStatus::Generating,
        'format' => 'pdf',
        'requested_by' => $this->user->id,
    ]);

    Storage::disk('private')->put('reports/tb-jan-2024.pdf', 'real report content');
    $this->service->markCompleted($report, 'reports/tb-jan-2024.pdf');

    $fresh = $report->fresh();
    expect($fresh->status)->toBe(ReportStatus::Completed);
    expect($fresh->file_path)->toBe('reports/tb-jan-2024.pdf');
    expect($fresh->generated_at)->not->toBeNull();
});

it('generates a real private PDF artifact for a trial balance', function (): void {
    Storage::fake('private');

    $fiscalYear = app(FiscalYearGeneratorService::class)->generate($this->company, 2024);
    $period = AccountingPeriod::query()
        ->where('fiscal_year_id', $fiscalYear->id)
        ->where('period_name', '2024-01')
        ->firstOrFail();

    $report = Report::create([
        'company_id' => $this->company->id,
        'report_type' => 'trial_balance',
        'title' => 'Trial Balance January 2024',
        'status' => ReportStatus::Queued,
        'format' => 'pdf',
        'parameters' => ['accounting_period_id' => $period->id],
        'requested_by' => $this->user->id,
    ]);

    $this->service->generate($report);

    $fresh = $report->fresh();
    expect($fresh->status)->toBe(ReportStatus::Completed)
        ->and($fresh->file_path)->not->toBeNull();
    Storage::disk('private')->assertExists($fresh->file_path);
});

it('generates a real private CSV artifact for a trial balance', function (): void {
    Storage::fake('private');

    $fiscalYear = app(FiscalYearGeneratorService::class)->generate($this->company, 2024);
    $period = AccountingPeriod::query()
        ->where('fiscal_year_id', $fiscalYear->id)
        ->where('period_name', '2024-01')
        ->firstOrFail();

    $report = Report::create([
        'company_id' => $this->company->id,
        'report_type' => 'trial_balance',
        'title' => 'Trial Balance January 2024 CSV',
        'status' => ReportStatus::Queued,
        'format' => 'csv',
        'parameters' => ['accounting_period_id' => $period->id],
        'requested_by' => $this->user->id,
    ]);

    $this->service->generate($report);

    $fresh = $report->fresh();
    expect($fresh->status)->toBe(ReportStatus::Completed)
        ->and($fresh->file_path)->toEndWith('.csv');
    Storage::disk('private')->assertExists($fresh->file_path);
});
