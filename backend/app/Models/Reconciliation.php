<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property-read Collection<int, ReconciliationItem> $items */
final class Reconciliation extends Model
{
    protected $fillable = [
        'company_id', 'account_id', 'accounting_period_id',
        'reconciliation_type', 'status',
        'book_balance', 'bank_balance', 'difference',
        'approved_at', 'approved_by', 'locked_at', 'locked_by',
    ];

    protected function casts(): array
    {
        return [
            'book_balance' => 'string',
            'bank_balance' => 'string',
            'difference' => 'string',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class, 'accounting_period_id');
    }

    /** @return HasMany<ReconciliationItem, self> */
    public function items(): HasMany
    {
        return $this->hasMany(ReconciliationItem::class);
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function save(array $options = []): bool
    {
        if ($this->exists && $this->getRawOriginal('status') === 'locked') {
            throw new \DomainException('Locked reconciliations are immutable.');
        }

        return parent::save($options);
    }
}
