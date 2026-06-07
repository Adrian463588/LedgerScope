<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditLog;
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
     */
    public function log(
        Request $request,
        string $action,
        object|null $subject = null,
        array|null $before = null,
        array|null $after = null,
        int|null $companyId = null,
        array|null $metadata = null,
    ): AuditLog {
        /** @var AuditLog $log */
        $log = AuditLog::create([
            'user_id'     => $request->user()?->id,
            'company_id'  => $companyId,
            'action'      => $action,
            'object_type' => $subject !== null ? class_basename($subject) : null,
            'object_id'   => $subject !== null && property_exists($subject, 'id') ? $subject->id : null,
            'before_value' => $before,
            'after_value'  => $after,
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 500),
            'metadata'     => $metadata,
        ]);

        return $log;
    }
}
