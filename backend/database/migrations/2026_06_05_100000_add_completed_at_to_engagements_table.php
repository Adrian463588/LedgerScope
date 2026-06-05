<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add completed_at column to engagements table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('engagements', function (Blueprint $table): void {
            $table->timestamp('completed_at')->nullable()->after('objectives');
        });
    }

    public function down(): void
    {
        Schema::table('engagements', function (Blueprint $table): void {
            $table->dropColumn('completed_at');
        });
    }
};
