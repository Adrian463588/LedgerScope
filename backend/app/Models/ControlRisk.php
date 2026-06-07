<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ControlRisk extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'internal_control_id',
        'engagement_id',
        'risk_name',
        'risk_description',
        'likelihood',
        'impact',
        'residual_risk',
        'mitigating_factors',
    ];

    public function internalControl(): BelongsTo
    {
        return $this->belongsTo(InternalControl::class);
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }
}
