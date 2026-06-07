<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkingPaper;
use App\Models\Engagement;
use Illuminate\Auth\Access\HandlesAuthorization;

final class WorkingPaperPolicy
{
    use HandlesAuthorization;

    public function view(User $user, WorkingPaper $workingPaper): bool
    {
        if (! $user->hasPermission('working_paper.view')) {
            return false;
        }

        // Firm admin / super admin of the company can view
        if ($user->hasRole('firm_admin') || $user->hasRole('super_admin')) {
            return $user->companies()->where('companies.id', $workingPaper->engagement->company_id)->exists();
        }

        // Engagement lead, manager, partner can view
        if ($workingPaper->engagement->lead_auditor_id === $user->id 
            || $workingPaper->engagement->manager_id === $user->id 
            || $workingPaper->engagement->partner_id === $user->id) {
            return true;
        }

        // Engagement members can view
        return $workingPaper->engagement->members()->where('user_id', $user->id)->exists();
    }

    public function create(User $user, Engagement $engagement): bool
    {
        if (! $user->hasPermission('working_paper.create')) {
            return false;
        }

        // Must be engagement member
        return $engagement->members()->where('user_id', $user->id)->exists()
            || $engagement->lead_auditor_id === $user->id
            || $engagement->manager_id === $user->id
            || $engagement->partner_id === $user->id;
    }

    public function update(User $user, WorkingPaper $workingPaper): bool
    {
        if (! $user->hasPermission('working_paper.update')) {
            return false;
        }

        // Locked papers cannot be updated
        if ($workingPaper->is_locked) {
            return false;
        }

        // Must be engagement member or lead auditor/manager/partner
        return $workingPaper->engagement->members()->where('user_id', $user->id)->exists()
            || $workingPaper->engagement->lead_auditor_id === $user->id
            || $workingPaper->engagement->manager_id === $user->id
            || $workingPaper->engagement->partner_id === $user->id;
    }

    public function signoff(User $user, WorkingPaper $workingPaper): bool
    {
        if (! $user->hasPermission('working_paper.signoff')) {
            return false;
        }

        if ($user->hasRole('firm_admin') || $user->hasRole('super_admin')) {
            return true;
        }

        // Must be lead auditor, manager, or partner for this engagement
        return $workingPaper->engagement->lead_auditor_id === $user->id 
            || $workingPaper->engagement->manager_id === $user->id 
            || $workingPaper->engagement->partner_id === $user->id;
    }

    public function lock(User $user, WorkingPaper $workingPaper): bool
    {
        return $this->signoff($user, $workingPaper);
    }

    public function unlock(User $user, WorkingPaper $workingPaper): bool
    {
        return $this->signoff($user, $workingPaper);
    }
}
