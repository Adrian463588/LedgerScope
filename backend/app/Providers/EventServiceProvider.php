<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\Accounting\JournalApproved;
use App\Events\Accounting\JournalCreated;
use App\Events\Accounting\JournalPosted;
use App\Events\Accounting\JournalRejected;
use App\Events\Accounting\JournalReversed;
use App\Events\Accounting\JournalSubmitted;
use App\Events\Auth\UserActivated;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Events\Auth\UserLoginFailed;
use App\Events\Audit\FindingStatusChanged;
use App\Events\Audit\WorkingPaperSignedOff;
use App\Events\Audit\ReviewNoteResolved;
use App\Events\Evidence\EvidenceAccepted;
use App\Events\Evidence\EvidenceRejected;
use App\Listeners\AuditTrail\WriteAuditLog;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * Every auditable action MUST have a listener registered here. (AGENTS.md §2, Rule 9)
     *
     * @var array<class-string, list<class-string>>
     */
    protected $listen = [
        // ─── Auth events ──────────────────────────────────────────────────────
        UserLoggedIn::class => [WriteAuditLog::class],
        UserLoggedOut::class => [WriteAuditLog::class],
        UserLoginFailed::class => [WriteAuditLog::class],
        UserActivated::class => [WriteAuditLog::class],

        // ─── Journal events ───────────────────────────────────────────────────
        JournalCreated::class => [WriteAuditLog::class],
        JournalSubmitted::class => [WriteAuditLog::class],
        JournalApproved::class => [WriteAuditLog::class],
        JournalPosted::class => [WriteAuditLog::class],
        JournalRejected::class => [WriteAuditLog::class],
        JournalReversed::class => [WriteAuditLog::class],

        // ─── Finding events ───────────────────────────────────────────────────
        FindingStatusChanged::class => [WriteAuditLog::class],

        // ─── Evidence events ──────────────────────────────────────────────────
        EvidenceAccepted::class => [WriteAuditLog::class],
        EvidenceRejected::class => [WriteAuditLog::class],

        // ─── Working Paper & Review Note events ──────────────────────────────
        WorkingPaperSignedOff::class => [WriteAuditLog::class],
        ReviewNoteResolved::class => [WriteAuditLog::class],
    ];

    public function boot(): void {}

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
