<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnforceSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * Enforces an inactivity timeout (capped at 15 minutes per security compliance).
     * If user is inactive for too long, logs them out and returns 401 session_expired.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && $request->hasSession()) {
            $lastActivity = $request->session()->get('last_activity');

            // Security requirement: Cap inactivity timeout at 15 minutes
            $timeoutMinutes = min((int) config('session.lifetime', 120), 15);
            $timeoutSeconds = $timeoutMinutes * 60;

            if ($lastActivity !== null && (time() - $lastActivity) > $timeoutSeconds) {
                // Safely log out from the web/session guard
                if (method_exists(Auth::guard(), 'logout')) {
                    Auth::logout();
                } else {
                    Auth::guard('web')->logout();
                }

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return ApiResponse::error('Your session has expired due to inactivity.', 401, null, 'session_expired');
            }

            $request->session()->put('last_activity', time());
        }

        return $next($request);
    }
}
