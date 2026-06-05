<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Accounting\PeriodStatus;
use App\Enums\Accounting\PeriodType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property int $fiscal_year_id
 * @property int|null $quarter_id
 * @property string $period_name
 * @property PeriodType $period_type
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property PeriodStatus $status
 * @property bool $is_locked
 */
final class AccountingPeriod extends Model
{
    protected $fillable = [
        'company_id',
        'fiscal_year_id',
        'quarter_id',
        'period_name',
        'period_type',
        'start_date',
        'end_date',
        'status',
        'is_locked',
        'locked_at',
        'locked_by',
        'unlock_reason',
    ];

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
            'period_type' => PeriodType::class,
            'status' => PeriodStatus::class,
        ];
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function quarter(): BelongsTo
    {
        return $this->belongsTo(Quarter::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    public function isOpen(): bool
    {
        return $this->status === PeriodStatus::Open && ! $this->is_locked;
    }
}
