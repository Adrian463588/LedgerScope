<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Engagement;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\Engagement;
use App\Models\User;
use App\Services\Audit\EngagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EngagementController extends Controller
{
    public function __construct(private readonly EngagementService $service) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(
            Engagement::where('company_id', $company->id)->orderByDesc('created_at')->get(),
        );
    }

    public function store(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'engagement_type' => ['required', 'string', 'in:accounting_service,financial_analysis,external_audit,internal_audit,review_engagement,compilation_engagement,tax_compliance,risk_advisory,internal_control_review'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'scope' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
        ]);

        // B-06: Use service layer — enforces Planning status + DB transaction
        $engagement = $this->service->create($validated, $company, $request->user());

        return ApiResponse::created($engagement, 'Engagement created.');
    }

    public function show(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement->company);

        return ApiResponse::success($engagement->load('members'));
    }

    public function update(Request $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:200'],
            'status' => ['sometimes', 'string'],
            'end_date' => ['sometimes', 'date'],
            'scope' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
        ]);

        $engagement->update($validated);

        return ApiResponse::success($engagement->fresh(), 'Engagement updated.');
    }

    public function addMember(Request $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        $engagement->members()->create($validated);

        return ApiResponse::success(null, 'Member added.');
    }

    public function removeMember(Engagement $engagement, User $user): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $engagement->members()->where('user_id', $user->id)->delete();

        return ApiResponse::success(null, 'Member removed.');
    }
}
