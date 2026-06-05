<?php

declare(strict_types=1);

namespace App\Services\Company;

use App\Models\Company;
use App\Models\CompanyContact;
use App\Models\CompanyUser;
use App\Models\User;
use App\Support\Guards\CompanyAccessGuard;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * CompanyService — all business logic for Company domain.
 * Controllers must NOT contain business logic (AGENTS.md §2).
 */
final class CompanyService
{
    /**
     * @return LengthAwarePaginator<Company>
     */
    public function listForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        $query = Company::query()->withTrashed(false);

        // Non-admin users only see their assigned companies
        if (! $user->hasRole('super_admin') && ! $user->hasRole('firm_admin')) {
            $query->whereHas('companyUsers', fn ($q) => $q->where('user_id', $user->id));
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);

        return $company->fresh();
    }

    public function softDelete(Company $company): void
    {
        $company->delete();
    }

    public function assignUser(Company $company, User $user, array $data): CompanyUser
    {
        CompanyAccessGuard::flushCache();

        return DB::transaction(function () use ($company, $user, $data): CompanyUser {
            /** @var CompanyUser $companyUser */
            $companyUser = CompanyUser::updateOrCreate(
                ['company_id' => $company->id, 'user_id' => $user->id],
                [
                    'role_id' => $data['role_id'] ?? null,
                    'job_title' => $data['job_title'] ?? null,
                    'is_primary' => $data['is_primary'] ?? false,
                ],
            );

            return $companyUser;
        });
    }

    public function removeUser(Company $company, User $user): void
    {
        CompanyAccessGuard::flushCache();

        CompanyUser::where('company_id', $company->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * @return Collection<int, CompanyContact>
     */
    public function listContacts(Company $company): Collection
    {
        return $company->contacts()->orderBy('is_primary', 'desc')->get();
    }

    public function addContact(Company $company, array $data): CompanyContact
    {
        return $company->contacts()->create($data);
    }
}
