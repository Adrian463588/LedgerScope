<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string|null $legal_name
 * @property string $currency
 * @property int $fiscal_year_start_month
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
final class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'registration_number',
        'tax_id',
        'industry',
        'currency',
        'fiscal_year_start_month',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'website',
        'logo_path',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fiscal_year_start_month' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function companyUsers(): HasMany
    {
        return $this->hasMany(CompanyUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_users')
            ->withPivot(['role_id', 'job_title', 'is_primary', 'joined_at']);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CompanyContact::class);
    }

    public function fiscalYears(): HasMany
    {
        return $this->hasMany(FiscalYear::class);
    }

    public function accountingPeriods(): HasMany
    {
        return $this->hasMany(AccountingPeriod::class);
    }

    public function quarters(): HasMany
    {
        return $this->hasMany(Quarter::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function reconciliations(): HasMany
    {
        return $this->hasMany(Reconciliation::class);
    }

    public function financialStatements(): HasMany
    {
        return $this->hasMany(FinancialStatement::class);
    }

    public function statementTemplates(): HasMany
    {
        return $this->hasMany(StatementTemplate::class);
    }

    public function trialBalances(): HasMany
    {
        return $this->hasMany(TrialBalance::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function importBatches(): HasMany
    {
        return $this->hasMany(ImportBatch::class);
    }

    public function batches(): HasMany
    {
        return $this->importBatches();
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }
}
