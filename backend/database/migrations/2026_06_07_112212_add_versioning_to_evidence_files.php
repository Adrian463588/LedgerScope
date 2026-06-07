<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_files', function (Blueprint $table) {
            $table->integer('version')->default(1);
            $table->foreignId('previous_version_id')->nullable()->constrained('evidence_files')->nullOnDelete();
            $table->jsonb('custody_log')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evidence_files', function (Blueprint $table) {
            $table->dropForeign(['previous_version_id']);
            $table->dropColumn(['version', 'previous_version_id', 'custody_log']);
        });
    }
};
