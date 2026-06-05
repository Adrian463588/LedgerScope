<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Common\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class InviteUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'name' => ['nullable', 'string', 'max:150'],
        ]);

        return DB::transaction(function () use ($validated, $request): JsonResponse {
            // Create placeholder user
            /** @var User $user */
            $user = User::create([
                'name' => $validated['name'] ?? 'Invited User',
                'email' => $validated['email'],
                'password' => bcrypt(Str::random(32)),
                'status' => UserStatus::Inactive->value,
            ]);

            /** @var UserInvitation $invitation */
            $invitation = UserInvitation::create([
                'user_id' => $user->id,
                'role_id' => $validated['role_id'],
                'token' => bin2hex(random_bytes(32)),   // 64-char hex
                'invited_by' => $request->user()->id,
                'status' => 'pending',
                'expires_at' => now()->addHours(72),
            ]);

            // Dispatch invitation email (Phase 1 §1.7 — mail job)
            // InvitationMail::dispatch($user, $invitation);

            return ApiResponse::created([
                'user_id' => $user->id,
                'email' => $user->email,
                'expires_at' => $invitation->expires_at,
            ], 'Invitation sent.');
        });
    }
}
