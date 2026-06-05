<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Trial Balances ───────────────────────────────────────────────────
        Schema::create('trial_balances', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('accounting_period_id')->constrained('accounting_periods');
            $table->string('status', 20)->default('draft');
            $table->decimal('total_debit', 20, 2)->default('0.00');
            $table->decimal('total_credit', 20, 2)->default('0.00');
            $table->boolean('is_balanced')->default(false);
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'accounting_period_id']);
        });

        // ─── Trial Balance Lines ──────────────────────────────────────────────
        Schema::create('trial_balance_lines', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('trial_balance_id')->constrained('trial_balances')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->decimal('opening_debit', 20, 2)->default('0.00');
            $table->decimal('opening_credit', 20, 2)->default('0.00');
            $table->decimal('period_debit', 20, 2)->default('0.00');
            $table->decimal('period_credit', 20, 2)->default('0.00');
            $table->decimal('closing_debit', 20, 2)->default('0.00');
            $table->decimal('closing_credit', 20, 2)->default('0.00');
            $table->timestamps();
        });

        // ─── Reconciliations ─────────────────────────────────────────────────
        Schema::create('reconciliations', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->foreignId('accounting_period_id')->constrained('accounting_periods');
            $table->string('reconciliation_type', 30)->default('bank');
            $table->string('status', 20)->default('draft');
            $table->decimal('book_balance', 20, 2)->default('0.00');
            $table->decimal('bank_balance', 20, 2)->default('0.00');
            $table->decimal('difference', 20, 2)->default('0.00');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // ─── Reconciliation Items ─────────────────────────────────────────────
        Schema::create('reconciliation_items', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('reconciliation_id')->constrained('reconciliations')->cascadeOnDelete();
            $table->foreignId('journal_line_id')->nullable()->constrained('journal_entry_lines')->nullOnDelete();
            $table->string('item_type', 30)->default('unmatched');
            $table->decimal('amount', 20, 2);
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->string('reference', 100)->nullable();
            $table->boolean('is_matched')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_items');
        Schema::dropIfExists('reconciliations');
        Schema::dropIfExists('trial_balance_lines');
        Schema::dropIfExists('trial_balances');
    }
};
