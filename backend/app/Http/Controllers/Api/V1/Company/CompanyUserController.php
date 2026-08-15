<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Controllers\Controller;
use App\Http\Resources\Company\CompanyUserResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\User;
use App\Services\Company\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CompanyUserController extends Controller
{
    public function __construct(private readonly CompanyService $service) {}

    public function store(Request $request, Company $company): JsonResponse
    {
        $this->authorize('manageUsers', $company);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $user = User::findOrFail((int) $validated['user_id']);
        $companyUser = $this->service->assignUser($company, $user, $validated);

        return ApiResponse::created(new CompanyUserResource($companyUser), 'User assigned to company.');
    }

    public function destroy(Company $company, User $user): JsonResponse
    {
        $this->authorize('manageUsers', $company);

        $this->service->removeUser($company, $user);

        return ApiResponse::success(null, 'User removed from company.');
    }
}
