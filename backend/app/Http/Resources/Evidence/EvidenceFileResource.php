<?php

declare(strict_types=1);

namespace App\Http\Resources\Evidence;

use App\Models\EvidenceFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EvidenceFile */
final class EvidenceFileResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'engagement_id' => $this->engagement_id,
            'working_paper_id' => $this->working_paper_id,
            'file_name' => $this->original_name,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'file_size_bytes' => $this->file_size_bytes,
            'checksum' => $this->checksum,
            'status' => $this->status->value,
            'description' => $this->description,
            'version' => $this->version,
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'rejected_at' => $this->rejected_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'uploaded_by' => $this->uploaded_by,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
