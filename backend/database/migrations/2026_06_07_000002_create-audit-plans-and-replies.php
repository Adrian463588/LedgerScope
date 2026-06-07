<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_plans', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('engagement_id')->constrained('engagements')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->decimal('overall_materiality', 15, 2)->default(0.00);
            $table->decimal('performance_materiality', 15, 2)->default(0.00);
            $table->decimal('trivial_threshold', 15, 2)->default(0.00);
            $table->text('audit_strategy')->nullable();
            $table->json('planning_checklist')->nullable();
            $table->timestamps();
        });

        Schema::create('review_note_replies', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('review_note_id')->constrained('review_notes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_note_replies');
        Schema::dropIfExists('audit_plans');
    }
};
