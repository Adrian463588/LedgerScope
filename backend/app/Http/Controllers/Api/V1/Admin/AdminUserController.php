<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Common\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AdminUserController — Epic 7B PRD §Admin User Management
 *
 * All write operations are audit-logged.
 */
final class AdminUserController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles:id,name,display_name')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('search'), fn ($q, $s) => $q->where(function ($q) use ($s): void {
                $q->where('name', 'ilike', "%{$s}%")
                  ->orWhere('email', 'ilike', "%{$s}%");
            }))
            ->orderBy('name')
            ->paginate(25);

        return ApiResponse::paginated($users, 'Users loaded.');
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return ApiResponse::success(
            $user->load('roles:id,name,display_name'),
        );
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name'   => ['sometimes', 'string', 'max:150'],
            'phone'  => ['nullable', 'string', 'max:30'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
        ]);

        $before = $user->only(['name', 'phone', 'status']);

        DB::transaction(function () use ($user, $validated, $before, $request): void {
            $user->update($validated);

            $this->auditLog->log(
                $request,
                'admin.user.updated',
                $user,
                $before,
                $user->fresh()->only(['name', 'phone', 'status']),
            );
        });

        return ApiResponse::success($user->fresh()->load('roles:id,name,display_name'), 'User updated.');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        DB::transaction(function () use ($user, $request): void {
            $this->auditLog->log($request, 'admin.user.deleted', $user, $user->toArray());
            $user->delete();
        });

        return ApiResponse::noContent();
    }

    public function suspend(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $before = ['status' => $user->status];
        $user->update(['status' => UserStatus::Suspended]);

        $this->auditLog->log($request, 'admin.user.suspended', $user, $before, ['status' => UserStatus::Suspended]);

        return ApiResponse::success(null, 'User suspended.');
    }

    public function activate(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $before = ['status' => $user->status];
        $user->update(['status' => UserStatus::Active]);

        $this->auditLog->log($request, 'admin.user.activated', $user, $before, ['status' => UserStatus::Active]);

        return ApiResponse::success(null, 'User activated.');
    }
}
