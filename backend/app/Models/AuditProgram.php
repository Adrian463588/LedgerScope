<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property-read Collection<int, AuditProgramStep> $steps */
final class AuditProgram extends Model
{
    protected $fillable = [
        'engagement_id',
        'name',
        'objectives',
        'status',
    ];

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    /** @return HasMany<AuditProgramStep, self> */
    public function steps(): HasMany
    {
        return $this->hasMany(AuditProgramStep::class)->orderBy('step_number');
    }
}
