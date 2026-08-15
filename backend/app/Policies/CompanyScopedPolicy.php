<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\DocumentRequest;
use App\Models\FinancialStatement;
use App\Models\FiscalYear;
use App\Models\ImportBatch;
use App\Models\JournalEntry;
use App\Models\Quarter;
use App\Models\Reconciliation;
use App\Models\Report;
use App\Models\StatementTemplate;
use App\Models\TrialBalance;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared ownership policy for resources that carry a company_id.
 *
 * Controllers still authorize the parent Company for nested routes; this
 * policy also makes direct Gate checks safe and consistent for child models.
 */
final class CompanyScopedPolicy
{
    /**
     * @var array<class-string, array<string, string>>
     */
    private const PERMISSIONS = [
        AccountingPeriod::class => ['view' => 'fiscal_year.view', 'update' => 'quarter.lock'],
        ChartOfAccount::class => ['view' => 'account.view', 'update' => 'account.update', 'delete' => 'account.delete'],
        DocumentRequest::class => ['view' => 'engagement.view', 'update' => 'engagement.update', 'delete' => 'engagement.update'],
        FinancialStatement::class => ['view' => 'statement.view', 'update' => 'statement.approve'],
        FiscalYear::class => ['view' => 'fiscal_year.view', 'update' => 'fiscal_year.create'],
        ImportBatch::class => ['view' => 'account.view', 'update' => 'account.import'],
        JournalEntry::class => ['view' => 'journal.view', 'update' => 'journal.update', 'delete' => 'journal.update'],
        Quarter::class => ['view' => 'fiscal_year.view', 'update' => 'quarter.lock'],
        Reconciliation::class => ['view' => 'reconciliation.view', 'update' => 'reconciliation.approve'],
        Report::class => ['view' => 'report.view', 'update' => 'report.approve'],
        StatementTemplate::class => ['view' => 'statement.view', 'update' => 'statement.generate'],
        TrialBalance::class => ['view' => 'trial_balance.view', 'update' => 'trial_balance.generate'],
    ];

    public function view(User $user, Model $resource): bool
    {
        return $this->allows($user, $resource, 'view');
    }

    public function update(User $user, Model $resource): bool
    {
        return $this->allows($user, $resource, 'update');
    }

    public function delete(User $user, Model $resource): bool
    {
        return $this->allows($user, $resource, 'delete');
    }

    private function allows(User $user, Model $resource, string $ability): bool
    {
        $companyId = $this->companyId($resource);
        if ($companyId === null) {
            return false;
        }

        $permission = self::PERMISSIONS[$resource::class][$ability]
            ?? ($ability === 'view' ? 'company.view' : 'company.update');

        if (! $user->hasPermission($permission)) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->companies()->whereKey($companyId)->exists();
    }

    private function companyId(Model $resource): ?int
    {
        $companyId = $resource->getAttribute('company_id');

        if ($companyId === null) {
            return null;
        }

        return (int) $companyId;
    }
}
