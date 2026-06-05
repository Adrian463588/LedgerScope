<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Common\InvitationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInvitation extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'email',
        'name',
        'token',
        'status',
        'role_id',
        'invited_by',
        'expires_at',
        'accepted_at',
        'invitable_type',
        'invitable_id',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'status' => InvitationStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === InvitationStatus::Pending;
    }

    public function isUsable(): bool
    {
        return $this->isPending() && ! $this->isExpired();
    }
}
