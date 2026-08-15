<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\Reconciliation;
use App\Models\ReconciliationItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Reconciliation */
final class ReconciliationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var Reconciliation $reconciliation */
        $reconciliation = $this->resource;

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'account_id' => $this->account_id,
            'accounting_period_id' => $this->accounting_period_id,
            'reconciliation_type' => $this->reconciliation_type,
            'status' => $this->status,
            'book_balance' => (string) $this->book_balance,
            'bank_balance' => (string) $this->bank_balance,
            'difference' => (string) $this->difference,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'locked_at' => $this->locked_at?->toIso8601String(),
            'items' => $this->when($reconciliation->relationLoaded('items'), static function () use ($reconciliation): array {
                /** @var Collection<int, ReconciliationItem> $items */
                $items = $reconciliation->getRelation('items');

                $result = [];
                foreach ($items as $item) {
                    $result[] = [
                        'id' => $item->id,
                        'journal_line_id' => $item->journal_line_id,
                        'item_type' => $item->item_type,
                        'amount' => (string) $item->amount,
                        'transaction_date' => $item->transaction_date?->toDateString(),
                        'description' => $item->description,
                        'reference' => $item->reference,
                        'is_matched' => $item->is_matched,
                    ];
                }

                return $result;
            }),
        ];
    }
}
