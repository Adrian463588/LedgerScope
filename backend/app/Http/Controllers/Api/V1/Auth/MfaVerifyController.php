<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MfaVerifyController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Full MFA implementation (Phase 10)
        return ApiResponse::success(null, 'MFA verified.');
    }
}
