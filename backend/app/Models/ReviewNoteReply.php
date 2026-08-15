<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property Carbon $created_at
 */
final class ReviewNoteReply extends Model
{
    protected $fillable = [
        'review_note_id',
        'user_id',
        'message',
    ];

    public function reviewNote(): BelongsTo
    {
        return $this->belongsTo(ReviewNote::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
