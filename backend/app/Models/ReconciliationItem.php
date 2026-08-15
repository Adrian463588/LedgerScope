<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReconciliationItem extends Model
{
    protected $fillable = [
        'reconciliation_id', 'journal_line_id', 'item_type',
        'amount', 'transaction_date', 'description', 'reference', 'is_matched',
    ];

    protected function casts(): array
    {
        return ['amount' => 'string', 'transaction_date' => 'date', 'is_matched' => 'boolean'];
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(Reconciliation::class);
    }

    public function journalLine(): BelongsTo
    {
        return $this->belongsTo(JournalEntryLine::class, 'journal_line_id');
    }
}
