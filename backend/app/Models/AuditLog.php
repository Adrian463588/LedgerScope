<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * AuditLog — IMMUTABLE APPEND-ONLY model.
 *
 * Rules (enforced by AGENTS.md):
 * - No updated_at column.
 * - save() and update() throw LogicException — audit logs cannot be modified.
 * - Only create() is allowed from application code.
 */
class AuditLog extends Model
{
    /**
     * Audit logs have no updated_at column by design.
     */
    public const UPDATED_AT = null;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'company_id',
        'action',
        'object_type',
        'object_id',
        'before_value',
        'after_value',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'before_value' => 'array',
            'after_value' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  array<string, mixed>  $options
     *
     * @throws LogicException Always — audit logs are immutable.
     */
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new LogicException('Audit logs are immutable. Use AuditLog::create() instead.');
        }

        return parent::save($options);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $options
     *
     * @throws LogicException Always — audit logs cannot be updated.
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new LogicException('Audit logs are immutable and cannot be updated.');
    }

    /**
     * @throws LogicException Always — audit logs cannot be deleted.
     */
    public function delete(): ?bool
    {
        throw new LogicException('Audit logs are immutable and cannot be deleted.');
    }
}
