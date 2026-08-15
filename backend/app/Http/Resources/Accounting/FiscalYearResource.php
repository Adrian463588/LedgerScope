<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FiscalYear */
final class FiscalYearResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'year' => $this->year,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'status' => $this->is_locked ? 'locked' : 'open',
            'is_locked' => $this->is_locked,
        ];
    }
}
