<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Audit\EngagementStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Engagement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'engagement_type', 'status',
        'start_date', 'end_date', 'lead_auditor_id', 'manager_id', 'partner_id',
        'scope', 'objectives', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => EngagementStatus::class,
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function leadAuditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(EngagementMember::class);
    }

    public function workingPapers(): HasMany
    {
        return $this->hasMany(WorkingPaper::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class);
    }

    public function evidenceFiles(): HasMany
    {
        return $this->hasMany(EvidenceFile::class);
    }

    public function documentRequests(): HasMany
    {
        return $this->hasMany(DocumentRequest::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    public function auditPlan(): HasOne
    {
        return $this->hasOne(AuditPlan::class);
    }

    public function riskAssessments(): HasMany
    {
        return $this->hasMany(RiskAssessment::class);
    }
}
