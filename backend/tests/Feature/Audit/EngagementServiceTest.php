<?php

declare(strict_types=1);

use App\Enums\Audit\EngagementStatus;
use App\Models\Company;
use App\Models\Engagement;
use App\Models\User;
use App\Services\Audit\EngagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->lead    = User::factory()->create();
    $this->company = Company::factory()->create();
    $this->service = app(EngagementService::class);
});

it('creates an engagement in draft status', function (): void {
    $engagement = $this->service->create([
        'name'            => 'Audit 2024',
        'engagement_type' => 'audit',
        'start_date'      => '2024-01-01',
        'end_date'        => '2024-12-31',
    ], $this->company, $this->lead);

    expect($engagement->status)->toBe(EngagementStatus::Planning);
    expect($engagement->company_id)->toBe($this->company->id);
    expect($engagement->lead_auditor_id)->toBe($this->lead->id);
});

it('activates engagement from planning status', function (): void {
    $engagement = Engagement::create([
        'company_id'      => $this->company->id,
        'name'            => 'Audit 2024',
        'engagement_type' => 'audit',
        'status'          => EngagementStatus::Planning,
        'start_date'      => '2024-01-01',
        'end_date'        => '2024-12-31',
        'lead_auditor_id' => $this->lead->id,
    ]);

    $this->service->activate($engagement, $this->lead);

    expect($engagement->fresh()->status)->toBe(EngagementStatus::InProgress);
});

it('cannot activate already active engagement', function (): void {
    $engagement = Engagement::create([
        'company_id'      => $this->company->id,
        'name'            => 'Active',
        'engagement_type' => 'audit',
        'status'          => EngagementStatus::InProgress,
        'start_date'      => '2024-01-01',
        'end_date'        => '2024-12-31',
        'lead_auditor_id' => $this->lead->id,
    ]);

    expect(fn () => $this->service->activate($engagement, $this->lead))
        ->toThrow(\DomainException::class, 'already active');
});

it('closes engagement and sets completed_at', function (): void {
    $engagement = Engagement::create([
        'company_id'      => $this->company->id,
        'name'            => 'Closing',
        'engagement_type' => 'audit',
        'status'          => EngagementStatus::InProgress,
        'start_date'      => '2024-01-01',
        'end_date'        => '2024-12-31',
        'lead_auditor_id' => $this->lead->id,
    ]);

    $this->service->close($engagement, $this->lead);

    $fresh = $engagement->fresh();
    expect($fresh->status)->toBe(EngagementStatus::Completed);
    expect($fresh->completed_at)->not->toBeNull();
});
