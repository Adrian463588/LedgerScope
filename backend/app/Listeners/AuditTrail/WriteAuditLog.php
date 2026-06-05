<?php

declare(strict_types=1);

namespace App\Listeners\AuditTrail;

use App\Events\BaseAuditableEvent;
use App\Models\AuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * WriteAuditLog Listener.
 *
 * Listens to ALL events that extend BaseAuditableEvent.
 * Runs asynchronously on the 'audit-trail' queue.
 * Must be registered in EventServiceProvider for each event.
 */
final class WriteAuditLog implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Queue name for audit trail jobs.
     */
    public string $queue = 'audit-trail';

    /**
     * Max retry attempts before giving up.
     */
    public int $tries = 3;

    /**
     * Handle the event.
     */
    public function handle(BaseAuditableEvent $event): void
    {
        AuditLog::create([
            // userId=0 means anonymous (e.g. failed login before user resolved) → store as null
            'user_id' => $event->userId > 0 ? $event->userId : null,
            'company_id' => $event->companyId,
            'action' => $event->action,
            'object_type' => $event->objectType,
            'object_id' => $event->objectId,
            'before_value' => $event->before,
            'after_value' => $event->after,
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'metadata' => $event->metadata,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(BaseAuditableEvent $event, \Throwable $exception): void
    {
        // Log the failure — audit log failure is critical
        Log::critical('WriteAuditLog listener failed', [
            'action' => $event->action,
            'user_id' => $event->userId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
