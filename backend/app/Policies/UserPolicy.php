<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * UserPolicy — guards Admin User Management endpoints.
 */
final class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('firm_admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('firm_admin') || $user->id === $model->id;
    }

    /** Admins can update any user; users can update themselves. */
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('super_admin')
            || $user->hasRole('firm_admin')
            || $user->id === $model->id;
    }

    /** Only super_admin can delete users. */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('super_admin') && $user->id !== $model->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('firm_admin');
    }
}
