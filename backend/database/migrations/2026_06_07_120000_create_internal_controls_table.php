<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Internal Controls ─────────────────────────────────────────────────
        Schema::create('internal_controls', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('control_type', 50)->comment('preventive, detective, corrective');
            $table->string('category', 100)->nullable()->comment('financial reporting, operational, compliance, it');
            $table->text('description')->nullable();
            $table->string('frequency', 50)->nullable()->comment('daily, weekly, monthly, quarterly, annual, ad-hoc');
            $table->string('owner', 200)->nullable();
            $table->string('effectiveness', 20)->default('not_tested')
                ->comment('not_tested, effective, partially_effective, ineffective');
            $table->text('testing_procedure')->nullable();
            $table->text('testing_notes')->nullable();
            $table->foreignId('tested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tested_at')->nullable();
            $table->timestamps();

            $table->index(['engagement_id', 'effectiveness']);
        });

        // ─── Control Risks (risks linked to a control) ─────────────────────────
        Schema::create('control_risks', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('internal_control_id')->constrained('internal_controls')->cascadeOnDelete();
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->string('risk_name', 200);
            $table->text('risk_description')->nullable();
            $table->string('likelihood', 20)->default('medium')->comment('low, medium, high');
            $table->string('impact', 20)->default('medium')->comment('low, medium, high');
            $table->string('residual_risk', 20)->default('medium')->comment('low, medium, high');
            $table->text('mitigating_factors')->nullable();
            $table->timestamps();

            $table->index(['internal_control_id']);
            $table->index(['engagement_id', 'residual_risk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_risks');
        Schema::dropIfExists('internal_controls');
    }
};
