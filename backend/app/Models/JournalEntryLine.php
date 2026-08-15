<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Accounting\JournalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $journal_entry_id
 * @property int $account_id
 * @property string $debit DECIMAL(20,2) stored as string
 * @property string $credit DECIMAL(20,2) stored as string
 * @property string $currency
 */
final class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'description',
        'debit',
        'credit',
        'currency',
        'exchange_rate',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'debit' => 'string',   // Keep as string — use Money VO for arithmetic
            'credit' => 'string',
            'exchange_rate' => 'string',
        ];
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function save(array $options = []): bool
    {
        $this->assertMutable();

        return parent::save($options);
    }

    public function delete(): ?bool
    {
        $this->assertMutable();

        return parent::delete();
    }

    private function assertMutable(): void
    {
        $journal = $this->journalEntry()->with('accountingPeriod')->first();

        if ($journal === null) {
            return;
        }

        if ($journal->isPosted() || $journal->status === JournalStatus::Reversed) {
            throw new \DomainException('Lines of posted journal entries are immutable.');
        }

        if ($journal->accountingPeriod?->isLocked()) {
            throw new \DomainException('Journal lines in locked periods are immutable.');
        }
    }
}
