<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\Common\UserStatus;
use App\Events\Auth\UserActivated;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AcceptInvitationController extends Controller
{
    public function __invoke(Request $request, string $token): JsonResponse
    {
        $invitation = UserInvitation::where('token', $token)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($invitation, $request): void {
            $user = User::findOrFail($invitation->user_id);
            $user->update([
                'name' => $request->input('name'),
                'password' => $request->input('password'),
                'status' => UserStatus::Active->value,
            ]);

            if ($invitation->role_id) {
                $user->roles()->syncWithoutDetaching($invitation->role_id);
            }

            $invitation->update(['status' => 'accepted', 'accepted_at' => now()]);

            event(new UserActivated(
                userId: $user->id,
                action: 'user_activated',
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            ));
        });

        return ApiResponse::success(null, 'Invitation accepted. You can now log in.');
    }
}
