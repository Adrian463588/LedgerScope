<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Auth Routes (to be implemented later if needed)
Route::redirect('/', '/dashboard');

// Dashboard
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
});

// Accounting Routes
Route::get('/journal-entries', function () {
    return Inertia::render('JournalEntries');
});

Route::get('/trial-balance', function () {
    return Inertia::render('TrialBalance');
});

Route::get('/financial-statements', function () {
    return Inertia::render('FinancialStatements');
});

// Audit Routes
Route::get('/audit-engagements', function () {
    return Inertia::render('AuditEngagements');
});

// Fallback for missing routes that were part of the mockup
Route::get('/{any}', function ($any) {
    return Inertia::render('Dashboard');
})->where('any', '.*');
