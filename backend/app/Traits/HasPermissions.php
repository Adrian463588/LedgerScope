<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;

trait HasPermissions
{
    /** @var array<string, bool> */
    private array $permissionCache = [];

    /**
     * Check if the user has a specific permission.
     * Uses in-memory cache to avoid N+1 queries.
     */
    public function hasPermission(string $permission): bool
    {
        if (isset($this->permissionCache[$permission])) {
            return $this->permissionCache[$permission];
        }

        $hasPermission = $this->getAllPermissions()->contains($permission);
        $this->permissionCache[$permission] = $hasPermission;

        return $hasPermission;
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function hasAnyPermission(string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has all of the given permissions.
     */
    public function hasAllPermissions(string ...$permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the user has a specific role by name.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Get all permissions for the user across all roles.
     *
     * @return Collection<int, string>
     */
    public function getAllPermissions(): Collection
    {
        return $this->roles
            ->flatMap(fn (Role $role): Collection => $role->permissions->pluck('name'))
            ->unique()
            ->values();
    }

    /**
     * Clear the in-memory permission cache.
     * Call this after role/permission changes.
     */
    public function clearPermissionCache(): void
    {
        $this->permissionCache = [];
    }
}
