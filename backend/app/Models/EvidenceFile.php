<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Common\EvidenceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class EvidenceFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'working_paper_id',
        'engagement_id',
        'uploaded_by',
        'original_name',
        'storage_path',
        'mime_type',
        'file_size_bytes',
        'checksum',
        'status',
        'description',
        'accepted_by',
        'accepted_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'version',
        'previous_version_id',
        'custody_log',
    ];

    protected function casts(): array
    {
        return [
            'file_size_bytes' => 'integer',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'status' => EvidenceStatus::class,
            'custody_log' => 'array',
        ];
    }

    public function workingPaper(): BelongsTo
    {
        return $this->belongsTo(WorkingPaper::class);
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function previousVersion(): BelongsTo
    {
        return $this->belongsTo(EvidenceFile::class, 'previous_version_id');
    }
}
