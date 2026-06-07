<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Audit\FindingSeverity;
use App\Enums\Audit\FindingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Finding extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'engagement_id',
        'company_id',
        'title',
        'description',
        'category',
        'severity',
        'status',
        'root_cause',
        'impact',
        'recommendation',
        'management_response',
        'action_plan',
        'responsible_person',
        'due_date',
        'assigned_to',
        'created_by',
        'approved_by',
        'is_repeat',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'severity' => FindingSeverity::class,
            'status' => FindingStatus::class,
            'is_repeat' => 'boolean',
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

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
