<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Engagement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class EngagementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('engagement.view');
    }

    public function view(User $user, Engagement $engagement): bool
    {
        if (! $user->hasPermission('engagement.view')) {
            return false;
        }

        if ($user->hasRole('firm_admin') || $user->hasRole('super_admin')) {
            return $user->companies()->where('companies.id', $engagement->company_id)->exists();
        }

        if ($engagement->lead_auditor_id === $user->id 
            || $engagement->manager_id === $user->id 
            || $engagement->partner_id === $user->id) {
            return true;
        }

        return $engagement->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('engagement.create');
    }

    public function update(User $user, Engagement $engagement): bool
    {
        if (! $user->hasPermission('engagement.update')) {
            return false;
        }

        if ($user->hasRole('firm_admin') || $user->hasRole('super_admin')) {
            return $user->companies()->where('companies.id', $engagement->company_id)->exists();
        }

        if ($engagement->lead_auditor_id === $user->id 
            || $engagement->manager_id === $user->id 
            || $engagement->partner_id === $user->id) {
            return true;
        }

        return $engagement->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['partner', 'manager', 'lead_auditor'])
            ->exists();
    }

    public function delete(User $user, Engagement $engagement): bool
    {
        return $user->hasPermission('engagement.update') 
            && ($user->hasRole('firm_admin') || $user->hasRole('super_admin'))
            && $user->companies()->where('companies.id', $engagement->company_id)->exists();
    }

    public function manageMembers(User $user, Engagement $engagement): bool
    {
        if (! $user->hasPermission('engagement.manage_members')) {
            return false;
        }

        if ($user->hasRole('firm_admin') || $user->hasRole('super_admin')) {
            return $user->companies()->where('companies.id', $engagement->company_id)->exists();
        }

        return $engagement->lead_auditor_id === $user->id 
            || $engagement->manager_id === $user->id 
            || $engagement->partner_id === $user->id;
    }
}
