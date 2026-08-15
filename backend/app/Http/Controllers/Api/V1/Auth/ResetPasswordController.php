<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Events\AuditActionRecorded;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

final class ResetPasswordController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password): void {
                $user->forceFill(['password' => $password])->save();
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return ApiResponse::domainError('Invalid reset token.', 422);
        }

        $user = User::where('email', $request->string('email')->toString())->first();
        if ($user !== null) {
            event(new AuditActionRecorded(
                userId: $user->id,
                action: 'auth.password.reset',
                objectType: 'User',
                objectId: $user->id,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            ));
        }

        return ApiResponse::success(null, 'Password reset successful.');
    }
}
