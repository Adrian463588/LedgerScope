<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Controllers\Controller;
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

        return ApiResponse::paginated($companies);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Company::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'legal_name' => ['nullable', 'string', 'max:200'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3'],
            'fiscal_year_start_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:180'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        $company = $this->service->create($validated);

        return ApiResponse::created($company, 'Company created successfully.');
    }

    public function show(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success($company);
    }

    public function update(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:200'],
            'legal_name' => ['nullable', 'string', 'max:200'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:100'],
            'currency' => ['nullable', 'string', 'size:3'],
            'fiscal_year_start_month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $company = $this->service->update($company, $validated);

        return ApiResponse::success($company, 'Company updated successfully.');
    }

    public function destroy(Company $company): JsonResponse
    {
        $this->authorize('delete', $company);

        $this->service->softDelete($company);

        return ApiResponse::success(null, 'Company deleted successfully.');
    }
}
