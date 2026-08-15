<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\AuditProgramStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AuditProgramStep */
final class AuditProgramStepResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'audit_program_id' => $this->audit_program_id,
            'step_number' => $this->step_number,
            'procedure' => $this->procedure,
            'assigned_to' => $this->assigned_to,
            'is_completed' => $this->is_completed,
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
