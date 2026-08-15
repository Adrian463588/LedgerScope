<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Common\UserStatus;
use App\Events\AuditActionRecorded;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\UserInvitationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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

        /** @var array{user_id: int, email: string, name: string, invitation: UserInvitation} $result */
        $result = DB::transaction(function () use ($validated, $request): array {
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

            return [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'invitation' => $invitation,
            ];
        });

        Notification::route('mail', $result['email'])
            ->notify(new UserInvitationNotification($result['invitation'], $result['name']));

        event(new AuditActionRecorded(
            userId: $request->user()->id,
            action: 'admin.user.invited',
            objectType: 'User',
            objectId: $result['user_id'],
            after: ['email' => $result['email'], 'role_id' => $validated['role_id']],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        return ApiResponse::created([
            'user_id' => $result['user_id'],
            'email' => $result['email'],
            'expires_at' => $result['invitation']->expires_at,
            'email_queued' => true,
        ], 'Invitation created and email queued.');
    }
}
