<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property-read Collection<int, TrialBalanceLine> $lines */
final class TrialBalance extends Model
{
    protected $fillable = [
        'company_id', 'accounting_period_id', 'status',
        'total_debit', 'total_credit', 'is_balanced',
        'generated_at', 'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'total_debit' => 'string',
            'total_credit' => 'string',
            'is_balanced' => 'boolean',
            'generated_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class, 'accounting_period_id');
    }

    /** @return HasMany<TrialBalanceLine, self> */
    public function lines(): HasMany
    {
        return $this->hasMany(TrialBalanceLine::class);
    }
}
