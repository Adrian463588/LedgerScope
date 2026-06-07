<?php

declare(strict_types=1);

use App\Enums\Audit\EngagementStatus;
use App\Enums\Audit\FindingSeverity;
use App\Models\Company;
use App\Models\Engagement;
use App\Models\Finding;
use App\Models\User;
use App\Services\Audit\FindingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->auditor = User::factory()->create();
    $this->company = Company::factory()->create();

    $this->engagement = Engagement::create([
        'company_id' => $this->company->id,
        'name' => 'Audit FY2024',
        'engagement_type' => 'audit',
        'status' => EngagementStatus::InProgress,
        'start_date' => '2024-01-01',
        'end_date' => '2024-12-31',
        'lead_auditor_id' => $this->auditor->id,
    ]);

    $this->service = app(FindingService::class);
});

it('creates a finding on open status', function (): void {
    $finding = $this->service->create([
        'title' => 'Weak Access Controls',
        'description' => 'Users share passwords.',
        'severity' => FindingSeverity::High,
        'recommendation' => 'Enforce MFA.',
    ], $this->engagement, $this->auditor);

    expect($finding)->toBeInstanceOf(Finding::class);
    expect($finding->status)->toBe(App\Enums\Audit\FindingStatus::Open);
    expect($finding->severity)->toBe(FindingSeverity::High);
    expect($finding->engagement_id)->toBe($this->engagement->id);
});

it('records management response on finding', function (): void {
    $finding = Finding::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'Missing Segregation',
        'description' => 'Same person approves and posts.',
        'severity' => FindingSeverity::Medium->value,
        'status' => 'open',
    ]);

    $this->service->recordManagementResponse($finding, 'We will hire additional staff by Q3.', $this->auditor);

    expect($finding->fresh()->management_response)->toBe('We will hire additional staff by Q3.');
});

it('closes finding when resolved', function (): void {
    $finding = Finding::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'Expired Licenses',
        'description' => 'Software licenses expired.',
        'severity' => FindingSeverity::Low->value,
        'status' => 'open',
    ]);

    $this->service->resolve($finding, $this->auditor);

    expect($finding->fresh()->status)->toBe(App\Enums\Audit\FindingStatus::Resolved);
});

it('cannot resolve an already resolved finding', function (): void {
    $finding = Finding::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'Already Fixed',
        'description' => 'Fixed last quarter.',
        'severity' => FindingSeverity::Low->value,
        'status' => 'resolved',
    ]);

    expect(fn () => $this->service->resolve($finding, $this->auditor))
        ->toThrow(DomainException::class, 'already resolved');
});
