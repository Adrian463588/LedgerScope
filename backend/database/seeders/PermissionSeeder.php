<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

final class PermissionSeeder extends Seeder
{
    /**
     * All granular permissions grouped by module.
     *
     * @var list<array{name: string, module: string, action: string, description: string}>
     */
    private const PERMISSIONS = [
        // --- Company ---
        ['name' => 'company.view',        'module' => 'company',        'action' => 'view',        'description' => 'View company data'],
        ['name' => 'company.create',      'module' => 'company',        'action' => 'create',      'description' => 'Create companies'],
        ['name' => 'company.update',      'module' => 'company',        'action' => 'update',      'description' => 'Update company data'],
        ['name' => 'company.delete',      'module' => 'company',        'action' => 'delete',      'description' => 'Delete / archive companies'],
        ['name' => 'company.manage_users', 'module' => 'company',        'action' => 'manage_users', 'description' => 'Assign users to companies'],

        // --- Accounting / Chart of Accounts ---
        ['name' => 'account.view',        'module' => 'account',        'action' => 'view',        'description' => 'View chart of accounts'],
        ['name' => 'account.create',      'module' => 'account',        'action' => 'create',      'description' => 'Create accounts'],
        ['name' => 'account.update',      'module' => 'account',        'action' => 'update',      'description' => 'Update accounts'],
        ['name' => 'account.delete',      'module' => 'account',        'action' => 'delete',      'description' => 'Delete / archive accounts'],
        ['name' => 'account.import',      'module' => 'account',        'action' => 'import',      'description' => 'Import chart of accounts'],

        // --- Journal Entries ---
        ['name' => 'journal.view',        'module' => 'journal',        'action' => 'view',        'description' => 'View journal entries'],
        ['name' => 'journal.create',      'module' => 'journal',        'action' => 'create',      'description' => 'Create journal entries'],
        ['name' => 'journal.update',      'module' => 'journal',        'action' => 'update',      'description' => 'Update draft journals'],
        ['name' => 'journal.submit',      'module' => 'journal',        'action' => 'submit',      'description' => 'Submit journal for review'],
        ['name' => 'journal.approve',     'module' => 'journal',        'action' => 'approve',     'description' => 'Approve journal entries'],
        ['name' => 'journal.post',        'module' => 'journal',        'action' => 'post',        'description' => 'Post journal entries to ledger'],
        ['name' => 'journal.reverse',     'module' => 'journal',        'action' => 'reverse',     'description' => 'Reverse posted journal entries'],
        ['name' => 'journal.import',      'module' => 'journal',        'action' => 'import',      'description' => 'Import journal entries'],

        // --- Fiscal Year & Periods ---
        ['name' => 'fiscal_year.view',    'module' => 'fiscal_year',    'action' => 'view',        'description' => 'View fiscal years and periods'],
        ['name' => 'fiscal_year.create',  'module' => 'fiscal_year',    'action' => 'create',      'description' => 'Create fiscal years'],
        ['name' => 'quarter.lock',        'module' => 'quarter',        'action' => 'lock',        'description' => 'Lock a quarter / period'],
        ['name' => 'quarter.unlock',      'module' => 'quarter',        'action' => 'unlock',      'description' => 'Unlock a locked quarter / period'],
        ['name' => 'quarter.approve',     'module' => 'quarter',        'action' => 'approve',     'description' => 'Approve quarterly closing'],

        // --- Trial Balance ---
        ['name' => 'trial_balance.view',        'module' => 'trial_balance', 'action' => 'view',        'description' => 'View trial balance'],
        ['name' => 'trial_balance.generate',    'module' => 'trial_balance', 'action' => 'generate',    'description' => 'Generate trial balance'],

        // --- Reconciliation ---
        ['name' => 'reconciliation.view',       'module' => 'reconciliation', 'action' => 'view',       'description' => 'View reconciliations'],
        ['name' => 'reconciliation.create',     'module' => 'reconciliation', 'action' => 'create',     'description' => 'Create reconciliations'],
        ['name' => 'reconciliation.approve',    'module' => 'reconciliation', 'action' => 'approve',    'description' => 'Approve reconciliations'],

        // --- Financial Statements ---
        ['name' => 'statement.view',            'module' => 'statement',      'action' => 'view',       'description' => 'View financial statements'],
        ['name' => 'statement.generate',        'module' => 'statement',      'action' => 'generate',   'description' => 'Generate financial statements'],
        ['name' => 'statement.approve',         'module' => 'statement',      'action' => 'approve',    'description' => 'Approve financial statements'],
        ['name' => 'statement.lock',            'module' => 'statement',      'action' => 'lock',       'description' => 'Lock approved financial statements'],

        // --- Engagements ---
        ['name' => 'engagement.view',           'module' => 'engagement',     'action' => 'view',       'description' => 'View engagements'],
        ['name' => 'engagement.create',         'module' => 'engagement',     'action' => 'create',     'description' => 'Create engagements'],
        ['name' => 'engagement.update',         'module' => 'engagement',     'action' => 'update',     'description' => 'Update engagements'],
        ['name' => 'engagement.manage_members', 'module' => 'engagement',     'action' => 'manage_members', 'description' => 'Assign members to engagement'],

        // --- Evidence ---
        ['name' => 'evidence.upload',           'module' => 'evidence',       'action' => 'upload',     'description' => 'Upload evidence files'],
        ['name' => 'evidence.view',             'module' => 'evidence',       'action' => 'view',       'description' => 'View evidence files'],
        ['name' => 'evidence.download',         'module' => 'evidence',       'action' => 'download',   'description' => 'Download evidence files'],
        ['name' => 'evidence.review',           'module' => 'evidence',       'action' => 'review',     'description' => 'Accept or reject evidence'],
        ['name' => 'evidence.delete',           'module' => 'evidence',       'action' => 'delete',     'description' => 'Delete unaccepted evidence'],

        // --- Working Papers ---
        ['name' => 'working_paper.view',        'module' => 'working_paper',  'action' => 'view',       'description' => 'View working papers'],
        ['name' => 'working_paper.create',      'module' => 'working_paper',  'action' => 'create',     'description' => 'Create working papers'],
        ['name' => 'working_paper.update',      'module' => 'working_paper',  'action' => 'update',     'description' => 'Update working papers'],
        ['name' => 'working_paper.signoff',     'module' => 'working_paper',  'action' => 'signoff',    'description' => 'Sign off working papers'],

        // --- Audit Findings ---
        ['name' => 'finding.view',              'module' => 'finding',        'action' => 'view',       'description' => 'View audit findings'],
        ['name' => 'finding.create',            'module' => 'finding',        'action' => 'create',     'description' => 'Create audit findings'],
        ['name' => 'finding.update',            'module' => 'finding',        'action' => 'update',     'description' => 'Update audit findings'],
        ['name' => 'finding.approve',           'module' => 'finding',        'action' => 'approve',    'description' => 'Approve high/critical findings'],
        ['name' => 'finding.close',             'module' => 'finding',        'action' => 'close',      'description' => 'Close resolved findings'],

        // --- Reports ---
        ['name' => 'report.generate',           'module' => 'report',         'action' => 'generate',   'description' => 'Generate reports'],
        ['name' => 'report.view',               'module' => 'report',         'action' => 'view',       'description' => 'View and download reports'],
        ['name' => 'report.approve',            'module' => 'report',         'action' => 'approve',    'description' => 'Approve and finalize reports'],

        // --- Audit Logs ---
        ['name' => 'audit_log.view',            'module' => 'audit_log',      'action' => 'view',       'description' => 'View audit trail logs'],

        // --- User Management ---
        ['name' => 'user.view',                 'module' => 'user',           'action' => 'view',       'description' => 'View user list'],
        ['name' => 'user.invite',               'module' => 'user',           'action' => 'invite',     'description' => 'Invite new users'],
        ['name' => 'user.update',               'module' => 'user',           'action' => 'update',     'description' => 'Update user profiles'],
        ['name' => 'user.deactivate',           'module' => 'user',           'action' => 'deactivate', 'description' => 'Deactivate user accounts'],
        ['name' => 'role.manage',               'module' => 'role',           'action' => 'manage',     'description' => 'Manage roles and permissions'],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }
    }
}
