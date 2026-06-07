<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoginFailed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class LoginController extends Controller
{
    /**
     * Handle user login (POST /api/v1/auth/login).
     *
     * RTK — Red:  LoginTest::it_rejects_invalid_credentials()
     * RTK — Green: RateLimit → Validate → authenticate → session → dispatch event → respond
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        // Enforce rate limiting before attempting authentication
        $request->ensureIsNotRateLimited();

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->incrementLoginAttempts();

            event(new UserLoginFailed(
                userId: 0,
                action: 'failed_login',
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
                metadata: ['email' => $request->input('email')],
            ));

            return ApiResponse::unauthorized('The provided credentials are incorrect.');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();

            return ApiResponse::unauthorized('Your account has been deactivated. Please contact your administrator.');
        }

        if ($user->mfa_enabled) {
            $userId = $user->id;
            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->put('mfa:user_id', $userId);
            }

            return ApiResponse::success([
                'mfa_required' => true,
                'email' => $user->email,
            ], 'MFA verification required.');
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        $request->clearLoginAttempts();

        $user->update(['last_login_at' => now()]);

        event(new UserLoggedIn(
            userId: $user->id,
            action: 'login',
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        return ApiResponse::success(
            new UserResource($user->load('roles.permissions')),
            'Login successful.',
        );
    }
}
