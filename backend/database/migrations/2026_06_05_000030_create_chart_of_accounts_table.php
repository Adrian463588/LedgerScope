<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Import Batches (shared by COA + Journal imports) ─────────────────
        Schema::create('import_batches', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('import_type', 50)->comment('chart_of_accounts, journal_entries');
            $table->string('status', 20)->default('pending');
            $table->string('original_filename', 255)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->string('error_report_path', 500)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'import_type', 'created_at']);
        });

        // ─── Chart of Accounts ────────────────────────────────────────────────
        Schema::create('chart_of_accounts', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('account_code', 80);
            $table->string('account_name', 200);
            $table->string('account_type', 50)->comment('asset, liability, equity, revenue, expense, etc.');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_journal_entries')->default(true);
            $table->tinyInteger('level')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'account_code'], 'uq_coa_company_code');
            $table->index(['company_id', 'account_type']);
            $table->index(['company_id', 'is_active']);
            $table->foreign('parent_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
        Schema::dropIfExists('import_batches');
    }
};
