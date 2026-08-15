<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Controller;
use App\Http\Resources\Audit\AuditProgramResource;
use App\Http\Resources\Audit\AuditProgramStepResource;
use App\Http\Responses\ApiResponse;
use App\Models\AuditProgram;
use App\Models\AuditProgramStep;
use App\Models\Engagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AuditProgramController — EPIC 12 PRD §6.19
 *
 * Manages audit programs and their steps (checklist-style).
 */
final class AuditProgramController extends Controller
{
    // ─── Programs ─────────────────────────────────────────────────────────────

    public function index(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(AuditProgramResource::collection(
            AuditProgram::where('engagement_id', $engagement->id)
                ->with('steps')
                ->orderByDesc('created_at')
                ->get(),
        ));
    }

    public function store(Request $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'objectives' => ['nullable', 'string'],
        ]);

        $program = DB::transaction(function () use ($validated, $engagement): AuditProgram {
            /** @var AuditProgram $program */
            $program = AuditProgram::create(array_merge($validated, [
                'engagement_id' => $engagement->id,
                'status' => 'draft',
            ]));

            return $program;
        });

        return ApiResponse::created(new AuditProgramResource($program), 'Audit program created.');
    }

    public function show(Engagement $engagement, AuditProgram $auditProgram): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(new AuditProgramResource($auditProgram->load('steps')));
    }

    public function update(Request $request, Engagement $engagement, AuditProgram $auditProgram): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:200'],
            'objectives' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:draft,active,completed'],
        ]);

        DB::transaction(function () use ($auditProgram, $validated): void {
            $auditProgram->update($validated);
        });

        return ApiResponse::success(new AuditProgramResource($auditProgram->fresh()), 'Audit program updated.');
    }

    // ─── Steps ────────────────────────────────────────────────────────────────

    public function addStep(Request $request, Engagement $engagement, AuditProgram $auditProgram): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validate([
            'step_number' => ['required', 'string', 'max:10'],
            'procedure' => ['required', 'string'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $step = DB::transaction(function () use ($validated, $auditProgram): AuditProgramStep {
            /** @var AuditProgramStep $step */
            $step = AuditProgramStep::create(array_merge($validated, [
                'audit_program_id' => $auditProgram->id,
                'is_completed' => false,
            ]));

            return $step;
        });

        return ApiResponse::created(new AuditProgramStepResource($step), 'Audit program step added.');
    }

    public function completeStep(Request $request, Engagement $engagement, AuditProgram $auditProgram, AuditProgramStep $step): JsonResponse
    {
        $this->authorize('update', $engagement);

        DB::transaction(function () use ($step): void {
            $step->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        });

        return ApiResponse::success(new AuditProgramStepResource($step->fresh()), 'Step marked complete.');
    }
}
