<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Journal Entries ──────────────────────────────────────────────────
        Schema::create('journal_entries', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('accounting_period_id')->constrained('accounting_periods');
            $table->string('journal_number', 50)->nullable()->comment('Auto-generated on post');
            $table->string('reference', 100)->nullable();
            $table->text('description');
            $table->date('journal_date');
            $table->string('status', 20)->default('draft');
            $table->string('source_type', 30)->default('manual');
            $table->unsignedBigInteger('reversed_from_id')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'journal_number'], 'uq_journal_number');
            $table->index(['company_id', 'status', 'journal_date']);
            $table->index(['company_id', 'accounting_period_id']);
            $table->foreign('reversed_from_id')->references('id')->on('journal_entries')->nullOnDelete();
        });

        // ─── Journal Entry Lines ──────────────────────────────────────────────
        Schema::create('journal_entry_lines', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->text('description')->nullable();
            $table->decimal('debit', 20, 2)->default('0.00');
            $table->decimal('credit', 20, 2)->default('0.00');
            $table->string('currency', 3)->default('IDR');
            $table->decimal('exchange_rate', 20, 6)->default('1.000000');
            $table->timestamps();

            $table->index(['journal_entry_id', 'account_id']);
        });

        // Check constraints via raw SQL
        DB::statement('ALTER TABLE journal_entry_lines ADD CONSTRAINT chk_jel_debit_positive CHECK (debit >= 0)');
        DB::statement('ALTER TABLE journal_entry_lines ADD CONSTRAINT chk_jel_credit_positive CHECK (credit >= 0)');
        DB::statement('ALTER TABLE journal_entry_lines ADD CONSTRAINT chk_jel_not_both CHECK (NOT (debit > 0 AND credit > 0))');
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
    }
};
