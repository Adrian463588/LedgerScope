<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Engagements ──────────────────────────────────────────────────────
        Schema::create('engagements', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('engagement_type', 50)->comment('audit, review, compilation, advisory');
            $table->string('status', 30)->default('planning');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('lead_auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('scope')->nullable();
            $table->text('objectives')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        // ─── Engagement Members ───────────────────────────────────────────────
        Schema::create('engagement_members', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 50)->default('auditor');
            $table->timestamps();

            $table->unique(['engagement_id', 'user_id']);
        });

        // ─── Risk Assessments ─────────────────────────────────────────────────
        Schema::create('risk_assessments', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->string('risk_area', 200);
            $table->string('risk_level', 20)->default('medium');
            $table->text('description')->nullable();
            $table->text('mitigation')->nullable();
            $table->timestamps();
        });

        // ─── Audit Programs ───────────────────────────────────────────────────
        Schema::create('audit_programs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('objectives')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });

        // ─── Audit Program Steps ──────────────────────────────────────────────
        Schema::create('audit_program_steps', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('audit_program_id')->constrained('audit_programs')->cascadeOnDelete();
            $table->string('step_number', 10);
            $table->text('procedure');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // ─── Working Papers ───────────────────────────────────────────────────
        Schema::create('working_papers', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('paper_ref', 50)->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('prepared_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();

            $table->index(['engagement_id', 'status']);
        });

        // ─── Evidence Files ───────────────────────────────────────────────────
        Schema::create('evidence_files', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('working_paper_id')->nullable()->constrained('working_papers')->nullOnDelete();
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('original_name', 255);
            $table->string('storage_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('checksum', 64)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        // ─── Review Notes ─────────────────────────────────────────────────────
        Schema::create('review_notes', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('working_paper_id')->constrained('working_papers')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->text('content');
            $table->string('status', 20)->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ─── Findings ────────────────────────────────────────────────────────
        Schema::create('findings', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description');
            $table->string('severity', 20)->default('medium');
            $table->string('status', 20)->default('open');
            $table->text('recommendation')->nullable();
            $table->text('management_response')->nullable();
            $table->date('due_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['engagement_id', 'severity', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('findings');
        Schema::dropIfExists('review_notes');
        Schema::dropIfExists('evidence_files');
        Schema::dropIfExists('working_papers');
        Schema::dropIfExists('audit_program_steps');
        Schema::dropIfExists('audit_programs');
        Schema::dropIfExists('risk_assessments');
        Schema::dropIfExists('engagement_members');
        Schema::dropIfExists('engagements');
    }
};
