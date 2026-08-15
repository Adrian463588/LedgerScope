<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreFiscalYearRequest;
use App\Http\Resources\Accounting\AccountingPeriodResource;
use App\Http\Resources\Accounting\FiscalYearResource;
use App\Http\Resources\Accounting\QuarterResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Services\Accounting\FiscalYearGeneratorService;
use Illuminate\Http\JsonResponse;

final class FiscalYearController extends Controller
{
    public function __construct(private readonly FiscalYearGeneratorService $generator) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(FiscalYearResource::collection(
            FiscalYear::where('company_id', $company->id)->orderByDesc('year')->get(),
        ));
    }

    public function store(StoreFiscalYearRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validated();

        $fiscalYear = $this->generator->generate($company, $validated['year']);

        return ApiResponse::created(new FiscalYearResource($fiscalYear), 'Fiscal year created.');
    }

    public function show(Company $company, FiscalYear $fiscalYear): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('view', $fiscalYear);

        return ApiResponse::success(new FiscalYearResource($fiscalYear));
    }

    public function periods(Company $company, FiscalYear $fiscalYear): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('view', $fiscalYear);

        return ApiResponse::success(AccountingPeriodResource::collection(
            $fiscalYear->accountingPeriods()->orderBy('start_date')->get(),
        ));
    }

    public function quarters(Company $company, FiscalYear $fiscalYear): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('view', $fiscalYear);

        return ApiResponse::success(QuarterResource::collection(
            $fiscalYear->quarters()->orderBy('quarter_code')->get(),
        ));
    }
}
