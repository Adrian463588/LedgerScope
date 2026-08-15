<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Engagement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Authorization for resources owned by an engagement rather than directly by
 * a company. Parent route authorization remains mandatory; this policy closes
 * direct-model and nested-child access paths as well.
 */
final class EngagementScopedPolicy
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'view' => 'engagement.view',
        'update' => 'engagement.update',
        'delete' => 'engagement.update',
    ];

    public function view(User $user, Model $resource): bool
    {
        return $this->allows($user, $resource, 'view');
    }

    public function update(User $user, Model $resource): bool
    {
        return $this->allows($user, $resource, 'update');
    }

    public function delete(User $user, Model $resource): bool
    {
        return $this->allows($user, $resource, 'delete');
    }

    private function allows(User $user, Model $resource, string $ability): bool
    {
        if (! $user->hasPermission(self::PERMISSIONS[$ability])) {
            return false;
        }

        $engagementId = $this->engagementId($resource);
        if ($engagementId === null) {
            return false;
        }

        $engagement = Engagement::query()->find($engagementId);
        if ($engagement === null) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('firm_admin')) {
            return $user->companies()->whereKey($engagement->company_id)->exists();
        }

        return $engagement->lead_auditor_id === $user->id
            || $engagement->manager_id === $user->id
            || $engagement->partner_id === $user->id
            || $engagement->members()->where('user_id', $user->id)->exists();
    }

    private function engagementId(Model $resource): ?int
    {
        $engagementId = $resource->getAttribute('engagement_id');
        if ($engagementId !== null) {
            return (int) $engagementId;
        }

        $workingPaper = $resource->getRelationValue('workingPaper');
        if ($workingPaper instanceof Model && $workingPaper->getAttribute('engagement_id') !== null) {
            return (int) $workingPaper->getAttribute('engagement_id');
        }

        $auditProgram = $resource->getRelationValue('auditProgram');
        if ($auditProgram instanceof Model && $auditProgram->getAttribute('engagement_id') !== null) {
            return (int) $auditProgram->getAttribute('engagement_id');
        }

        return null;
    }
}
