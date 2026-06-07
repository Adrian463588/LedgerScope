<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DocumentRequest — EPIC 5 PBC (Prepared by Client) Portal.
 *
 * PRD §6.15
 */
final class DocumentRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'engagement_id',
        'company_id',
        'title',
        'description',
        'status',
        'due_date',
        'requested_by',
        'assigned_to',
        'rejection_reason',
        'evidence_file_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
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

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function evidenceFile(): BelongsTo
    {
        return $this->belongsTo(EvidenceFile::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->isPast()
            && ! in_array($this->status, ['submitted', 'accepted', 'cancelled'], true);
    }
}
