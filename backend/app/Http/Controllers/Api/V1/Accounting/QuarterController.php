<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Events\AuditActionRecorded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\UnlockQuarterRequest;
use App\Http\Requests\Accounting\UpdateQuarterChecklistRequest;
use App\Http\Resources\Accounting\QuarterClosingChecklistResource;
use App\Http\Resources\Accounting\QuarterResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\Quarter;
use App\Models\QuarterClosingChecklist;
use App\Services\Accounting\PeriodLockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class QuarterController extends Controller
{
    public function __construct(private readonly PeriodLockService $lockService) {}

    public function lock(Request $request, Company $company, Quarter $quarter): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $quarter);

        // B-08: PRD §6.4 — all required checklist items must be completed before locking
        $incompleteItems = $quarter->checklists()
            ->where('is_required', true)
            ->where('is_completed', false)
            ->pluck('checklist_key');

        if ($incompleteItems->isNotEmpty()) {
            return ApiResponse::error(
                "Quarter cannot be locked: {$incompleteItems->count()} required checklist item(s) are incomplete.",
                422,
                ['incomplete_items' => $incompleteItems->values()->all()],
                'domain_error',
            );
        }

        DB::transaction(function () use ($quarter, $request): void {
            foreach ($quarter->periods as $period) {
                $this->lockService->lock($period, $request->user());
            }

            $quarter->forceFill([
                'is_locked' => true,
                'locked_at' => now(),
                'status' => 'locked',
            ])->save();
        });

        return ApiResponse::success(new QuarterResource($quarter->fresh()), 'Quarter locked.');
    }

    public function unlock(UnlockQuarterRequest $request, Company $company, Quarter $quarter): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $quarter);

        $validated = $request->validated();

        DB::transaction(function () use ($quarter, $request, $validated): void {
            foreach ($quarter->periods as $period) {
                $this->lockService->unlock($period, $request->user(), $validated['reason']);
            }

            $quarter->forceFill([
                'is_locked' => false,
                'locked_at' => null,
                'unlock_reason' => $validated['reason'],
                'status' => 'open',
            ])->save();
        });

        return ApiResponse::success(new QuarterResource($quarter->fresh()), 'Quarter unlocked.');
    }

    public function checklist(Company $company, Quarter $quarter): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('view', $quarter);

        return ApiResponse::success(QuarterClosingChecklistResource::collection($quarter->checklists()->get()));
    }

    public function updateChecklist(UpdateQuarterChecklistRequest $request, Company $company, Quarter $quarter, string $key): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $quarter);

        $validated = $request->validated();

        $item = DB::transaction(function () use ($quarter, $key, $validated, $request): QuarterClosingChecklist {
            $item = QuarterClosingChecklist::query()
                ->where('quarter_id', $quarter->id)
                ->where('checklist_key', $key)
                ->lockForUpdate()
                ->firstOrFail();
            $before = $item->toArray();

            $item->update([
                'is_completed' => $validated['is_completed'],
                'completed_at' => $validated['is_completed'] ? now() : null,
                'completed_by' => $validated['is_completed'] ? $request->user()->id : null,
                'notes' => $validated['notes'] ?? $item->notes,
            ]);

            event(new AuditActionRecorded(
                userId: $request->user()->id,
                action: 'quarter.checklist.update',
                companyId: $quarter->company_id,
                objectType: 'QuarterClosingChecklist',
                objectId: $item->id,
                before: $before,
                after: $item->fresh()->toArray(),
            ));

            return $item;
        });

        return ApiResponse::success(new QuarterClosingChecklistResource($item), 'Checklist updated.');
    }
}
