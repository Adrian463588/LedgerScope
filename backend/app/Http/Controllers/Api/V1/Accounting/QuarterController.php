<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\Quarter;
use App\Models\QuarterClosingChecklist;
use App\Services\Accounting\PeriodLockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class QuarterController extends Controller
{
    public function __construct(private readonly PeriodLockService $lockService) {}

    public function lock(Request $request, Company $company, Quarter $quarter): JsonResponse
    {
        $this->authorize('update', $company);

        // Lock all periods in the quarter
        foreach ($quarter->periods as $period) {
            $this->lockService->lock($period, $request->user());
        }

        $quarter->forceFill(['is_locked' => true, 'locked_at' => now(), 'status' => 'locked'])->save();

        return ApiResponse::success($quarter->fresh(), 'Quarter locked.');
    }

    public function unlock(Request $request, Company $company, Quarter $quarter): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10'],
        ]);

        foreach ($quarter->periods as $period) {
            $this->lockService->unlock($period, $request->user(), $validated['reason']);
        }

        $quarter->forceFill([
            'is_locked' => false,
            'locked_at' => null,
            'unlock_reason' => $validated['reason'],
            'status' => 'open',
        ])->save();

        return ApiResponse::success($quarter->fresh(), 'Quarter unlocked.');
    }

    public function checklist(Company $company, Quarter $quarter): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success($quarter->checklists()->get());
    }

    public function updateChecklist(Request $request, Company $company, Quarter $quarter, string $key): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $item = QuarterClosingChecklist::where('quarter_id', $quarter->id)
            ->where('checklist_key', $key)
            ->firstOrFail();

        $item->update([
            'is_completed' => $validated['is_completed'],
            'completed_at' => $validated['is_completed'] ? now() : null,
            'completed_by' => $validated['is_completed'] ? $request->user()->id : null,
            'notes' => $validated['notes'] ?? $item->notes,
        ]);

        return ApiResponse::success($item, 'Checklist updated.');
    }
}
