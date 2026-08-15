<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Engagement;

use App\Events\AuditActionRecorded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreEngagementRequest;
use App\Http\Requests\Audit\UpdateEngagementRequest;
use App\Http\Resources\Audit\EngagementResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\Engagement;
use App\Models\User;
use App\Services\Audit\EngagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class EngagementController extends Controller
{
    public function __construct(private readonly EngagementService $service) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(EngagementResource::collection(
            Engagement::where('company_id', $company->id)->orderByDesc('created_at')->get(),
        ));
    }

    public function store(StoreEngagementRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        // B-06: Use service layer — enforces Planning status + DB transaction
        $engagement = $this->service->create($request->validated(), $company, $request->user());

        event(new AuditActionRecorded(
            userId: $request->user()->id,
            action: 'engagement.create',
            companyId: $company->id,
            objectType: 'Engagement',
            objectId: $engagement->id,
            after: $engagement->toArray(),
        ));

        return ApiResponse::created(new EngagementResource($engagement), 'Engagement created.');
    }

    public function show(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(new EngagementResource($engagement->load('members')));
    }

    public function update(UpdateEngagementRequest $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        DB::transaction(function () use ($engagement, $request): void {
            $validated = $request->validated();
            $before = $engagement->toArray();
            $engagement->update($validated);

            event(new AuditActionRecorded(
                userId: $request->user()->id,
                action: 'engagement.update',
                companyId: $engagement->company_id,
                objectType: 'Engagement',
                objectId: $engagement->id,
                before: $before,
                after: $engagement->fresh()->toArray(),
            ));
        });

        return ApiResponse::success(new EngagementResource($engagement->fresh()), 'Engagement updated.');
    }

    public function addMember(Request $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('manageMembers', $engagement);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['nullable', 'string', 'max:50'],
        ]);

        if (! $engagement->company->users()->whereKey($validated['user_id'])->exists()) {
            throw new \DomainException('Engagement members must belong to the engagement company.');
        }

        DB::transaction(function () use ($engagement, $validated, $request): void {
            $member = $engagement->members()->create($validated);

            event(new AuditActionRecorded(
                userId: $request->user()->id,
                action: 'engagement.member_add',
                companyId: $engagement->company_id,
                objectType: 'EngagementMember',
                objectId: $member->id,
                after: $member->toArray(),
            ));
        });

        return ApiResponse::success(null, 'Member added.');
    }

    public function removeMember(Request $request, Engagement $engagement, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $engagement);

        DB::transaction(function () use ($engagement, $user, $request): void {
            $deleted = $engagement->members()->where('user_id', $user->id)->delete();

            if ($deleted > 0) {
                event(new AuditActionRecorded(
                    userId: $request->user()->id,
                    action: 'engagement.member_remove',
                    companyId: $engagement->company_id,
                    objectType: 'EngagementMember',
                    metadata: ['user_id' => $user->id],
                ));
            }
        });

        return ApiResponse::success(null, 'Member removed.');
    }
}
