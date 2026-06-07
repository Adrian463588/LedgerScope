<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class InternalControl extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'engagement_id',
        'name',
        'control_type',
        'category',
        'description',
        'frequency',
        'owner',
        'effectiveness',
        'testing_procedure',
        'testing_notes',
        'tested_by',
        'tested_at',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'tested_at' => 'datetime',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    /** @return HasMany<ControlRisk, InternalControl> */
    public function controlRisks(): HasMany
    {
        return $this->hasMany(ControlRisk::class);
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }
}
