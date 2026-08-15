<?php

use App\Console\Commands\MarkOverdueDocumentRequests;
use App\Jobs\WeeklyDigestJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// EPIC 10: Mark overdue document requests daily at midnight
Schedule::command(MarkOverdueDocumentRequests::class)->dailyAt('00:00');

// EPIC 10: Dispatch weekly notifications digest
Schedule::job(new WeeklyDigestJob)->weekly();

// Audit logs are append-only. Retention must be handled by an approved archive
// process outside the application; never delete evidence of a sensitive action
// from the scheduler.
