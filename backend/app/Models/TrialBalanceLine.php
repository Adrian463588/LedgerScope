<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TrialBalanceLine extends Model
{
    protected $fillable = [
        'trial_balance_id', 'account_id',
        'opening_debit', 'opening_credit',
        'period_debit', 'period_credit',
        'closing_debit', 'closing_credit',
    ];

    protected function casts(): array
    {
        return [
            'opening_debit' => 'string', 'opening_credit' => 'string',
            'period_debit' => 'string', 'period_credit' => 'string',
            'closing_debit' => 'string', 'closing_credit' => 'string',
        ];
    }

    public function trialBalance(): BelongsTo
    {
        return $this->belongsTo(TrialBalance::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
