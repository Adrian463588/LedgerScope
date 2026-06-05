<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EvidenceFile extends Model
{
    protected $fillable = [
        'working_paper_id', 'engagement_id', 'uploaded_by',
        'original_name', 'storage_path', 'mime_type', 'file_size_bytes',
        'checksum', 'status',
    ];

    protected function casts(): array
    {
        return ['file_size_bytes' => 'integer'];
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
}
