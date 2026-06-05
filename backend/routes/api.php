<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Accounting\AccountController;
use App\Http\Controllers\Api\V1\Accounting\FinancialStatementController;
use App\Http\Controllers\Api\V1\Accounting\FiscalYearController;
use App\Http\Controllers\Api\V1\Accounting\JournalController;
use App\Http\Controllers\Api\V1\Accounting\QuarterController;
use App\Http\Controllers\Api\V1\Accounting\ReconciliationController;
use App\Http\Controllers\Api\V1\Accounting\StatementTemplateController;
use App\Http\Controllers\Api\V1\Accounting\TrialBalanceController;
use App\Http\Controllers\Api\V1\Admin\InviteUserController;
use App\Http\Controllers\Api\V1\Auth\AcceptInvitationController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\MfaVerifyController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\Company\CompanyContactController;
use App\Http\Controllers\Api\V1\Company\CompanyController;
use App\Http\Controllers\Api\V1\Company\CompanyUserController;
use App\Http\Controllers\Api\V1\Engagement\EngagementController;
use App\Http\Controllers\Api\V1\Reporting\ReportController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — LedgerScope v1
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api/v1 by the RouteServiceProvider.
| Every authenticated route uses Sanctum session-based authentication.
|
*/

// ─── Health Check (public) ────────────────────────────────────────────────────
Route::get('/health', function (): JsonResponse {
    $checks = [
        'database' => 'ok',
        'redis' => 'ok',
        'queue' => 'ok',
        'storage' => 'ok',
    ];

    try {
        DB::connection()->getPdo();
    } catch (Exception) {
        $checks['database'] = 'error';
    }

    try {
        Redis::ping();
    } catch (Exception) {
        $checks['redis'] = 'error';
    }

    $status = in_array('error', $checks, true) ? 'degraded' : 'ok';

    return response()->json(array_merge(['status' => $status, 'timestamp' => now()->toIso8601String()], $checks));
});

