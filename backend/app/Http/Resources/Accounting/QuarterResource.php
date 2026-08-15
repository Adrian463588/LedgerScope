<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\Quarter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Quarter */
final class QuarterResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'fiscal_year_id' => $this->fiscal_year_id,
            'quarter_code' => $this->quarter_code,
            'quarter_name' => $this->quarter_code,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->status,
            'is_locked' => $this->is_locked,
            'locked_at' => $this->locked_at?->toIso8601String(),
            'unlock_reason' => $this->unlock_reason,
        ];
    }
}
