<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Fiscal Years ─────────────────────────────────────────────────────
        Schema::create('fiscal_years', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->smallInteger('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'year'], 'uq_fiscal_years_company_year');
            $table->index('company_id');
        });

        // ─── Quarters ─────────────────────────────────────────────────────────
        Schema::create('quarters', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->char('quarter_code', 2)->comment('Q1, Q2, Q3, Q4');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('open');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('unlock_reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'fiscal_year_id', 'quarter_code'], 'uq_quarters_company_year_code');
        });

        // ─── Accounting Periods ───────────────────────────────────────────────
        Schema::create('accounting_periods', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignId('quarter_id')->nullable()->constrained('quarters')->nullOnDelete();
            $table->string('period_name', 50)->comment('e.g. 2024-01, 2024-Q1');
            $table->string('period_type', 20)->default('monthly');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('open');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('unlock_reason')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'period_name', 'period_type'], 'uq_accounting_periods_name_type');
            $table->index(['company_id', 'start_date', 'end_date']);
        });

        // ─── Quarter Closing Checklists ───────────────────────────────────────
        Schema::create('quarter_closing_checklists', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('quarter_id')->constrained('quarters')->cascadeOnDelete();
            $table->string('checklist_key', 80);
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['quarter_id', 'checklist_key'], 'uq_checklist_quarter_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quarter_closing_checklists');
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('quarters');
        Schema::dropIfExists('fiscal_years');
    }
};
