<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit logs must be APPEND-ONLY.
     * No updated_at column by design.
     * Application code must never UPDATE or DELETE these records.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('action', 100);
            $table->string('object_type', 100)->nullable();
            $table->unsignedBigInteger('object_id')->nullable();
            $table->jsonb('before_value')->nullable();
            $table->jsonb('after_value')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->jsonb('metadata')->nullable(); // extra context
            // Only created_at — NO updated_at (append-only by design)
            $table->timestamp('created_at')->useCurrent();

            // Composite indexes for high-traffic queries
            $table->index(['company_id', 'action', 'created_at'], 'idx_audit_logs_company_action_created');
            $table->index(['user_id', 'created_at']);
            $table->index(['object_type', 'object_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
