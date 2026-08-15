<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Engagement;
use App\Models\Finding;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class FindingPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Finding $finding): bool
    {
        if (! $user->hasPermission('finding.view')) {
            return false;
        }

        // Firm admin / super admin can view all findings for the company
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('firm_admin')) {
            return $user->companies()->where('companies.id', $finding->company_id)->exists();
        }

        // Client user belongs to the company
        if ($user->hasRole('client_user') || $user->hasRole('client_admin')) {
            return $user->companies()->where('companies.id', $finding->company_id)->exists();
        }

        // Engagement members can view
        return $finding->engagement->members()->where('user_id', $user->id)->exists()
            || $finding->engagement->lead_auditor_id === $user->id
            || $finding->engagement->manager_id === $user->id
            || $finding->engagement->partner_id === $user->id;
    }

    public function create(User $user, Engagement $engagement): bool
    {
        if (! $user->hasPermission('finding.create')) {
            return false;
        }

        // Client users cannot create findings
        if ($user->hasRole('client_user') || $user->hasRole('client_admin')) {
            return false;
        }

        // Must be engagement member
        return $engagement->members()->where('user_id', $user->id)->exists()
            || $engagement->lead_auditor_id === $user->id
            || $engagement->manager_id === $user->id
            || $engagement->partner_id === $user->id;
    }

    public function update(User $user, Finding $finding): bool
    {
        if (! $user->hasPermission('finding.update')) {
            return false;
        }

        // Client users cannot update findings (except management response)
        if ($user->hasRole('client_user') || $user->hasRole('client_admin')) {
            return false;
        }

        // Must be engagement member
        return $finding->engagement->members()->where('user_id', $user->id)->exists()
            || $finding->engagement->lead_auditor_id === $user->id
            || $finding->engagement->manager_id === $user->id
            || $finding->engagement->partner_id === $user->id;
    }

    public function approve(User $user, Finding $finding): bool
    {
        if (! $user->hasPermission('finding.approve')) {
            return false;
        }

        // Must be manager/partner on engagement, or firm admin
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('firm_admin')) {
            return $user->companies()->where('companies.id', $finding->company_id)->exists();
        }

        return $finding->engagement->manager_id === $user->id
            || $finding->engagement->partner_id === $user->id;
    }

    public function close(User $user, Finding $finding): bool
    {
        if (! $user->hasPermission('finding.close')) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('firm_admin')) {
            return $user->companies()->where('companies.id', $finding->company_id)->exists();
        }

        // Must be lead, manager, partner
        return $finding->engagement->lead_auditor_id === $user->id
            || $finding->engagement->manager_id === $user->id
            || $finding->engagement->partner_id === $user->id;
    }

    public function managementResponse(User $user, Finding $finding): bool
    {
        // Client users who belong to the company can reply to management response
        return $user->companies()->where('companies.id', $finding->company_id)->exists();
    }
}
