<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Events\AuditActionRecorded;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\Auth\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MfaSetupController extends Controller
{
    private TotpService $totp;

    public function __construct(TotpService $totp)
    {
        $this->totp = $totp;
    }

    /**
     * Get MFA setup details (GET /api/v1/auth/mfa/setup).
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->mfa_enabled) {
            return ApiResponse::badRequest('MFA is already enabled on your account.');
        }

        // Generate a secret if one isn't stored yet
        $secret = $user->mfa_secret ?: $this->totp->generateSecret();
        if ($user->mfa_secret !== $secret) {
            $user->update(['mfa_secret' => $secret]);
        }

        $qrCodeUrl = $this->totp->getQrCodeUrl($user->email, $secret);

        return ApiResponse::success([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ], 'MFA setup details generated.');
    }

    /**
     * Verify and enable MFA (POST /api/v1/auth/mfa/setup).
     */
    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->mfa_enabled) {
            return ApiResponse::badRequest('MFA is already enabled.');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = $user->mfa_secret;
        if (! $secret) {
            return ApiResponse::badRequest('MFA setup has not been initiated.');
        }

        if (! $this->totp->verify($secret, $request->input('code'))) {
            return ApiResponse::unauthorized('Invalid MFA code.');
        }

        $user->update(['mfa_enabled' => true]);

        event(new AuditActionRecorded(
            userId: $user->id,
            action: 'auth.mfa.enabled',
            objectType: 'User',
            objectId: $user->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        return ApiResponse::success(null, 'MFA enabled successfully.');
    }

    /**
     * Disable MFA (DELETE /api/v1/auth/mfa/setup).
     */
    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->mfa_enabled) {
            return ApiResponse::badRequest('MFA is not enabled.');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        // Require verifying a code to disable MFA for security
        $secret = $user->mfa_secret;
        if (! $secret || ! $this->totp->verify($secret, $request->input('code'))) {
            return ApiResponse::unauthorized('Invalid MFA code. Cannot disable MFA.');
        }

        $user->update([
            'mfa_enabled' => false,
            'mfa_secret' => null,
        ]);

        event(new AuditActionRecorded(
            userId: $user->id,
            action: 'auth.mfa.disabled',
            objectType: 'User',
            objectId: $user->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        return ApiResponse::success(null, 'MFA disabled successfully.');
    }
}
