<?php

declare(strict_types=1);

use App\Enums\Audit\EngagementStatus;
use App\Events\Audit\ReviewNoteResolved;
use App\Events\Audit\WorkingPaperSignedOff;
use App\Models\Company;
use App\Models\DocumentRequest;
use App\Models\Engagement;
use App\Models\EngagementMember;
use App\Models\EvidenceFile;
use App\Models\ReviewNote;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkingPaper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    /** @var TestCase $this */
    $this->user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'super_admin', 'display_name' => 'Super Admin']);
    $this->user->roles()->attach($role);

    $this->company = Company::factory()->create();
    $this->user->companies()->attach($this->company);
    $this->engagement = Engagement::create([
        'company_id' => $this->company->id,
        'name' => 'Audit 2026',
        'engagement_type' => 'audit',
        'status' => EngagementStatus::Planning,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'lead_auditor_id' => $this->user->id,
    ]);
});

it('can retrieve and update audit plan', function (): void {
    $this->actingAs($this->user);

    // Get audit plan (creates default)
    $response = $this->getJson("/api/v1/engagements/{$this->engagement->id}/audit-plan");
    $response->assertStatus(200);
    $response->assertJsonPath('data.overall_materiality', '0.00');

    // Update audit plan
    $response = $this->putJson("/api/v1/engagements/{$this->engagement->id}/audit-plan", [
        'overall_materiality' => 50000.00,
        'performance_materiality' => 37500.00,
        'trivial_threshold' => 2500.00,
        'audit_strategy' => 'Detailed substantive testing strategy.',
        'planning_checklist' => [
            ['key' => 'understand_entity', 'name' => 'Understand the Entity', 'is_completed' => true],
        ],
    ]);

    $response->assertStatus(200);
    $response->assertJsonPath('data.overall_materiality', '50000.00');
    $response->assertJsonPath('data.audit_strategy', 'Detailed substantive testing strategy.');
    $response->assertJsonPath('data.planning_checklist.0.is_completed', true);
});

it('can reply to a review note', function (): void {
    $this->actingAs($this->user);

    $wp = WorkingPaper::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'Cash WP',
        'paper_ref' => 'A-1',
        'status' => 'draft',
        'prepared_by' => $this->user->id,
    ]);

    $note = ReviewNote::create([
        'working_paper_id' => $wp->id,
        'content' => 'Please verify cash reconciliation.',
        'created_by' => $this->user->id,
        'status' => 'open',
    ]);

    $response = $this->postJson("/api/v1/engagements/{$this->engagement->id}/review-notes/{$note->id}/reply", [
        'message' => 'I have verified it, and matches perfectly.',
    ]);

    $response->assertStatus(201);
    $response->assertJsonPath('data.message', 'I have verified it, and matches perfectly.');

    // Check relationship is eager loaded
    $indexResponse = $this->getJson("/api/v1/engagements/{$this->engagement->id}/review-notes");
    $indexResponse->assertStatus(200);
    $indexResponse->assertJsonFragment([
        'message' => 'I have verified it, and matches perfectly.',
    ]);
});

it('verifies the document request state machine', function (): void {
    $this->actingAs($this->user);

    // 1. Create request (defaults to requested status)
    $response = $this->postJson("/api/v1/engagements/{$this->engagement->id}/document-requests", [
        'title' => 'Bank Confirmation',
        'due_date' => now()->addDays(5)->toDateString(),
    ]);
    $response->assertStatus(201);
    $request = DocumentRequest::find($response->json('data.id'));
    expect($request->status)->toBe('requested');

    // 2. Open request (moves to in_progress)
    // Client access simulation
    $client = User::factory()->create();
    $clientRole = Role::firstOrCreate(['name' => 'client', 'display_name' => 'Client']);
    $client->roles()->attach($clientRole);
    $this->company->users()->attach($client);
    EngagementMember::create([
        'engagement_id' => $this->engagement->id,
        'user_id' => $client->id,
        'role' => 'client',
    ]);

    $this->actingAs($client);
    $showResponse = $this->getJson("/api/v1/client/document-requests/{$request->id}");
    $showResponse->assertStatus(200);
    expect($request->fresh()->status)->toBe('in_progress');

    // 3. Submit evidence (moves to under_review)
    $evidence = EvidenceFile::create([
        'engagement_id' => $this->engagement->id,
        'uploaded_by' => $client->id,
        'original_name' => 'bank.pdf',
        'storage_path' => 'evidence/bank.pdf',
        'file_size_bytes' => 1024,
        'mime_type' => 'application/pdf',
        'checksum' => 'abc123xyz',
        'status' => 'pending',
    ]);

    $this->actingAs($this->user);
    $submitResponse = $this->postJson("/api/v1/engagements/{$this->engagement->id}/document-requests/{$request->id}/submit", [
        'evidence_file_id' => $evidence->id,
    ]);
    $submitResponse->assertStatus(200);
    expect($request->fresh()->status)->toBe('under_review');

    // 4. Accept / Reject by auditor
    $this->actingAs($this->user);
    $rejectResponse = $this->postJson("/api/v1/engagements/{$this->engagement->id}/document-requests/{$request->id}/reject", [
        'reason' => 'Signatures are missing.',
    ]);
    $rejectResponse->assertStatus(200);
    expect($request->fresh()->status)->toBe('rejected');

    // Submit again from rejected
    $submitResponse2 = $this->postJson("/api/v1/engagements/{$this->engagement->id}/document-requests/{$request->id}/submit", [
        'evidence_file_id' => $evidence->id,
    ]);
    $submitResponse2->assertStatus(200);
    expect($request->fresh()->status)->toBe('under_review');

    // Accept by auditor
    $acceptResponse = $this->postJson("/api/v1/engagements/{$this->engagement->id}/document-requests/{$request->id}/accept");
    $acceptResponse->assertStatus(200);
    expect($request->fresh()->status)->toBe('accepted');
});

