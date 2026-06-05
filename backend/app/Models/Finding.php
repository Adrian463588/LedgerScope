<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Audit\FindingSeverity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Finding extends Model
{
    protected $fillable = [
        'engagement_id', 'title', 'description', 'severity', 'status',
        'recommendation', 'management_response', 'due_date', 'assigned_to',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'severity' => FindingSeverity::class];
    }

    public function engagement(): BelongsTo
    {
        return $this->belongsTo(Engagement::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
