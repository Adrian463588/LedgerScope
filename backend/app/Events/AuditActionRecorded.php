<?php

declare(strict_types=1);

namespace App\Events;

/**
 * Generic auditable domain event for mutations without a dedicated lifecycle event.
 *
 * Dedicated accounting/auth/evidence events remain preferred where they already
 * exist; this event closes the audit gap for the remaining sensitive mutations.
 */
final class AuditActionRecorded extends BaseAuditableEvent {}
