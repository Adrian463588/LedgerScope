<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @property-read Collection<int, ReviewNoteReply> $replies */
final class ReviewNote extends Model
{
    protected $fillable = [
        'working_paper_id',
        'created_by',
        'content',
        'status',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function workingPaper(): BelongsTo
    {
        return $this->belongsTo(WorkingPaper::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /** @return HasMany<ReviewNoteReply, self> */
    public function replies(): HasMany
    {
        return $this->hasMany(ReviewNoteReply::class);
    }
}
