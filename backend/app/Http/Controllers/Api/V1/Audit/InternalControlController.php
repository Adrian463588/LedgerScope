<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreInternalControlRequest;
use App\Http\Requests\Audit\UpdateInternalControlRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Engagement;
use App\Models\InternalControl;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * InternalControlController — Epic 7A PRD §Internal Controls
 */
final class InternalControlController extends Controller
{
    public function index(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        $controls = InternalControl::where('engagement_id', $engagement->id)
            ->with(['tester:id,name,email', 'controlRisks'])
            ->orderByDesc('created_at')
            ->get();

        return ApiResponse::success($controls);
    }

    public function store(StoreInternalControlRequest $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validated();

        $control = DB::transaction(function () use ($validated, $engagement): InternalControl {
            /** @var InternalControl $control */
            $control = InternalControl::create(array_merge($validated, [
                'engagement_id' => $engagement->id,
            ]));

            return $control;
        });

        return ApiResponse::created($control->load('tester:id,name,email'), 'Internal control created.');
    }

    public function show(Engagement $engagement, InternalControl $internalControl): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success($internalControl->load(['tester:id,name,email', 'controlRisks']));
    }

    public function update(UpdateInternalControlRequest $request, Engagement $engagement, InternalControl $internalControl): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validated();

        $internalControl->update($validated);

        return ApiResponse::success($internalControl->fresh()->load('tester:id,name,email'), 'Internal control updated.');
    }

    public function destroy(Engagement $engagement, InternalControl $internalControl): JsonResponse
    {
        $this->authorize('update', $engagement);

        $internalControl->delete();

        return ApiResponse::noContent();
    }
}
