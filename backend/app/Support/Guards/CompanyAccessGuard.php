<?php

declare(strict_types=1);

namespace App\Support\Guards;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;

/**
 * CompanyAccessGuard
 *
 * Verifies that the given User belongs to the requested Company.
 * Checks are memoized per request to avoid repeated DB queries.
 *
 * AGENTS.md §2.3 — "Every Service method that fetches company-scoped data must
 * abort_unless(CompanyAccessGuard::check(...))"
 */
final class CompanyAccessGuard
{
    /**
     * In-memory cache for current request (avoids DB N+1).
     *
     * @var array<string, bool>
     */
    private static array $cache = [];

    /**
     * Check if user has access to the given company.
     */
    public static function check(User $user, int $companyId): bool
    {
        // Super admin bypasses all company access checks
        if ($user->hasRole('super_admin') || $user->hasRole('firm_admin')) {
            return true;
        }

        $cacheKey = "company_access_{$user->id}_{$companyId}";

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $hasAccess = $user->companies()
            ->where('companies.id', $companyId)
            ->exists();

        self::$cache[$cacheKey] = $hasAccess;

        return $hasAccess;
    }

    /**
     * Assert user has access — throw 403 if not.
     *
     * @throws AuthorizationException
     */
    public static function authorize(User $user, int $companyId): void
    {
        if (! self::check($user, $companyId)) {
            throw new AuthorizationException('Access denied to this company.');
        }
    }

    /**
     * Clear in-memory cache between requests/tests.
     */
    public static function flushCache(): void
    {
        self::$cache = [];
    }
}
