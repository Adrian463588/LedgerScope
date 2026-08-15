<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Http\Resources\Evidence\EvidenceFileResource;
use App\Models\WorkingPaper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WorkingPaper */
final class WorkingPaperResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'engagement_id' => $this->engagement_id,
            'title' => $this->title,
            'paper_ref' => $this->paper_ref,
            'status' => $this->status,
            'prepared_by' => $this->prepared_by,
            'reviewed_by' => $this->reviewed_by,
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'content' => $this->content,
            'sign_off_at' => $this->sign_off_at?->toIso8601String(),
            'is_locked' => $this->is_locked,
            'locked_at' => $this->locked_at?->toIso8601String(),
            'evidence_files' => $this->whenLoaded('evidenceFiles', fn () => EvidenceFileResource::collection($this->evidenceFiles)),
            'review_notes' => $this->whenLoaded('reviewNotes', fn () => ReviewNoteResource::collection($this->reviewNotes)),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
