<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

final class RoleSeeder extends Seeder
{
    /** @var list<array<string, mixed>> */
    private const ROLES = [
        [
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'description' => 'Global system administrator with unrestricted access.',
            'is_system' => true,
        ],
        [
            'name' => 'firm_admin',
            'display_name' => 'Firm Admin',
            'description' => 'Manages firm-level users, clients, and configuration.',
            'is_system' => true,
        ],
        [
            'name' => 'partner',
            'display_name' => 'Partner',
            'description' => 'High-level approval and final audit/report sign-off.',
            'is_system' => true,
        ],
        [
            'name' => 'audit_manager',
            'display_name' => 'Audit Manager',
            'description' => 'Planning, review, supervision, and approval.',
            'is_system' => true,
        ],
        [
            'name' => 'senior_auditor',
            'display_name' => 'Senior Auditor',
            'description' => 'Reviews junior auditor work and audit documentation.',
            'is_system' => true,
        ],
        [
            'name' => 'junior_auditor',
            'display_name' => 'Junior Auditor',
            'description' => 'Audit fieldwork execution.',
            'is_system' => true,
        ],
        [
            'name' => 'accountant',
            'display_name' => 'Accountant',
            'description' => 'Bookkeeping, journal entries, trial balance, and financial reporting.',
            'is_system' => true,
        ],
        [
            'name' => 'financial_analyst',
            'display_name' => 'Financial Analyst',
            'description' => 'Financial analysis, ratio analysis, and management reporting.',
            'is_system' => true,
        ],
        [
            'name' => 'client',
            'display_name' => 'Client',
            'description' => 'Limited access — uploads requested documents and views assigned reports.',
            'is_system' => true,
        ],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
