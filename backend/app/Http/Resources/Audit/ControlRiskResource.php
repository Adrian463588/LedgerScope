<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\ControlRisk;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ControlRisk */
final class ControlRiskResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'internal_control_id' => $this->internal_control_id,
            'engagement_id' => $this->engagement_id,
            'risk_name' => $this->risk_name,
            'risk_description' => $this->risk_description,
            'likelihood' => $this->likelihood,
            'impact' => $this->impact,
            'residual_risk' => $this->residual_risk,
            'mitigating_factors' => $this->mitigating_factors,
        ];
    }
}
