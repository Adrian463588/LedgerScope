<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AdminRoleController — Epic 7B PRD §Role Mapping
 */
final class AdminRoleController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Role::class);

        return ApiResponse::success(
            Role::with('permissions:id,name')->orderBy('name')->get(),
        );
    }

    /** Assign a role to a user */
    public function assign(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        DB::transaction(function () use ($user, $validated, $request): void {
            $user->roles()->syncWithoutDetaching([$validated['role_id']]);

            $this->auditLog->log(
                $request,
                'admin.role.assigned',
                $user,
                null,
                ['role_id' => $validated['role_id']],
            );
        });

        return ApiResponse::success(
            $user->load('roles:id,name,display_name'),
            'Role assigned.',
        );
    }

    /** Revoke a role from a user */
    public function revoke(Request $request, User $user, Role $role): JsonResponse
    {
        $this->authorize('update', $user);

        DB::transaction(function () use ($user, $role, $request): void {
            $user->roles()->detach($role->id);

            $this->auditLog->log(
                $request,
                'admin.role.revoked',
                $user,
                ['role_id' => $role->id],
                null,
            );
        });

        return ApiResponse::success(
            $user->load('roles:id,name,display_name'),
            'Role revoked.',
        );
    }
}
