<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\Engagement;
use App\Models\User;
use App\Services\Evidence\EvidenceService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('evidence upload persists a private company and engagement scoped file', function (): void {
    Storage::fake('private');

    $uploadedBy = User::factory()->create();
    $company = Company::factory()->create();
    $engagement = Engagement::create([
        'company_id' => $company->id,
        'name' => 'Audit 2026',
        'engagement_type' => 'audit',
        'status' => 'planning',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'lead_auditor_id' => $uploadedBy->id,
    ]);
    $file = UploadedFile::fake()->create('ledger.pdf', 10, 'application/pdf');

    $evidence = app(EvidenceService::class)->upload(
        file: $file,
        engagement: $engagement,
        uploadedBy: $uploadedBy,
    );

    expect($evidence->storage_path)
        ->toStartWith("companies/{$company->id}/engagements/{$engagement->id}/evidence/");
    Storage::disk('private')->assertExists($evidence->storage_path);
});
