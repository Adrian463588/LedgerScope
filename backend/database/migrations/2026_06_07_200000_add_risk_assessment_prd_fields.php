<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_assessments', function (Blueprint $table): void {
            $table->string('likelihood', 50)->nullable()->after('risk_level');
            $table->string('impact', 50)->nullable()->after('likelihood');
            $table->string('inherent_risk', 50)->nullable()->after('impact');
            $table->string('control_risk', 50)->nullable()->after('inherent_risk');
            $table->string('residual_risk', 50)->nullable()->after('control_risk');
            $table->string('fraud_risk', 50)->nullable()->after('residual_risk');
            $table->string('risk_category', 100)->nullable()->after('fraud_risk');
            $table->boolean('is_significant')->default(false)->after('risk_category');
        });
    }

    public function down(): void
    {
        Schema::table('risk_assessments', function (Blueprint $table): void {
            $table->dropColumn([
                'likelihood',
                'impact',
                'inherent_risk',
                'control_risk',
                'residual_risk',
                'fraud_risk',
                'risk_category',
                'is_significant',
            ]);
        });
    }
};
