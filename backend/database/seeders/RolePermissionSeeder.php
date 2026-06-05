<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

final class RolePermissionSeeder extends Seeder
{
    /**
     * Default permission mapping per role.
     *
     * @var array<string, list<string>>
     */
    private const ROLE_PERMISSIONS = [
        'super_admin' => ['*'], // All permissions via code (handled separately)

        'firm_admin' => [
            'company.view', 'company.create', 'company.update', 'company.delete', 'company.manage_users',
            'user.view', 'user.invite', 'user.update', 'user.deactivate',
            'engagement.view', 'engagement.create', 'engagement.update', 'engagement.manage_members',
            'report.view', 'audit_log.view',
        ],

        'partner' => [
            'company.view',
            'engagement.view',
            'statement.view', 'statement.approve', 'statement.lock',
            'quarter.approve', 'quarter.lock',
            'finding.view', 'finding.approve', 'finding.close',
            'report.view', 'report.approve',
            'audit_log.view',
        ],

        'audit_manager' => [
            'company.view',
            'engagement.view', 'engagement.create', 'engagement.update', 'engagement.manage_members',
            'working_paper.view', 'working_paper.signoff',
            'finding.view', 'finding.approve', 'finding.close',
            'quarter.approve', 'quarter.lock',
            'evidence.view', 'evidence.review', 'evidence.download',
            'statement.view', 'statement.approve',
            'report.generate', 'report.view', 'report.approve',
            'trial_balance.view', 'trial_balance.generate',
            'journal.view', 'journal.approve',
            'audit_log.view',
        ],

        'senior_auditor' => [
            'company.view',
            'engagement.view',
            'evidence.view', 'evidence.review', 'evidence.download', 'evidence.upload',
            'working_paper.view', 'working_paper.create', 'working_paper.update', 'working_paper.signoff',
            'finding.view', 'finding.create', 'finding.update',
            'trial_balance.view',
            'statement.view',
            'report.view',
            'journal.view',
        ],

        'junior_auditor' => [
            'company.view',
            'engagement.view',
            'evidence.upload', 'evidence.view', 'evidence.download',
            'working_paper.view', 'working_paper.create', 'working_paper.update',
            'finding.view', 'finding.create', 'finding.update',
            'journal.view',
        ],

        'accountant' => [
            'company.view',
            'account.view', 'account.create', 'account.update', 'account.delete', 'account.import',
            'journal.view', 'journal.create', 'journal.update', 'journal.submit', 'journal.post', 'journal.reverse', 'journal.import',
            'fiscal_year.view', 'fiscal_year.create',
            'trial_balance.view', 'trial_balance.generate',
            'reconciliation.view', 'reconciliation.create', 'reconciliation.approve',
            'statement.view', 'statement.generate',
            'report.generate', 'report.view',
        ],

        'financial_analyst' => [
            'company.view',
            'journal.view',
            'trial_balance.view', 'trial_balance.generate',
            'statement.view',
            'report.generate', 'report.view',
        ],

        'client' => [
            'evidence.upload', 'evidence.view',
            'report.view', // only approved reports flagged for client
        ],
    ];

    public function run(): void
    {
        $allPermissions = Permission::all()->keyBy('name');

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                continue;
            }

            // Super admin is handled via hasRole check in HasPermissions trait
            if ($permissionNames === ['*']) {
                continue;
            }

            $permissionIds = collect($permissionNames)
                ->filter(fn (string $name): bool => $allPermissions->has($name))
                ->map(fn (string $name): int => $allPermissions->get($name)->id)
                ->all();

            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
