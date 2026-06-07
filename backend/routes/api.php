<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Accounting\AccountController;
use App\Http\Controllers\Api\V1\Accounting\FinancialStatementController;
use App\Http\Controllers\Api\V1\Accounting\FiscalYearController;
use App\Http\Controllers\Api\V1\Accounting\JournalController;
use App\Http\Controllers\Api\V1\Accounting\JournalRedFlagController;
use App\Http\Controllers\Api\V1\Accounting\QuarterController;
use App\Http\Controllers\Api\V1\Accounting\ReconciliationController;
use App\Http\Controllers\Api\V1\Accounting\FinancialAnalysisController;
use App\Http\Controllers\Api\V1\Accounting\StatementTemplateController;
use App\Http\Controllers\Api\V1\Accounting\TrialBalanceController;
use App\Http\Controllers\Api\V1\Admin\AdminRoleController;
use App\Http\Controllers\Api\V1\Admin\AdminUserController;
use App\Http\Controllers\Api\V1\Admin\AuditTrailController;
use App\Http\Controllers\Api\V1\Admin\InviteUserController;
use App\Http\Controllers\Api\V1\Audit\AuditPlanController;
use App\Http\Controllers\Api\V1\Audit\AuditProgramController;
use App\Http\Controllers\Api\V1\Audit\ControlRiskController;
use App\Http\Controllers\Api\V1\Audit\DocumentRequestController;
use App\Http\Controllers\Api\V1\Audit\FindingController;
use App\Http\Controllers\Api\V1\Audit\InternalControlController;
use App\Http\Controllers\Api\V1\Audit\ReviewNoteController;
use App\Http\Controllers\Api\V1\Audit\RiskAssessmentController;
use App\Http\Controllers\Api\V1\Audit\WorkingPaperController;
use App\Http\Controllers\Api\V1\Auth\AcceptInvitationController;
use App\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\MfaSetupController;
use App\Http\Controllers\Api\V1\Auth\MfaVerifyController;
use App\Http\Controllers\Api\V1\Auth\ResendVerificationEmailController;
use App\Http\Controllers\Api\V1\Auth\ResetPasswordController;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\Client\ClientPortalController;
use App\Http\Controllers\Api\V1\Company\CompanyContactController;
use App\Http\Controllers\Api\V1\Company\CompanyController;
use App\Http\Controllers\Api\V1\Company\CompanyUserController;
use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\Engagement\EngagementController;
use App\Http\Controllers\Api\V1\Evidence\EvidenceController;
use App\Http\Controllers\Api\V1\Notification\NotificationController;
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
        Route::post('/verify-email/resend', [ResendVerificationEmailController::class, '__invoke'])->name('auth.verify-email.resend');
        Route::post('/mfa/verify', [MfaVerifyController::class, '__invoke'])->name('auth.mfa.verify');
    });

    // ─── Invitation Accept (public) ──────────────────────────────────────────
    Route::post('/invitations/{token}/accept', [AcceptInvitationController::class, '__invoke'])
        ->name('invitations.accept');

    // ─── Authenticated routes ─────────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'session.timeout'])->group(function (): void {

        // Dashboard
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('/notifications/preferences', [NotificationController::class, 'preferences'])->name('notifications.preferences');
        Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences'])->name('notifications.preferences.update');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

        // Auth
        Route::prefix('auth')->group(function (): void {
            Route::post('/logout', LogoutController::class)->name('auth.logout');
            Route::get('/mfa/setup', [MfaSetupController::class, 'index'])->name('auth.mfa.setup.index');
            Route::post('/mfa/setup', [MfaSetupController::class, 'store'])->name('auth.mfa.setup.store');
            Route::delete('/mfa/setup', [MfaSetupController::class, 'destroy'])->name('auth.mfa.setup.destroy');
            Route::get('/me', MeController::class)->name('auth.me');
        });

        // ─── Admin ───────────────────────────────────────────────────────────
        Route::prefix('admin')->middleware(['auth:sanctum'])->group(function (): void {
            // Invite (existing)
            Route::post('/users/invite', [InviteUserController::class, '__invoke'])
                ->name('admin.users.invite');

            // Epic 7B — Admin User Management
            Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
            Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
            Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
            Route::post('/users/{user}/suspend', [AdminUserController::class, 'suspend'])->name('admin.users.suspend');
            Route::post('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('admin.users.activate');

            // Epic 7B — Admin Role Mapping
            Route::get('/roles', [AdminRoleController::class, 'index'])->name('admin.roles.index');
            Route::post('/users/{user}/roles', [AdminRoleController::class, 'assign'])->name('admin.roles.assign');
            Route::delete('/users/{user}/roles/{role}', [AdminRoleController::class, 'revoke'])->name('admin.roles.revoke');

            // Epic 7C — Audit Trail
            Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('admin.audit-trail.index');
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
            Route::post('/{company}/journals/import', [JournalController::class, 'import'])->name('journals.import');
            Route::post('/{company}/journals/red-flag-scan', [JournalRedFlagController::class, 'scan'])->name('journals.red-flag-scan');
            Route::apiResource('/{company}/journals', JournalController::class)->only(['index', 'store', 'show', 'update'])->names(['index' => 'journals.index', 'store' => 'journals.store', 'show' => 'journals.show', 'update' => 'journals.update']);
            Route::post('/{company}/journals/{journal}/submit', [JournalController::class, 'submit'])->name('journals.submit');
            Route::post('/{company}/journals/{journal}/approve', [JournalController::class, 'approve'])->name('journals.approve');
            Route::post('/{company}/journals/{journal}/post', [JournalController::class, 'post'])->name('journals.post');
            Route::post('/{company}/journals/{journal}/reverse', [JournalController::class, 'reverse'])->name('journals.reverse');
            Route::post('/{company}/journals/{journal}/reject', [JournalController::class, 'reject'])->name('journals.reject');

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
            Route::get('/{company}/financial-statements/{version}/export', [FinancialStatementController::class, 'export'])->name('financial-statements.export');

            // Financial Analysis (EPIC 5)
            Route::get('/{company}/financial-analysis/ratios', [FinancialAnalysisController::class, 'ratios'])->name('financial-analysis.ratios');
            Route::get('/{company}/financial-analysis/trends', [FinancialAnalysisController::class, 'trends'])->name('financial-analysis.trends');
            Route::get('/{company}/financial-analysis/variance', [FinancialAnalysisController::class, 'variance'])->name('financial-analysis.variance');

            // Engagements (Phase 8)
            Route::get('/{company}/engagements', [EngagementController::class, 'index'])->name('engagements.index');
            Route::post('/{company}/engagements', [EngagementController::class, 'store'])->name('engagements.store');

            // Reports (Phase 9)
            Route::post('/{company}/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
            Route::get('/{company}/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/{company}/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
            Route::get('/{company}/reports/{report}/download', [ReportController::class, 'download'])->name('reports.download');
        });

        // ─── Engagements (standalone) ──────────────────────────────────────────
        Route::prefix('engagements')->middleware(['role:super_admin,firm_admin,partner,audit_manager,senior_auditor,junior_auditor'])->group(function (): void {
            Route::get('/{engagement}', [EngagementController::class, 'show'])->name('engagements.show');
            Route::put('/{engagement}', [EngagementController::class, 'update'])->name('engagements.update');
            Route::post('/{engagement}/members', [EngagementController::class, 'addMember'])->name('engagements.members.store');
            Route::delete('/{engagement}/members/{user}', [EngagementController::class, 'removeMember'])->name('engagements.members.destroy');

            // ─── Evidence (EPIC 4) ─────────────────────────────────────────────
            Route::get('/{engagement}/evidence', [EvidenceController::class, 'index'])->name('evidence.index');
            Route::post('/{engagement}/evidence', [EvidenceController::class, 'store'])->name('evidence.store');
            Route::get('/{engagement}/evidence/{evidence}', [EvidenceController::class, 'show'])->name('evidence.show');
            Route::post('/{engagement}/evidence/{evidence}/accept', [EvidenceController::class, 'accept'])->name('evidence.accept');
            Route::post('/{engagement}/evidence/{evidence}/reject', [EvidenceController::class, 'reject'])->name('evidence.reject');
            Route::get('/{engagement}/evidence/{evidence}/download', [EvidenceController::class, 'download'])->name('evidence.download');
            Route::delete('/{engagement}/evidence/{evidence}', [EvidenceController::class, 'destroy'])->name('evidence.destroy');

            // ─── Working Papers (EPIC 6) ───────────────────────────────────────
            Route::get('/{engagement}/working-papers', [WorkingPaperController::class, 'index'])->name('working-papers.index');
            Route::post('/{engagement}/working-papers', [WorkingPaperController::class, 'store'])->name('working-papers.store');
            Route::get('/{engagement}/working-papers/{workingPaper}', [WorkingPaperController::class, 'show'])->name('working-papers.show');
            Route::put('/{engagement}/working-papers/{workingPaper}', [WorkingPaperController::class, 'update'])->name('working-papers.update');
            Route::post('/{engagement}/working-papers/{workingPaper}/sign-off', [WorkingPaperController::class, 'signOff'])->name('working-papers.sign-off');
            Route::post('/{engagement}/working-papers/{workingPaper}/lock', [WorkingPaperController::class, 'lock'])->name('working-papers.lock');
            Route::post('/{engagement}/working-papers/{workingPaper}/unlock', [WorkingPaperController::class, 'unlock'])->name('working-papers.unlock');

            // ─── Findings (EPIC 7) ─────────────────────────────────────────────
            Route::get('/{engagement}/findings', [FindingController::class, 'index'])->name('findings.index');
            Route::post('/{engagement}/findings', [FindingController::class, 'store'])->name('findings.store');
            Route::get('/{engagement}/findings/{finding}', [FindingController::class, 'show'])->name('findings.show');
            Route::put('/{engagement}/findings/{finding}', [FindingController::class, 'update'])->name('findings.update');
            Route::post('/{engagement}/findings/{finding}/resolve', [FindingController::class, 'resolve'])->name('findings.resolve');
            Route::post('/{engagement}/findings/{finding}/reopen', [FindingController::class, 'reopen'])->name('findings.reopen');
            Route::post('/{engagement}/findings/{finding}/management-response', [FindingController::class, 'managementResponse'])->name('findings.management-response');

            // ─── Review Notes (EPIC 8) ─────────────────────────────────────────
            Route::get('/{engagement}/review-notes', [ReviewNoteController::class, 'index'])->name('review-notes.index');
            Route::post('/{engagement}/review-notes', [ReviewNoteController::class, 'store'])->name('review-notes.store');
            Route::post('/{engagement}/review-notes/{reviewNote}/resolve', [ReviewNoteController::class, 'resolve'])->name('review-notes.resolve');
            Route::post('/{engagement}/review-notes/{reviewNote}/reopen', [ReviewNoteController::class, 'reopen'])->name('review-notes.reopen');
            Route::post('/{engagement}/review-notes/{reviewNote}/reply', [ReviewNoteController::class, 'reply'])->name('review-notes.reply');
            Route::delete('/{engagement}/review-notes/{reviewNote}', [ReviewNoteController::class, 'destroy'])->name('review-notes.destroy');

            // ─── Audit Plan (EPIC 4) ───────────────────────────────────────────
            Route::get('/{engagement}/audit-plan', [AuditPlanController::class, 'show'])->name('audit-plan.show');
            Route::put('/{engagement}/audit-plan', [AuditPlanController::class, 'update'])->name('audit-plan.update');

            // ─── Document Requests / PBC Portal (EPIC 5) ────────────────────────
            Route::get('/{engagement}/document-requests', [DocumentRequestController::class, 'index'])->name('document-requests.index');
            Route::post('/{engagement}/document-requests', [DocumentRequestController::class, 'store'])->name('document-requests.store');
            Route::get('/{engagement}/document-requests/{documentRequest}', [DocumentRequestController::class, 'show'])->name('document-requests.show');
            Route::post('/{engagement}/document-requests/{documentRequest}/submit', [DocumentRequestController::class, 'submit'])->name('document-requests.submit');
            Route::post('/{engagement}/document-requests/{documentRequest}/accept', [DocumentRequestController::class, 'accept'])->name('document-requests.accept');
            Route::post('/{engagement}/document-requests/{documentRequest}/reject', [DocumentRequestController::class, 'reject'])->name('document-requests.reject');
            Route::delete('/{engagement}/document-requests/{documentRequest}', [DocumentRequestController::class, 'destroy'])->name('document-requests.destroy');

            // ─── Risk Assessments (EPIC 11) ─────────────────────────────────────
            Route::get('/{engagement}/risk-assessments', [RiskAssessmentController::class, 'index'])->name('risk-assessments.index');
            Route::post('/{engagement}/risk-assessments', [RiskAssessmentController::class, 'store'])->name('risk-assessments.store');
            Route::get('/{engagement}/risk-assessments/{riskAssessment}', [RiskAssessmentController::class, 'show'])->name('risk-assessments.show');
            Route::put('/{engagement}/risk-assessments/{riskAssessment}', [RiskAssessmentController::class, 'update'])->name('risk-assessments.update');
            Route::delete('/{engagement}/risk-assessments/{riskAssessment}', [RiskAssessmentController::class, 'destroy'])->name('risk-assessments.destroy');

            // ─── Audit Programs (EPIC 12) ────────────────────────────────────────
            Route::get('/{engagement}/audit-programs', [AuditProgramController::class, 'index'])->name('audit-programs.index');
            Route::post('/{engagement}/audit-programs', [AuditProgramController::class, 'store'])->name('audit-programs.store');
            Route::get('/{engagement}/audit-programs/{auditProgram}', [AuditProgramController::class, 'show'])->name('audit-programs.show');
            Route::put('/{engagement}/audit-programs/{auditProgram}', [AuditProgramController::class, 'update'])->name('audit-programs.update');
            Route::post('/{engagement}/audit-programs/{auditProgram}/steps', [AuditProgramController::class, 'addStep'])->name('audit-programs.steps.store');
            Route::post('/{engagement}/audit-programs/{auditProgram}/steps/{step}/complete', [AuditProgramController::class, 'completeStep'])->name('audit-programs.steps.complete');

            // ─── Internal Controls (EPIC 7A) ──────────────────────────────────────
            Route::get('/{engagement}/internal-controls', [InternalControlController::class, 'index'])->name('internal-controls.index');
            Route::post('/{engagement}/internal-controls', [InternalControlController::class, 'store'])->name('internal-controls.store');
            Route::get('/{engagement}/internal-controls/{internalControl}', [InternalControlController::class, 'show'])->name('internal-controls.show');
            Route::put('/{engagement}/internal-controls/{internalControl}', [InternalControlController::class, 'update'])->name('internal-controls.update');
            Route::delete('/{engagement}/internal-controls/{internalControl}', [InternalControlController::class, 'destroy'])->name('internal-controls.destroy');

            // ─── Control Risks (EPIC 7A) ──────────────────────────────────────────
            Route::get('/{engagement}/internal-controls/{internalControl}/risks', [ControlRiskController::class, 'index'])->name('control-risks.index');
            Route::post('/{engagement}/internal-controls/{internalControl}/risks', [ControlRiskController::class, 'store'])->name('control-risks.store');
            Route::put('/{engagement}/internal-controls/{internalControl}/risks/{controlRisk}', [ControlRiskController::class, 'update'])->name('control-risks.update');
            Route::delete('/{engagement}/internal-controls/{internalControl}/risks/{controlRisk}', [ControlRiskController::class, 'destroy'])->name('control-risks.destroy');
        });

        // ─── Client Portal (EPIC 14) ──────────────────────────────────────────────
        Route::prefix('client')->group(function (): void {
            Route::get('/document-requests', [ClientPortalController::class, 'listRequests'])->name('client.document-requests.index');
            Route::get('/document-requests/{documentRequest}', [ClientPortalController::class, 'showRequest'])->name('client.document-requests.show');
            Route::post('/document-requests/{documentRequest}/upload', [ClientPortalController::class, 'uploadAndSubmit'])->name('client.document-requests.upload');
        });
    });
});
