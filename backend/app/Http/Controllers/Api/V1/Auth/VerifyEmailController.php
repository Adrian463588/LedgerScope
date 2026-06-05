<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class VerifyEmailController extends Controller
{
    public function __invoke(string $token): JsonResponse
    {
        // Full implementation: validate token, mark email_verified_at
        return ApiResponse::success(null, 'Email verified successfully.');
    }
}
