<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Engagement;
use App\Models\EvidenceFile;
use App\Models\WorkingPaper;
use App\Models\Finding;
use App\Policies\AuditLogPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Policies\EngagementPolicy;
use App\Policies\EvidencePolicy;
use App\Policies\WorkingPaperPolicy;
use App\Policies\FindingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class      => CompanyPolicy::class,
        AuditLog::class     => AuditLogPolicy::class,
        Role::class         => RolePolicy::class,
        User::class         => UserPolicy::class,
        Engagement::class   => EngagementPolicy::class,
        EvidenceFile::class => EvidencePolicy::class,
        WorkingPaper::class => WorkingPaperPolicy::class,
        Finding::class      => FindingPolicy::class,
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
