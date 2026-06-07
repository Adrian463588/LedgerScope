<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WorkingPaper extends Model
{
    protected $fillable = [
        'engagement_id', 'title', 'paper_ref', 'status',
        'prepared_by', 'reviewed_by', 'reviewed_at', 'content',
        'sign_off_at', 'sign_off_by', 'is_locked', 'locked_at', 'locked_by',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'sign_off_at' => 'datetime',
            'locked_at' => 'datetime',
            'is_locked' => 'boolean',
        ];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function evidenceFiles(): HasMany
    {
        return $this->hasMany(EvidenceFile::class);
    }

    public function reviewNotes(): HasMany
    {
        return $this->hasMany(ReviewNote::class);
    }
}
