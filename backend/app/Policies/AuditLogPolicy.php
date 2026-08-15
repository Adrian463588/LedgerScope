<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * AuditLogPolicy — guards access to the compliance audit trail.
 */
final class AuditLogPolicy
{
    use HandlesAuthorization;

    /** Only super_admin and firm_admin may see the audit trail. */
    public function viewAuditTrail(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('firm_admin');
    }
}
