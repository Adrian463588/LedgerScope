<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Accounting\JournalSourceType;
use App\Enums\Accounting\JournalStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property int $accounting_period_id
 * @property string|null $journal_number
 * @property string $description
 * @property Carbon $journal_date
 * @property JournalStatus $status
 * @property JournalSourceType $source_type
 */
final class JournalEntry extends Model
{
    protected $fillable = [
        'company_id',
        'accounting_period_id',
        'journal_number',
        'reference',
        'description',
        'journal_date',
        'status',
        'source_type',
        'reversed_from_id',
        'created_by',
        'submitted_by',
        'approved_by',
        'posted_by',
        'posted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'journal_date' => 'date',
            'posted_at' => 'datetime',
            'status' => JournalStatus::class,
            'source_type' => JournalSourceType::class,
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedFrom(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'reversed_from_id');
    }

    public function isPosted(): bool
    {
        return $this->status === JournalStatus::Posted;
    }

    public function isDraft(): bool
    {
        return $this->status === JournalStatus::Draft;
    }

    /**
     * Posted journals are immutable — any mutating save() attempt throws.
     *
     * @param  array<string, mixed>  $options
     */
    public function save(array $options = []): bool
    {
        if ($this->exists && $this->isPosted()) {
            throw new \DomainException('Posted journal entries are immutable. Use reversal to correct.');
        }

        return parent::save($options);
    }
}
