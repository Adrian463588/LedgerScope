<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing PRD columns to existing tables and create new module tables.
 *
 * - findings: add company_id, root_cause, impact, action_plan, responsible_person, created_by, approved_by, category, is_repeat (B-05)
 * - evidence_files: add description, accepted_by, accepted_at, rejected_by, rejected_at, rejection_reason (EPIC 4)
 * - working_papers: add locked_at, locked_by, sign_off_at, sign_off_by (EPIC 6)
 * - document_requests: new table for PBC Portal (EPIC 5)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── Findings — add missing PRD fields ───────────────────────────────
        Schema::table('findings', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->after('engagement_id')
                ->constrained('companies')->nullOnDelete();
            $table->string('category', 100)->nullable()->after('title');
            $table->text('root_cause')->nullable()->after('description');
            $table->text('impact')->nullable()->after('root_cause');
            $table->text('action_plan')->nullable()->after('recommendation');
            $table->string('responsible_person', 200)->nullable()->after('action_plan');
            $table->foreignId('created_by')->nullable()->after('assigned_to')
                ->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')
                ->constrained('users')->nullOnDelete();
            $table->boolean('is_repeat')->default(false)->after('approved_by');
            $table->softDeletes();

            $table->index('company_id');
        });

        // ─── Evidence Files — add accept/reject columns ───────────────────────
        Schema::table('evidence_files', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('status');
            $table->foreignId('accepted_by')->nullable()->after('description')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable()->after('accepted_by');
            $table->foreignId('rejected_by')->nullable()->after('accepted_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
            $table->softDeletes();
        });

        // ─── Working Papers — add lock and sign-off columns ───────────────────
        Schema::table('working_papers', function (Blueprint $table): void {
            $table->timestamp('sign_off_at')->nullable()->after('reviewed_at');
            $table->foreignId('sign_off_by')->nullable()->after('sign_off_at')
                ->constrained('users')->nullOnDelete();
            $table->boolean('is_locked')->default(false)->after('sign_off_by');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->foreignId('locked_by')->nullable()->after('locked_at')
                ->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        // ─── Document Requests (PBC Portal) ──────────────────────────────────
        Schema::create('document_requests', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('requested')
                ->comment('requested,submitted,accepted,rejected,overdue,cancelled');
            $table->date('due_date')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('evidence_file_id')->nullable()
                ->constrained('evidence_files')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['engagement_id', 'status']);
        });

        // ─── EnsureCompanyAccess: add QuarterClosingChecklist is_required ─────
        // B-08 fix: The checklist gate requires is_required column to exist
        Schema::table('quarter_closing_checklists', function (Blueprint $table): void {
            if (! Schema::hasColumn('quarter_closing_checklists', 'is_required')) {
                $table->boolean('is_required')->default(true)->after('checklist_key');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_requests');

        Schema::table('quarter_closing_checklists', function (Blueprint $table): void {
            if (Schema::hasColumn('quarter_closing_checklists', 'is_required')) {
                $table->dropColumn('is_required');
            }
        });

        Schema::table('working_papers', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn(['sign_off_at', 'sign_off_by', 'is_locked', 'locked_at', 'locked_by']);
        });

        Schema::table('evidence_files', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'description', 'accepted_by', 'accepted_at',
                'rejected_by', 'rejected_at', 'rejection_reason',
            ]);
        });

        Schema::table('findings', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'company_id', 'category', 'root_cause', 'impact',
                'action_plan', 'responsible_person',
                'created_by', 'approved_by', 'is_repeat',
            ]);
        });
    }
};
