<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Events\AuditActionRecorded;
use App\Events\Auth\UserLoggedIn;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\Auth\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class MfaVerifyController extends Controller
{
    private TotpService $totp;

    public function __construct(TotpService $totp)
    {
        $this->totp = $totp;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('mfa:user_id');
        if (! $userId) {
            return ApiResponse::unauthorized('Session expired or invalid MFA request.');
        }

        /** @var User|null $user */
        $user = User::find($userId);
        if (! $user || ! $user->isActive()) {
            return ApiResponse::unauthorized('User account is invalid or deactivated.');
        }

        // Verify the code
        $secret = $user->mfa_secret ?? '';
        if (empty($secret) || ! $this->totp->verify($secret, $request->input('code'))) {
            return ApiResponse::unauthorized('Invalid MFA code.');
        }

        // Success! Log the user in
        Auth::login($user);
        $request->session()->forget('mfa:user_id');

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $user->update(['last_login_at' => now()]);

        event(new UserLoggedIn(
            userId: $user->id,
            action: 'login',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        event(new AuditActionRecorded(
            userId: $user->id,
            action: 'auth.mfa.verified',
            objectType: 'User',
            objectId: $user->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        return ApiResponse::success(
            new UserResource($user->load('roles.permissions')),
            'MFA verified. Login successful.',
        );
    }
}
