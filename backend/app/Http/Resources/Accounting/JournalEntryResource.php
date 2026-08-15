<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin JournalEntry */
final class JournalEntryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var JournalEntry $journal */
        $journal = $this->resource;

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'accounting_period_id' => $this->accounting_period_id,
            'journal_number' => $this->journal_number,
            'reference' => $this->reference,
            'description' => $this->description,
            'journal_date' => $this->journal_date?->toDateString(),
            'date' => $this->journal_date?->toDateString(),
            'status' => $this->status->value,
            'source_type' => $this->source_type->value,
            'reversed_from_id' => $this->reversed_from_id,
            'created_by' => $this->created_by,
            'submitted_by' => $this->submitted_by,
            'approved_by' => $this->approved_by,
            'posted_by' => $this->posted_by,
            'posted_at' => $this->posted_at?->toIso8601String(),
            'lines' => $this->when($journal->relationLoaded('lines'), static function () use ($journal): array {
                /** @var Collection<int, JournalEntryLine> $lines */
                $lines = $journal->getRelation('lines');

                $result = [];
                foreach ($lines as $line) {
                    $result[] = [
                        'id' => $line->id,
                        'account_id' => $line->account_id,
                        'account_code' => $line->account?->account_code,
                        'account_name' => $line->account?->account_name,
                        'debit' => (string) $line->debit,
                        'credit' => (string) $line->credit,
                        'description' => $line->description,
                    ];
                }

                return $result;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
