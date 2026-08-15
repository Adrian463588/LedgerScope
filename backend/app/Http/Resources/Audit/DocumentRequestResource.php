<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Http\Resources\Evidence\EvidenceFileResource;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DocumentRequest */
final class DocumentRequestResource extends JsonResource
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
            'status' => $this->status,
            'due_date' => $this->due_date?->toDateString(),
            'requested_by' => $this->requested_by,
            'assigned_to' => $this->assigned_to,
            'rejection_reason' => $this->rejection_reason,
            'evidence_file_id' => $this->evidence_file_id,
            'evidence_file' => $this->whenLoaded('evidenceFile', fn () => new EvidenceFileResource($this->evidenceFile)),
            'engagement' => $this->whenLoaded('engagement', fn () => [
                'id' => $this->engagement->id,
                'name' => $this->engagement->name,
                'status' => $this->engagement->status->value,
            ]),
            'is_overdue' => $this->isOverdue(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
