<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AuditPlan extends Model
{
    protected $fillable = [
        'engagement_id',
        'company_id',
        'overall_materiality',
        'performance_materiality',
        'trivial_threshold',
        'audit_strategy',
        'planning_checklist',
    ];

    protected function casts(): array
    {
        return [
            'overall_materiality' => 'decimal:2',
            'performance_materiality' => 'decimal:2',
            'trivial_threshold' => 'decimal:2',
            'planning_checklist' => 'array',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
