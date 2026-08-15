<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\Finding;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Finding */
final class FindingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'engagement_id' => $this->engagement_id,
            'company_id' => $this->company_id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'severity' => $this->severity->value,
            'status' => $this->status->value,
            'root_cause' => $this->root_cause,
            'impact' => $this->impact,
            'recommendation' => $this->recommendation,
            'management_response' => $this->management_response,
            'action_plan' => $this->action_plan,
            'responsible_person' => $this->responsible_person,
            'due_date' => $this->due_date?->toDateString(),
            'assigned_to' => $this->assigned_to,
            'is_repeat' => $this->is_repeat,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
