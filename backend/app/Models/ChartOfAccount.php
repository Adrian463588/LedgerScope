<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Accounting\AccountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $company_id
 * @property string $account_code
 * @property string $account_name
 * @property AccountType $account_type
 * @property bool $is_active
 */
final class ChartOfAccount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'company_id',
        'parent_id',
        'account_code',
        'account_name',
        'account_type',
        'description',
        'is_active',
        'allow_journal_entries',
        'level',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_type' => AccountType::class,
            'is_active' => 'boolean',
            'allow_journal_entries' => 'boolean',
            'level' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }
}
