<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\AuditProgram;
use App\Models\AuditProgramStep;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AuditProgram */
final class AuditProgramResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var AuditProgram $auditProgram */
        $auditProgram = $this->resource;

        return [
            'id' => $this->id,
            'engagement_id' => $this->engagement_id,
            'name' => $this->name,
            'objectives' => $this->objectives,
            'status' => $this->status,
            'steps' => $this->when($auditProgram->relationLoaded('steps'), static function () use ($auditProgram): array {
                /** @var Collection<int, AuditProgramStep> $steps */
                $steps = $auditProgram->getRelation('steps');

                $result = [];
                foreach ($steps as $step) {
                    $result[] = [
                        'id' => $step->id,
                        'audit_program_id' => $step->audit_program_id,
                        'step_number' => $step->step_number,
                        'procedure' => $step->procedure,
                        'assigned_to' => $step->assigned_to,
                        'is_completed' => $step->is_completed,
                        'completed_at' => $step->completed_at?->toIso8601String(),
                    ];
                }

                return $result;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
