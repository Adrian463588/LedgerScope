<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Reports ──────────────────────────────────────────────────────────
        Schema::create('reports', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('report_type', 50)->comment('trial_balance, income_statement, balance_sheet, audit_summary, etc.');
            $table->string('title', 200);
            $table->string('status', 20)->default('pending');
            $table->string('format', 10)->default('pdf')->comment('pdf, xlsx');
            $table->jsonb('parameters')->nullable()->comment('date range, period, filters');
            $table->string('file_path', 500)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status', 'created_at']);
        });

        // ─── Report Downloads ─────────────────────────────────────────────────
        Schema::create('report_downloads', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('downloaded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_downloads');
        Schema::dropIfExists('reports');
    }
};
