<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StatementTemplate extends Model
{
    protected $fillable = ['company_id', 'name', 'statement_type', 'structure', 'is_default'];

    protected function casts(): array
    {
        return ['structure' => 'array', 'is_default' => 'boolean'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