// ─── V1 API ───────────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function (): void {

    // ─── Auth (public) ───────────────────────────────────────────────────────
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', LoginController::class)->name('auth.login');
        Route::post('/forgot-password', [ForgotPasswordController::class, '__invoke'])->name('auth.forgot-password');
        Route::post('/reset-password', [ResetPasswordController::class, '__invoke'])->name('auth.reset-password');
        Route::post('/verify-email/{token}', [VerifyEmailController::class, '__invoke'])->name('auth.verify-email');
    });

    // ─── Invitation Accept (public) ──────────────────────────────────────────
    Route::post('/invitations/{token}/accept', [AcceptInvitationController::class, '__invoke'])
        ->name('invitations.accept');

    // ─── Authenticated routes ─────────────────────────────────────────────────
    Route::middleware(['auth:sanctum'])->group(function (): void {

        // Auth
        Route::prefix('auth')->group(function (): void {
            Route::post('/logout', LogoutController::class)->name('auth.logout');
            Route::post('/mfa/verify', [MfaVerifyController::class, '__invoke'])->name('auth.mfa.verify');
            Route::get('/me', MeController::class)->name('auth.me');
        });

        // ─── Admin ───────────────────────────────────────────────────────────
        Route::prefix('admin')->middleware(['auth:sanctum'])->group(function (): void {
            Route::post('/users/invite', [InviteUserController::class, '__invoke'])
                ->name('admin.users.invite');
        });

        // ─── Companies ───────────────────────────────────────────────────────
        Route::prefix('companies')->group(function (): void {
            Route::get('/', [CompanyController::class, 'index'])->name('companies.index');
            Route::post('/', [CompanyController::class, 'store'])->name('companies.store');
            Route::get('/{company}', [CompanyController::class, 'show'])->name('companies.show');
            Route::put('/{company}', [CompanyController::class, 'update'])->name('companies.update');
            Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
            Route::post('/{company}/users', [CompanyUserController::class, 'store'])->name('companies.users.store');
            Route::delete('/{company}/users/{user}', [CompanyUserController::class, 'destroy'])->name('companies.users.destroy');
            Route::get('/{company}/contacts', [CompanyContactController::class, 'index'])->name('companies.contacts.index');
            Route::post('/{company}/contacts', [CompanyContactController::class, 'store'])->name('companies.contacts.store');

            // Fiscal Years (Phase 3)
            Route::get('/{company}/fiscal-years', [FiscalYearController::class, 'index'])->name('fiscal-years.index');
            Route::post('/{company}/fiscal-years', [FiscalYearController::class, 'store'])->name('fiscal-years.store');
            Route::get('/{company}/fiscal-years/{fiscalYear}', [FiscalYearController::class, 'show'])->name('fiscal-years.show');
            Route::get('/{company}/fiscal-years/{fiscalYear}/periods', [FiscalYearController::class, 'periods'])->name('fiscal-years.periods');
            Route::get('/{company}/fiscal-years/{fiscalYear}/quarters', [FiscalYearController::class, 'quarters'])->name('fiscal-years.quarters');

            // Quarters
            Route::post('/{company}/quarters/{quarter}/lock', [QuarterController::class, 'lock'])->name('quarters.lock');
            Route::post('/{company}/quarters/{quarter}/unlock', [QuarterController::class, 'unlock'])->name('quarters.unlock');
            Route::get('/{company}/quarters/{quarter}/checklist', [QuarterController::class, 'checklist'])->name('quarters.checklist');
            Route::patch('/{company}/quarters/{quarter}/checklist/{key}', [QuarterController::class, 'updateChecklist'])->name('quarters.checklist.update');

            // Chart of Accounts (Phase 4)
            Route::get('/{company}/accounts', [AccountController::class, 'index'])->name('accounts.index');
            Route::post('/{company}/accounts', [AccountController::class, 'store'])->name('accounts.store');
            Route::get('/{company}/accounts/{account}', [AccountController::class, 'show'])->name('accounts.show');
            Route::put('/{company}/accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
            Route::delete('/{company}/accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
            Route::post('/{company}/accounts/import', [AccountController::class, 'import'])->name('accounts.import');
            Route::get('/{company}/accounts/import/{batch}', [AccountController::class, 'importStatus'])->name('accounts.import.status');

            // Journals (Phase 5)
            Route::apiResource('/{company}/journals', JournalController::class)->only(['index', 'store', 'show', 'update'])->names(['index' => 'journals.index', 'store' => 'journals.store', 'show' => 'journals.show', 'update' => 'journals.update']);
            Route::post('/{company}/journals/{journal}/submit', [JournalController::class, 'submit'])->name('journals.submit');
            Route::post('/{company}/journals/{journal}/approve', [JournalController::class, 'approve'])->name('journals.approve');
            Route::post('/{company}/journals/{journal}/post', [JournalController::class, 'post'])->name('journals.post');
            Route::post('/{company}/journals/{journal}/reverse', [JournalController::class, 'reverse'])->name('journals.reverse');
            Route::post('/{company}/journals/{journal}/reject', [JournalController::class, 'reject'])->name('journals.reject');
            Route::post('/{company}/journals/import', [JournalController::class, 'import'])->name('journals.import');

            // Trial Balance & Reconciliation (Phase 6)
            Route::post('/{company}/trial-balance/generate', [TrialBalanceController::class, 'generate'])->name('trial-balance.generate');
            Route::get('/{company}/trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance.index');
            Route::apiResource('/{company}/reconciliations', ReconciliationController::class)->only(['index', 'store'])->names(['index' => 'reconciliations.index', 'store' => 'reconciliations.store']);
            Route::post('/{company}/reconciliations/{reconciliation}/auto-match', [ReconciliationController::class, 'autoMatch'])->name('reconciliations.auto-match');
            Route::post('/{company}/reconciliations/{reconciliation}/match', [ReconciliationController::class, 'match'])->name('reconciliations.match');
            Route::post('/{company}/reconciliations/{reconciliation}/approve', [ReconciliationController::class, 'approve'])->name('reconciliations.approve');

            // Financial Statements (Phase 7)
            Route::get('/{company}/statement-templates', [StatementTemplateController::class, 'index'])->name('statement-templates.index');
            Route::post('/{company}/statement-templates', [StatementTemplateController::class, 'store'])->name('statement-templates.store');
            Route::post('/{company}/financial-statements/generate', [FinancialStatementController::class, 'generate'])->name('financial-statements.generate');
            Route::get('/{company}/financial-statements', [FinancialStatementController::class, 'index'])->name('financial-statements.index');
            Route::get('/{company}/financial-statements/{version}', [FinancialStatementController::class, 'show'])->name('financial-statements.show');
            Route::post('/{company}/financial-statements/{version}/approve', [FinancialStatementController::class, 'approve'])->name('financial-statements.approve');
            Route::post('/{company}/financial-statements/{version}/lock', [FinancialStatementController::class, 'lock'])->name('financial-statements.lock');

            // Engagements (Phase 8)
            Route::get('/{company}/engagements', [EngagementController::class, 'index'])->name('engagements.index');
            Route::post('/{company}/engagements', [EngagementController::class, 'store'])->name('engagements.store');

            // Reports (Phase 9)
            Route::post('/{company}/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
            Route::get('/{company}/reports', [ReportController::class, 'index'])->name('reports.index');
        });

        // ─── Engagements (standalone) ──────────────────────────────────────────
        Route::prefix('engagements')->group(function (): void {
            Route::get('/{engagement}', [EngagementController::class, 'show'])->name('engagements.show');
            Route::put('/{engagement}', [EngagementController::class, 'update'])->name('engagements.update');
            Route::post('/{engagement}/members', [EngagementController::class, 'addMember'])->name('engagements.members.store');
            Route::delete('/{engagement}/members/{user}', [EngagementController::class, 'removeMember'])->name('engagements.members.destroy');
        });
    });
});
