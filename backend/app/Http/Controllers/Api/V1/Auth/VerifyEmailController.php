<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Events\AuditActionRecorded;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class VerifyEmailController extends Controller
{
    public function __invoke(string $token): JsonResponse
    {
        /** @var User|null $user */
        $user = User::where('email_verification_token', $token)->first();

        if (! $user) {
            return ApiResponse::badRequest('Invalid or expired email verification token.');
        }

        if ($user->email_verification_expires_at && $user->email_verification_expires_at->isPast()) {
            return ApiResponse::badRequest('Email verification token has expired.');
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_expires_at' => null,
        ]);

        event(new AuditActionRecorded(
            userId: $user->id,
            action: 'auth.email_verified',
            objectType: 'User',
            objectId: $user->id,
        ));

        return ApiResponse::success(null, 'Email verified successfully. You can now log in.');
    }
}
