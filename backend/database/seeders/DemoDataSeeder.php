<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Common\UserStatus;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\Role;
use App\Models\User;
use App\Services\Accounting\FiscalYearGeneratorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        // Create demo user
        $demoUser = User::firstOrCreate(
            ['email' => 'rina@ledgerscope.test'],
            [
                'name' => 'Rina Sari',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        $adminRole = Role::where('name', 'firm_admin')->first();
        if ($adminRole && ! $demoUser->roles()->where('role_id', $adminRole->id)->exists()) {
            $demoUser->roles()->attach($adminRole->id);
        }

        // Create demo company
        $company = Company::firstOrCreate(
            ['name' => 'PT Tech Nusantara'],
            [
                'legal_name' => 'PT Tech Nusantara Tbk',
                'registration_number' => 'REG-2026-001',
                'tax_id' => '01.234.567.8-091.000',
                'industry' => 'Technology',
                'currency' => 'IDR',
                'fiscal_year_start_month' => 1,
                'address' => 'Jl. Sudirman No. 1, Jakarta',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'phone' => '+62-21-555-0101',
                'email' => 'finance@technusantara.test',
                'website' => 'https://technusantara.test',
            ],
        );

        // Attach user to company
        if (! $company->users()->where('user_id', $demoUser->id)->exists()) {
            $company->users()->attach($demoUser->id, ['role_id' => $adminRole->id]);
        }

        // Seed some basic chart of accounts
        $accounts = [
            ['account_code' => '1000', 'account_name' => 'Cash', 'account_type' => 'asset', 'is_active' => true],
            ['account_code' => '1100', 'account_name' => 'Accounts Receivable', 'account_type' => 'asset', 'is_active' => true],
            ['account_code' => '2000', 'account_name' => 'Accounts Payable', 'account_type' => 'liability', 'is_active' => true],
            ['account_code' => '3000', 'account_name' => 'Common Stock', 'account_type' => 'equity', 'is_active' => true],
            ['account_code' => '4000', 'account_name' => 'Sales Revenue', 'account_type' => 'revenue', 'is_active' => true],
            ['account_code' => '5000', 'account_name' => 'Cost of Goods Sold', 'account_type' => 'cost_of_goods_sold', 'is_active' => true],
            ['account_code' => '6000', 'account_name' => 'Operating Expense', 'account_type' => 'expense', 'is_active' => true],
        ];

        foreach ($accounts as $accountData) {
            ChartOfAccount::firstOrCreate(
                ['company_id' => $company->id, 'account_code' => $accountData['account_code']],
                $accountData,
            );
        }

        if (! FiscalYear::query()->where('company_id', $company->id)->where('year', now()->year)->exists()) {
            app(FiscalYearGeneratorService::class)->generate($company, now()->year, $demoUser);
        }
    }
}
