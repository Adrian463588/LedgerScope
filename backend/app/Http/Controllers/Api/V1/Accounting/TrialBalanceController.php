<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\TrialBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TrialBalanceController extends Controller
{
    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(
            TrialBalance::where('company_id', $company->id)
                ->orderByDesc('created_at')->get(),
        );
    }

    public function generate(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
        ]);

        // Stub — full implementation in Phase 6 service
        return ApiResponse::success(null, 'Trial balance generation queued.');
    }
}
