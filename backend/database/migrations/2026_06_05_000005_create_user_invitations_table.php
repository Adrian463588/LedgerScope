<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_invitations', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('email', 180);
            $table->string('name', 150);
            $table->string('token', 64)->unique();
            $table->string('status', 30)->default('pending');
            $table->foreignId('role_id')->constrained('roles');
            $table->foreignId('invited_by')->constrained('users');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->nullableMorphs('invitable'); // for company or engagement scoped invitations
            $table->timestamps();

            $table->index('token');
            $table->index('email');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_invitations');
    }
};
