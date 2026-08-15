<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\FinancialStatement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin FinancialStatement */
final class FinancialStatementResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'accounting_period_id' => $this->accounting_period_id,
            'statement_type' => $this->statement_type,
            'status' => $this->status,
            'version' => $this->version,
            'is_locked' => $this->is_locked,
            'data' => $this->data ?? [],
            'generated_at' => $this->created_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'locked_at' => $this->locked_at?->toIso8601String(),
        ];
    }
}
