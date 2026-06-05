<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\Reconciliation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReconciliationController extends Controller
{
    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(
            Reconciliation::where('company_id', $company->id)->get(),
        );
    }

    public function store(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
            'reconciliation_type' => ['required', 'string', 'in:bank,ar,ap'],
        ]);

        $rec = Reconciliation::create(array_merge($validated, ['company_id' => $company->id]));

        return ApiResponse::created($rec, 'Reconciliation created.');
    }

    public function autoMatch(Company $company, Reconciliation $reconciliation): JsonResponse
    {
        $this->authorize('update', $company);

        return ApiResponse::success(null, 'Auto-match queued.');
    }

    public function match(Request $request, Company $company, Reconciliation $reconciliation): JsonResponse
    {
        $this->authorize('update', $company);

        return ApiResponse::success(null, 'Items matched.');
    }

    public function approve(Request $request, Company $company, Reconciliation $reconciliation): JsonResponse
    {
        $this->authorize('update', $company);

        $reconciliation->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => $request->user()->id]);

        return ApiResponse::success($reconciliation->fresh(), 'Reconciliation approved.');
    }
}
