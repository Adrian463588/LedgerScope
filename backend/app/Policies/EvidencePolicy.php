<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EvidenceFile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class EvidencePolicy
{
    use HandlesAuthorization;

    public function view(User $user, EvidenceFile $evidenceFile): bool
    {
        if (! $user->hasPermission('evidence.view')) {
            return false;
        }

        // Firm admin and super admin of the company can view
        if ($user->hasRole('firm_admin') || $user->hasRole('super_admin')) {
            return $user->companies()->where('companies.id', $evidenceFile->engagement->company_id)->exists();
        }

        // Uploaded by user
        if ($evidenceFile->uploaded_by === $user->id) {
            return true;
        }

        // Member of the engagement
        return $evidenceFile->engagement->members()->where('user_id', $user->id)->exists()
            || $evidenceFile->engagement->lead_auditor_id === $user->id
            || $evidenceFile->engagement->manager_id === $user->id
            || $evidenceFile->engagement->partner_id === $user->id;
    }

    public function upload(User $user, EvidenceFile $evidenceFile): bool
    {
        return $user->hasPermission('evidence.upload');
    }

    public function download(User $user, EvidenceFile $evidenceFile): bool
    {
        if (! $user->hasPermission('evidence.download')) {
            return false;
        }

        return $this->view($user, $evidenceFile);
    }

    public function review(User $user, EvidenceFile $evidenceFile): bool
    {
        if (! $user->hasPermission('evidence.review')) {
            return false;
        }

        // Client users cannot review evidence
        if ($user->hasRole('client_user') || $user->hasRole('client_admin')) {
            return false;
        }

        if ($user->hasRole('firm_admin') || $user->hasRole('super_admin')) {
            return $user->companies()->where('companies.id', $evidenceFile->engagement->company_id)->exists();
        }

        return $evidenceFile->engagement->members()->where('user_id', $user->id)->exists()
            || $evidenceFile->engagement->lead_auditor_id === $user->id
            || $evidenceFile->engagement->manager_id === $user->id
            || $evidenceFile->engagement->partner_id === $user->id;
    }

    public function delete(User $user, EvidenceFile $evidenceFile): bool
    {
        if (! $user->hasPermission('evidence.delete')) {
            return false;
        }

        // Can only delete if not yet accepted/final
        if ($evidenceFile->status->value === 'accepted') {
            return false;
        }

        if ($user->hasRole('firm_admin') || $user->hasRole('super_admin')) {
            return $user->companies()->where('companies.id', $evidenceFile->engagement->company_id)->exists();
        }

        // Uploaded by same user or is engagement lead/manager
        return $evidenceFile->uploaded_by === $user->id
            || $evidenceFile->engagement->lead_auditor_id === $user->id
            || $evidenceFile->engagement->manager_id === $user->id
            || $evidenceFile->engagement->partner_id === $user->id;
    }
}
