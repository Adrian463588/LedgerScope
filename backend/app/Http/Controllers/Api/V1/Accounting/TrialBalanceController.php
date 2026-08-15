<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Accounting\TrialBalanceLineResource;
use App\Http\Resources\Accounting\TrialBalanceResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\TrialBalance;
use App\Services\Accounting\TrialBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class TrialBalanceController extends Controller
{
    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $trialBalance = TrialBalance::where('company_id', $company->id)
            ->with('lines.account')
            ->latest('created_at')
            ->first();

        return ApiResponse::success(TrialBalanceLineResource::collection(
            $trialBalance?->lines ?? collect(),
        ));
    }

    public function generate(Request $request, Company $company, TrialBalanceService $service): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
        ]);

        $period = $company->accountingPeriods()->findOrFail($validated['accounting_period_id']);
        $tb = $service->generate($company, $period, $request->user());

        return ApiResponse::success(new TrialBalanceResource($tb->load('lines.account')), 'Trial balance generated successfully.');
    }
}
