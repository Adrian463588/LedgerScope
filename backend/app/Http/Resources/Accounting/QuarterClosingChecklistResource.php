<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\QuarterClosingChecklist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QuarterClosingChecklist */
final class QuarterClosingChecklistResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quarter_id' => $this->quarter_id,
            'checklist_key' => $this->checklist_key,
            'checklist_name' => str_replace('_', ' ', ucfirst($this->checklist_key)),
            'is_required' => $this->is_required,
            'is_completed' => $this->is_completed,
            'notes' => $this->notes,
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }
}
