<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\ReconciliationItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ReconciliationItem */
final class ReconciliationItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reconciliation_id' => $this->reconciliation_id,
            'journal_line_id' => $this->journal_line_id,
            'item_type' => $this->item_type,
            'amount' => (string) $this->amount,
            'transaction_date' => $this->transaction_date?->toDateString(),
            'description' => $this->description,
            'reference' => $this->reference,
            'is_matched' => $this->is_matched,
        ];
    }
}
