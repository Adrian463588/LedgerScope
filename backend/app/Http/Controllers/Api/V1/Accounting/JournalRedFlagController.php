<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalRedFlagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JournalRedFlagController — Epic 7D PRD §Journal Red-Flag Testing
 *
 * POST /api/v1/companies/{company}/journals/red-flag-scan
 *   Optional filters: accounting_period_id, date_from, date_to
 */
final class JournalRedFlagController extends Controller
{
    public function __construct(private readonly JournalRedFlagService $scanner) {}

    public function scan(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $request->validate([
            'accounting_period_id' => ['nullable', 'integer', 'exists:accounting_periods,id'],
            'date_from'            => ['nullable', 'date'],
            'date_to'              => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $journals = JournalEntry::with(['lines.account'])
            ->where('company_id', $company->id)
            ->when($request->input('accounting_period_id'), fn ($q, $v) => $q->where('accounting_period_id', $v))
            ->when($request->input('date_from'), fn ($q, $v) => $q->whereDate('journal_date', '>=', $v))
            ->when($request->input('date_to'), fn ($q, $v) => $q->whereDate('journal_date', '<=', $v))
            ->get();

        $flags = $this->scanner->scan($journals);

        return ApiResponse::success([
            'total_journals_scanned' => $journals->count(),
            'total_flags'            => count($flags),
            'flags'                  => $flags,
        ], count($flags) === 0 ? 'No red flags detected.' : count($flags).' red flag(s) detected.');
    }
}
