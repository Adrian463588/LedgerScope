<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\AuditPlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AuditPlan */
final class AuditPlanResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'engagement_id' => $this->engagement_id,
            'company_id' => $this->company_id,
            'overall_materiality' => (string) $this->overall_materiality,
            'performance_materiality' => (string) $this->performance_materiality,
            'trivial_threshold' => (string) $this->trivial_threshold,
            'audit_strategy' => $this->audit_strategy,
            'planning_checklist' => $this->planning_checklist ?? [],
        ];
    }
}
