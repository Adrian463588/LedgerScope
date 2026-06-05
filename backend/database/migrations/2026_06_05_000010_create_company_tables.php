<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name', 200);
            $table->string('legal_name', 200)->nullable();
            $table->string('registration_number', 100)->nullable();
            $table->string('tax_id', 100)->nullable();
            $table->string('industry', 100)->nullable();
            $table->char('currency', 3)->default('IDR');
            $table->smallInteger('fiscal_year_start_month')->default(1)->comment('1=Jan, 12=Dec');
            $table->string('address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->nullable()->default('Indonesia');
            $table->string('phone', 30)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('logo_path', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add check constraint via raw SQL (Blueprint::check() not available in Laravel 13)
        DB::statement(
            'ALTER TABLE companies ADD CONSTRAINT chk_companies_fiscal_month CHECK (fiscal_year_start_month >= 1 AND fiscal_year_start_month <= 12)',
        );

        Schema::create('company_users', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->string('job_title', 150)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['company_id', 'user_id'], 'uq_company_users');
            $table->index('user_id');
        });

        Schema::create('company_contacts', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('email', 180)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('role', 100)->nullable()->comment('e.g. CFO, Director');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_contacts');
        Schema::dropIfExists('company_users');
        Schema::dropIfExists('companies');
    }
};
