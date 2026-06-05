<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $role
 * @property bool $is_primary
 */
final class CompanyContact extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'role',
        'is_primary',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
