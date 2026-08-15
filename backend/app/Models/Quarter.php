<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property int $fiscal_year_id
 * @property string $quarter_code Q1|Q2|Q3|Q4
 * @property bool $is_locked
 */
final class Quarter extends Model
{
    protected $fillable = [
        'company_id',
        'fiscal_year_id',
        'quarter_code',
        'start_date',
        'end_date',
        'status',
        'is_locked',
        'locked_at',
        'locked_by',
        'unlock_reason',
    ];

    protected static function booted(): void
    {
        self::created(function (Quarter $quarter): void {
            $keys = [
                'all_journals_posted',
                'imported_data_validated',
                'trial_balance_balanced',
                'bank_reconciliation_completed',
                'ar_reconciliation_completed',
                'ap_reconciliation_completed',
                'tax_account_reviewed',
                'accrual_entries_posted',
                'prepayment_entries_posted',
                'depreciation_entries_posted',
                'financial_statements_generated',
                'manager_review_completed',
                'quarter_approved',
                'quarter_locked',
            ];

            foreach ($keys as $key) {
                $quarter->checklists()->firstOrCreate([
                    'checklist_key' => $key,
                ], [
                    'is_completed' => false,
                ]);
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'locked_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(AccountingPeriod::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(QuarterClosingChecklist::class);
    }

    public function isLocked(): bool
    {
        return $this->is_locked;
    }
}
