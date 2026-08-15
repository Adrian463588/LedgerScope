<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\InternalControl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InternalControl */
final class InternalControlResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'engagement_id' => $this->engagement_id,
            'name' => $this->name,
            'control_type' => $this->control_type,
            'category' => $this->category,
            'description' => $this->description,
            'frequency' => $this->frequency,
            'owner' => $this->owner,
            'effectiveness' => $this->effectiveness,
            'testing_procedure' => $this->testing_procedure,
            'testing_notes' => $this->testing_notes,
            'tested_by' => $this->tested_by,
            'tested_at' => $this->tested_at?->toIso8601String(),
            'risks' => $this->whenLoaded('controlRisks', fn () => ControlRiskResource::collection($this->controlRisks)),
        ];
    }
}
