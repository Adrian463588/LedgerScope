<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Events\AuditActionRecorded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * AuditLogService — centralised helper for append-only audit entries.
 *
 * Usage:
 *   app(AuditLogService::class)->log($request, 'user.updated', $user, $before, $after);
 */
final class AuditLogService
{
    /**
     * Write a single audit-log entry.
     *
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>|null  $metadata
     *
     * The audit listener is queued after commit so a rolled-back mutation
     * cannot leave an orphan audit record.
     */
    public function log(
        Request $request,
        string $action,
        ?object $subject = null,
        ?array $before = null,
        ?array $after = null,
        ?int $companyId = null,
        ?array $metadata = null,
    ): void {
        $objectId = $subject instanceof Model ? (int) $subject->getKey() : null;
        $resolvedCompanyId = $companyId;

        if ($resolvedCompanyId === null && $subject instanceof Model) {
            $companyAttribute = $subject->getAttribute('company_id');
            $resolvedCompanyId = is_numeric($companyAttribute) ? (int) $companyAttribute : null;
        }

        event(new AuditActionRecorded(
            userId: $request->user()?->id ?? 0,
            action: $action,
            companyId: $resolvedCompanyId,
            objectType: $subject !== null ? class_basename($subject) : null,
            objectId: $objectId,
            before: $before,
            after: $after,
            ipAddress: $request->ip(),
            userAgent: substr((string) $request->userAgent(), 0, 500),
            metadata: $metadata,
        ));
    }
}
