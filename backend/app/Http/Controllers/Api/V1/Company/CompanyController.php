<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Http\Resources\Company\CompanyResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Services\Company\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CompanyController extends Controller
{
    public function __construct(private readonly CompanyService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Company::class);

        $companies = $this->service->listForUser($request->user(), (int) $request->query('per_page', '20'));

        return ApiResponse::paginated(
            $companies,
            'Companies loaded.',
            static fn (Company $company): CompanyResource => new CompanyResource($company),
        );
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $this->authorize('create', Company::class);

        $company = $this->service->create($request->validated());

        return ApiResponse::created(new CompanyResource($company), 'Company created successfully.');
    }

    public function show(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(new CompanyResource($company));
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $company = $this->service->update($company, $request->validated());

        return ApiResponse::success(new CompanyResource($company), 'Company updated successfully.');
    }

    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $this->service->softDelete($company);

        return ApiResponse::success(null, 'Company deleted successfully.');
    }
}
