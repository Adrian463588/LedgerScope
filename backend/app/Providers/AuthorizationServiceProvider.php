<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Company;
use App\Models\User;
use App\Policies\CompanyPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthorizationServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class => CompanyPolicy::class,
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
        Gate::define('*', function (User $user, string $ability): bool {
            return $user->hasPermission($ability);
        });
    }
}
