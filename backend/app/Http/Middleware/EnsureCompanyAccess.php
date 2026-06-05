<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Guards\CompanyAccessGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureCompanyAccess middleware.
 *
 * Validates that the authenticated user belongs to the company
 * identified by {company} or {companyId} route parameter.
 *
 * Usage: Route::middleware('company.access')
 */
final class EnsureCompanyAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user */
        $user = $request->user();

        // Resolve company ID from route — supports both {company} (model binding) and {companyId}
        $companyId = $request->route('company')?->id
            ?? $request->route('companyId')
            ?? null;

        if ($companyId === null) {
            return $next($request);
        }

        CompanyAccessGuard::authorize($user, (int) $companyId);

        return $next($request);
    }
}
