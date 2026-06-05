<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * CompanyPolicy — Controls access to company resources.
 *
 * Super admin bypass is handled globally in AuthorizationServiceProvider::Gate::before().
 */
final class CompanyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('company.view');
    }

    public function view(User $user, Company $company): bool
    {
        if (! $user->hasPermission('company.view')) {
            return false;
        }

        // User must belong to the company (for non-admins)
        if ($user->hasRole('firm_admin') || $user->hasRole('super_admin')) {
            return true;
        }

        return $user->companies()->where('companies.id', $company->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('company.create');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->hasPermission('company.update')
            && ($user->hasRole('firm_admin') || $user->companies()->where('companies.id', $company->id)->exists());
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->hasPermission('company.delete');
    }

    public function manageUsers(User $user, Company $company): bool
    {
        return $user->hasPermission('company.manage_users');
    }
}
