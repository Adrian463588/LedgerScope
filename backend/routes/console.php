<?php

use App\Console\Commands\MarkOverdueDocumentRequests;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// EPIC 10: Mark overdue document requests daily at midnight
Schedule::command(MarkOverdueDocumentRequests::class)->dailyAt('00:00');

// EPIC 10: Dispatch weekly notifications digest
Schedule::job(new \App\Jobs\WeeklyDigestJob)->weekly();

// EPIC 14: Purge audit logs older than 7 years (retention policy)
Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('audit_logs')
        ->where('created_at', '<', now()->subYears(7))
        ->delete();
})->daily();
