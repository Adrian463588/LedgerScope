<?php

declare(strict_types=1);

use App\Enums\Audit\EngagementStatus;
use App\Models\Company;
use App\Models\Engagement;
use App\Models\User;
use App\Models\WorkingPaper;
use App\Services\Audit\WorkingPaperService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->lead = User::factory()->create();
    $this->company = Company::factory()->create();

    $this->engagement = Engagement::create([
        'company_id' => $this->company->id,
        'name' => 'Audit 2024',
        'engagement_type' => 'audit',
        'status' => EngagementStatus::InProgress,
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'lead_auditor_id' => $this->lead->id,
    ]);

    $this->service = app(WorkingPaperService::class);
});

it('creates working paper in draft status', function (): void {
    $wp = $this->service->create([
        'title' => 'Cash Walkthrough',
        'paper_ref' => 'A-1',
        'content' => 'Procedures applied...',
    ], $this->engagement, $this->lead);

    expect($wp)->toBeInstanceOf(WorkingPaper::class);
    expect($wp->status)->toBe('draft');
    expect($wp->engagement_id)->toBe($this->engagement->id);
    expect($wp->prepared_by)->toBe($this->lead->id);
});

it('submits working paper for review', function (): void {
    $wp = WorkingPaper::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'Bank Confirm',
        'paper_ref' => 'A-2',
        'status' => 'draft',
        'prepared_by' => $this->lead->id,
    ]);

    $this->service->submitForReview($wp, $this->lead);

    expect($wp->fresh()->status)->toBe('in_review');
});

it('cannot submit already reviewed paper', function (): void {
    $wp = WorkingPaper::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'AR Confirm',
        'paper_ref' => 'B-1',
        'status' => 'reviewed',
        'prepared_by' => $this->lead->id,
    ]);

    expect(fn () => $this->service->submitForReview($wp, $this->lead))
        ->toThrow(DomainException::class, 'already reviewed');
});

it('approves working paper and sets reviewed_at', function (): void {
    $reviewer = User::factory()->create();

    $wp = WorkingPaper::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'Inventory Count',
        'paper_ref' => 'C-1',
        'status' => 'in_review',
        'prepared_by' => $this->lead->id,
    ]);

    $this->service->approve($wp, $reviewer);

    $fresh = $wp->fresh();
    expect($fresh->status)->toBe('reviewed');
    expect($fresh->reviewed_by)->toBe($reviewer->id);
    expect($fresh->reviewed_at)->not->toBeNull();
});
