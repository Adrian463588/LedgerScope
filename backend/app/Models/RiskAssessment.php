<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RiskAssessment extends Model
{
    protected $fillable = [
        'engagement_id',
        'risk_area',
        'risk_level',
        'description',
        'mitigation',
        'likelihood',
        'impact',
        'inherent_risk',
        'control_risk',
        'residual_risk',
        'fraud_risk',
        'risk_category',
        'is_significant',
    ];

    protected function casts(): array
    {
        return [
            'is_significant' => 'boolean',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }
}
