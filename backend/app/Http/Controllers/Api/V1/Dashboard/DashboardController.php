<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardRequest;
use App\Http\Responses\ApiResponse;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\DocumentRequest;
use App\Models\Engagement;
use App\Models\Finding;
use App\Models\JournalEntry;
use App\Models\Report;
use App\Models\TrialBalance;
use App\Models\User;
use App\Models\WorkingPaper;
use App\Support\Decimal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class DashboardController extends Controller
{
    /**
     * Get dashboard statistics (GET /api/v1/dashboard).
     *
     * Registered as a single-action invokable: Route::get('/dashboard', DashboardController::class)
     */
    public function __invoke(DashboardRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Get user's companies
        $companies = $user->companies()->get();
        $validated = $request->validated();
        $selectedCompanyId = $validated['company_id'] ?? null;
        $companyIds = $selectedCompanyId === null
            ? $companies->pluck('id')->all()
            : $companies->where('id', $selectedCompanyId)->pluck('id')->all();

        if ($selectedCompanyId !== null && $companyIds === []) {
            return ApiResponse::notFound('Company not found.');
        }

        $period = null;
        if (isset($validated['period_id'])) {
            $period = AccountingPeriod::query()
                ->whereKey($validated['period_id'])
                ->whereIn('company_id', $companyIds)
                ->first();

            if ($period === null) {
                return ApiResponse::notFound('Accounting period not found.');
            }
        }

        // KPI: Active Engagements count
        $activeEngagements = Engagement::whereIn('company_id', $companyIds)
            ->whereNotIn('status', ['completed', 'archived', 'cancelled'])
            ->count();

        // KPI: Outstanding Document Requests (status not accepted/closed)
        $outstandingRequests = empty($companyIds)
            ? 0
            : DocumentRequest::whereHas('engagement', fn ($q) => $q->whereIn('company_id', $companyIds))
                ->whereNotIn('status', ['accepted', 'closed'])
                ->count();

        // KPI: Open Findings — real query on audit_findings table
        $openFindings = empty($companyIds) ? 0 : Finding::whereHas(
            'engagement',
            fn ($q) => $q->whereIn('company_id', $companyIds),
        )->whereNotIn('status', ['resolved', 'closed'])->count();

        // Quarterly Snapshot — use bcmath strings, never float
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $journalScope = static function ($query) use ($companyIds, $period, $currentYear, $currentMonth): void {
            $query->whereIn('journal_entries.company_id', $companyIds)
                ->where('journal_entries.status', 'posted');

            if ($period !== null) {
                $query->whereBetween('journal_entries.journal_date', [$period->start_date, $period->end_date]);

                return;
            }

            $query->whereYear('journal_entries.journal_date', $currentYear)
                ->whereMonth('journal_entries.journal_date', '<=', $currentMonth);
        };

        // Revenue (credit balances in revenue accounts) — returned as string by SUM
        $revenueRaw = (string) JournalEntry::query()
            ->tap($journalScope)
            ->join('journal_entry_lines', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.account_type', 'revenue')
            ->sum('journal_entry_lines.credit');

        // Expenses (debit balances in expense accounts)
        $expensesRaw = (string) JournalEntry::query()
            ->tap($journalScope)
            ->join('journal_entry_lines', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->whereIn('chart_of_accounts.account_type', ['expense', 'cost_of_goods_sold'])
            ->sum('journal_entry_lines.debit');

        // bcmath string arithmetic — NO float casts
        $revenue = Decimal::normalize($revenueRaw);
        $expenses = Decimal::normalize($expensesRaw);
        $netProfit = Decimal::subtract($revenue, $expenses);

        $recentActivitiesRaw = JournalEntry::query()
            ->whereIn('company_id', $companyIds)
            ->when($period !== null, fn ($query) => $query->whereBetween('journal_date', [$period->start_date, $period->end_date]))
            ->with(['createdBy'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $recentActivities = [];
        foreach ($recentActivitiesRaw as $entry) {
            $recentActivities[] = [
                'id' => $entry->id,
                'action' => "Journal Entry #{$entry->journal_number} {$entry->status->value}",
                'status' => $entry->status->value,
                'time' => $entry->updated_at->diffForHumans(),
                'user' => $entry->createdBy?->name ?? 'Unknown',
            ];
        }

        // Quick Access
        $quickAccess = [
            ['label' => 'Journal Entries', 'hasData' => JournalEntry::whereIn('company_id', $companyIds)->exists()],
            ['label' => 'Trial Balance',   'hasData' => TrialBalance::whereIn('company_id', $companyIds)->exists()],
            ['label' => 'Working Papers',  'hasData' => WorkingPaper::whereHas('engagement', fn ($q) => $q->whereIn('company_id', $companyIds))->exists()],
            ['label' => 'Reports',         'hasData' => Report::whereIn('company_id', $companyIds)->exists()],
        ];

        return ApiResponse::success([
            'kpis' => [
                [
                    'label' => 'Total Active Engagements',
                    'value' => (string) $activeEngagements,
                    'change' => null,
                    'changeType' => 'up',
                    'isPrimary' => true,
                ],
                [
                    'label' => 'Outstanding Document Requests',
                    'value' => (string) $outstandingRequests,
                    'change' => null,
                    'changeType' => 'down',
                    'isPrimary' => false,
                ],
                [
                    'label' => 'Open Findings',
                    'value' => (string) $openFindings,
                    'change' => null,
                    'changeType' => $openFindings > 0 ? 'up' : 'down',
                    'isPrimary' => false,
                ],
            ],
            'quarterlySnapshot' => [
                [
                    'label' => 'Revenue',
                    'value' => 'IDR '.Decimal::format($revenue, 0),
                    'change' => null,
                    'changeType' => 'up',
                ],
                [
                    'label' => 'Expenses',
                    'value' => 'IDR '.Decimal::format($expenses, 0),
                    'change' => null,
                    'changeType' => 'down',
                ],
                [
                    'label' => 'Net Profit',
                    'value' => 'IDR '.Decimal::format($netProfit, 0),
                    'change' => null,
                    'changeType' => Decimal::compare($netProfit, '0') >= 0 ? 'up' : 'down',
                ],
            ],
            'recentActivities' => $recentActivities,
            'quickAccess' => $quickAccess,
            'companies' => $companies->map(fn (Company $company) => [
                'id' => $company->id,
                'name' => $company->name,
                'legal_name' => $company->legal_name,
            ]),
        ], 'Dashboard data retrieved successfully.');
    }
}
