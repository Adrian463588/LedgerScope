<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\Engagement;
use App\Models\Finding;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class DashboardController extends Controller
{
    /**
     * Get dashboard statistics (GET /api/v1/dashboard).
     *
     * Registered as a single-action invokable: Route::get('/dashboard', DashboardController::class)
     */
    public function __invoke(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Get user's companies
        $companies = $user->companies()->get();
        $companyIds = $companies->pluck('id')->toArray();

        // KPI: Active Engagements count
        $activeEngagements = Engagement::whereIn('company_id', $companyIds)
            ->whereNotIn('status', ['completed', 'archived', 'cancelled'])
            ->count();

        // KPI: Outstanding Document Requests (status not accepted/closed)
        // Uses real query — table may be empty if PBC module not yet seeded
        $outstandingRequests = 0; // DocumentRequest module (EPIC 5) — placeholder until routes added

        // KPI: Open Findings — real query on audit_findings table
        $openFindings = empty($companyIds) ? 0 : Finding::whereHas(
            'engagement',
            fn ($q) => $q->whereIn('company_id', $companyIds),
        )->whereNotIn('status', ['resolved', 'closed'])->count();

        // Quarterly Snapshot — use bcmath strings, never float
        $currentYear = now()->year;
        $currentMonth = now()->month;

        // Revenue (credit balances in revenue accounts) — returned as string by SUM
        $revenueRaw = (string) JournalEntry::whereIn('company_id', $companyIds)
            ->where('status', 'posted')
            ->whereYear('journal_date', $currentYear)
            ->whereMonth('journal_date', '<=', $currentMonth)
            ->join('journal_entry_lines', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.account_type', 'revenue')
            ->sum('credit');

        // Expenses (debit balances in expense accounts)
        $expensesRaw = (string) JournalEntry::whereIn('company_id', $companyIds)
            ->where('status', 'posted')
            ->whereYear('journal_date', $currentYear)
            ->whereMonth('journal_date', '<=', $currentMonth)
            ->join('journal_entry_lines', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('chart_of_accounts', 'journal_entry_lines.account_id', '=', 'chart_of_accounts.id')
            ->whereIn('chart_of_accounts.account_type', ['expense', 'cost_of_goods_sold'])
            ->sum('debit');

        // bcmath string arithmetic — NO float casts
        $revenue = bcadd($revenueRaw, '0', 2);
        $expenses = bcadd($expensesRaw, '0', 2);
        $netProfit = bcsub($revenue, $expenses, 2);

        $recentActivitiesRaw = JournalEntry::whereIn('company_id', $companyIds)
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
            ['label' => 'Trial Balance',   'hasData' => ! empty($companyIds)],
            ['label' => 'Working Papers',  'hasData' => Engagement::whereIn('company_id', $companyIds)->exists()],
            ['label' => 'Reports',         'hasData' => ! empty($companyIds)],
        ];

        return ApiResponse::success([
            'kpis' => [
                [
                    'label' => 'Total Active Engagements',
                    'value' => (string) $activeEngagements,
                    'change' => '+0',
                    'changeType' => 'up',
                    'isPrimary' => true,
                ],
                [
                    'label' => 'Outstanding Document Requests',
                    'value' => (string) $outstandingRequests,
                    'change' => '-0',
                    'changeType' => 'down',
                    'isPrimary' => false,
                ],
                [
                    'label' => 'Open Findings',
                    'value' => (string) $openFindings,
                    'change' => '+0',
                    'changeType' => $openFindings > 0 ? 'up' : 'down',
                    'isPrimary' => false,
                ],
            ],
            'quarterlySnapshot' => [
                [
                    'label' => 'Revenue',
                    'value' => 'IDR '.number_format((int) $revenue, 0, ',', '.'),
                    'change' => '+0%',
                    'changeType' => 'up',
                ],
                [
                    'label' => 'Expenses',
                    'value' => 'IDR '.number_format((int) $expenses, 0, ',', '.'),
                    'change' => '+0%',
                    'changeType' => 'down',
                ],
                [
                    'label' => 'Net Profit',
                    'value' => 'IDR '.number_format((int) $netProfit, 0, ',', '.'),
                    'change' => bccomp($netProfit, '0', 2) >= 0 ? '+0%' : '-0%',
                    'changeType' => bccomp($netProfit, '0', 2) >= 0 ? 'up' : 'down',
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