it('dispatches WorkingPaperSignedOff event on sign-off', function (): void {
    $this->actingAs($this->user);
    Event::fake([
        WorkingPaperSignedOff::class,
    ]);

    $wp = WorkingPaper::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'Cash WP',
        'paper_ref' => 'A-1',
        'status' => 'draft',
        'prepared_by' => $this->user->id,
    ]);

    $response = $this->postJson("/api/v1/engagements/{$this->engagement->id}/working-papers/{$wp->id}/sign-off");
    $response->assertStatus(200);

    Event::assertDispatched(WorkingPaperSignedOff::class, function ($event) use ($wp) {
        return $event->objectId === $wp->id && $event->userId === $this->user->id;
    });
});

it('dispatches ReviewNoteResolved event on resolve', function (): void {
    $this->actingAs($this->user);
    Event::fake([
        ReviewNoteResolved::class,
    ]);

    $wp = WorkingPaper::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'Cash WP',
        'paper_ref' => 'A-1',
        'status' => 'draft',
        'prepared_by' => $this->user->id,
    ]);

    $note = ReviewNote::create([
        'working_paper_id' => $wp->id,
        'content' => 'Please verify cash reconciliation.',
        'created_by' => $this->user->id,
        'status' => 'open',
    ]);

    $response = $this->postJson("/api/v1/engagements/{$this->engagement->id}/review-notes/{$note->id}/resolve");
    $response->assertStatus(200);

    Event::assertDispatched(ReviewNoteResolved::class, function ($event) use ($note) {
        return $event->objectId === $note->id && $event->userId === $this->user->id;
    });
});

it('can reopen a resolved review note', function (): void {
    $this->actingAs($this->user);

    $wp = WorkingPaper::create([
        'engagement_id' => $this->engagement->id,
        'title' => 'Cash WP',
        'paper_ref' => 'A-1',
        'status' => 'draft',
        'prepared_by' => $this->user->id,
    ]);

    $note = ReviewNote::create([
        'working_paper_id' => $wp->id,
        'content' => 'Please verify cash reconciliation.',
        'created_by' => $this->user->id,
        'status' => 'resolved',
        'resolved_at' => now(),
        'resolved_by' => $this->user->id,
    ]);

    $response = $this->postJson("/api/v1/engagements/{$this->engagement->id}/review-notes/{$note->id}/reopen");
    $response->assertStatus(200);

    expect($note->fresh()->status)->toBe('open');
    expect($note->fresh()->resolved_at)->toBeNull();
    expect($note->fresh()->resolved_by)->toBeNull();
});

it('enforces company level isolation for client portal document requests', function (): void {
    // 1. Create a request for the original company
    $request = DocumentRequest::create([
        'engagement_id' => $this->engagement->id,
        'company_id' => $this->company->id,
        'title' => 'Bank Confirmation',
        'due_date' => now()->addDays(5)->toDateString(),
        'status' => 'requested',
        'requested_by' => $this->user->id,
    ]);

    // 2. Create another company, client user, and engagement not linked to the original company
    $otherCompany = Company::factory()->create();
    $otherClient = User::factory()->create();
    $clientRole = Role::firstOrCreate(['name' => 'client', 'display_name' => 'Client']);
    $otherClient->roles()->attach($clientRole);
    $otherCompany->users()->attach($otherClient);

    // 3. Act as the other client, try to access original company's document request
    $this->actingAs($otherClient);

    $response = $this->getJson("/api/v1/client/document-requests/{$request->id}");

    // It should be 403 Forbidden because they are not in the same company
    $response->assertStatus(403);
});
