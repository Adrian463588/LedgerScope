<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Statement Templates ──────────────────────────────────────────────
        Schema::create('statement_templates', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('statement_type', 30)->comment('balance_sheet, income_statement, cash_flow, equity_changes');
            $table->jsonb('structure')->nullable()->comment('JSON tree of account groupings');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['company_id', 'statement_type']);
        });

        // ─── Financial Statements ─────────────────────────────────────────────
        Schema::create('financial_statements', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('accounting_period_id')->constrained('accounting_periods');
            $table->foreignId('template_id')->nullable()->constrained('statement_templates')->nullOnDelete();
            $table->string('statement_type', 30);
            $table->string('status', 20)->default('draft');
            $table->integer('version')->default(1);
            $table->boolean('is_locked')->default(false);
            $table->jsonb('data')->nullable();
            $table->foreignId('generated_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'accounting_period_id', 'statement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_statements');
        Schema::dropIfExists('statement_templates');
    }
};
