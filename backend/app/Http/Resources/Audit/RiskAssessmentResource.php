<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\RiskAssessment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin RiskAssessment */
final class RiskAssessmentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'engagement_id' => $this->engagement_id,
            'risk_area' => $this->risk_area,
            'risk_level' => $this->risk_level,
            'description' => $this->description,
            'mitigation' => $this->mitigation,
            'likelihood' => $this->likelihood,
            'impact' => $this->impact,
            'inherent_risk' => $this->inherent_risk,
            'control_risk' => $this->control_risk,
            'residual_risk' => $this->residual_risk,
            'fraud_risk' => $this->fraud_risk,
            'risk_category' => $this->risk_category,
            'is_significant' => $this->is_significant,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
