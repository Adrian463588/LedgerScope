<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AccountingPeriod;
use App\Models\AuditLog;
use App\Models\AuditPlan;
use App\Models\AuditProgram;
use App\Models\AuditProgramStep;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\ControlRisk;
use App\Models\DocumentRequest;
use App\Models\Engagement;
use App\Models\EvidenceFile;
use App\Models\FinancialStatement;
use App\Models\Finding;
use App\Models\FiscalYear;
use App\Models\ImportBatch;
use App\Models\InternalControl;
use App\Models\JournalEntry;
use App\Models\Quarter;
use App\Models\Reconciliation;
use App\Models\Report;
use App\Models\ReviewNote;
use App\Models\RiskAssessment;
use App\Models\Role;
use App\Models\StatementTemplate;
use App\Models\TrialBalance;
use App\Models\User;
use App\Models\WorkingPaper;
use App\Policies\AuditLogPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\CompanyScopedPolicy;
use App\Policies\EngagementPolicy;
use App\Policies\EngagementScopedPolicy;
use App\Policies\EvidencePolicy;
use App\Policies\FindingPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Policies\WorkingPaperPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class => CompanyPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserPolicy::class,
        Engagement::class => EngagementPolicy::class,
        EvidenceFile::class => EvidencePolicy::class,
        WorkingPaper::class => WorkingPaperPolicy::class,
        Finding::class => FindingPolicy::class,
        AccountingPeriod::class => CompanyScopedPolicy::class,
        ChartOfAccount::class => CompanyScopedPolicy::class,
        DocumentRequest::class => CompanyScopedPolicy::class,
        FinancialStatement::class => CompanyScopedPolicy::class,
        FiscalYear::class => CompanyScopedPolicy::class,
        ImportBatch::class => CompanyScopedPolicy::class,
        JournalEntry::class => CompanyScopedPolicy::class,
        Quarter::class => CompanyScopedPolicy::class,
        Reconciliation::class => CompanyScopedPolicy::class,
        Report::class => CompanyScopedPolicy::class,
        StatementTemplate::class => CompanyScopedPolicy::class,
        TrialBalance::class => CompanyScopedPolicy::class,
        AuditPlan::class => CompanyScopedPolicy::class,
        AuditProgram::class => EngagementScopedPolicy::class,
        AuditProgramStep::class => EngagementScopedPolicy::class,
        ControlRisk::class => EngagementScopedPolicy::class,
        InternalControl::class => EngagementScopedPolicy::class,
        ReviewNote::class => EngagementScopedPolicy::class,
        RiskAssessment::class => EngagementScopedPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super admin bypasses all Gates and Policies
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->hasRole('super_admin')) {
                return true;
            }

            return null;
        });

        // Register all permission-based gates dynamically
        // This allows: Gate::allows('journal.post') from controllers
        Gate::after(function (User $user, string $ability): ?bool {
            if ($user->hasPermission($ability)) {
                return true;
            }

            return null;
        });
    }
}
