<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Services\Accounting\FiscalYearGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FiscalYearController extends Controller
{
    public function __construct(private readonly FiscalYearGeneratorService $generator) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(
            FiscalYear::where('company_id', $company->id)->orderByDesc('year')->get(),
        );
    }

    public function store(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $fiscalYear = $this->generator->generate($company, $validated['year']);

        return ApiResponse::created($fiscalYear->load(['quarters', 'accountingPeriods']), 'Fiscal year created.');
    }

    public function show(Company $company, FiscalYear $fiscalYear): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success($fiscalYear->load(['quarters', 'accountingPeriods']));
    }

    public function periods(Company $company, FiscalYear $fiscalYear): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success($fiscalYear->accountingPeriods()->orderBy('start_date')->get());
    }

    public function quarters(Company $company, FiscalYear $fiscalYear): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success($fiscalYear->quarters()->orderBy('quarter_code')->get());
    }
}
