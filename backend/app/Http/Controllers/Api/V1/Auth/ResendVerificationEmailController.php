<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class ResendVerificationEmailController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:180'],
        ]);

        /** @var User|null $user */
        $user = User::where('email', $request->input('email'))->first();

        if (! $user) {
            // Standard security practice: do not reveal that the email doesn't exist
            return ApiResponse::success(null, 'If the email is registered, a new verification link has been sent.');
        }

        if ($user->email_verified_at) {
            return ApiResponse::badRequest('Email is already verified.');
        }

        // Generate a new token valid for 24 hours
        $user->update([
            'email_verification_token' => Str::random(40),
            'email_verification_expires_at' => now()->addHours(24),
        ]);

        // Note: In production, we would fire a notification here. For MVP, token is updated in DB.
        
        return ApiResponse::success(null, 'If the email is registered, a new verification link has been sent.');
    }
}
