<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FinancialStatement extends Model
{
    protected $fillable = [
        'company_id', 'accounting_period_id', 'template_id',
        'statement_type', 'status', 'version', 'is_locked', 'data',
        'generated_by', 'approved_by', 'approved_at', 'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_locked' => 'boolean',
            'version' => 'integer',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
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

    public function save(array $options = []): bool
    {
        if ($this->exists && (bool) $this->getRawOriginal('is_locked')) {
            throw new \DomainException('Locked financial statements are immutable.');
        }

        return parent::save($options);
    }

    public function delete(): ?bool
    {
        if ($this->exists && (bool) $this->getRawOriginal('is_locked')) {
            throw new \DomainException('Locked financial statements are immutable.');
        }

        return parent::delete();
    }
}
