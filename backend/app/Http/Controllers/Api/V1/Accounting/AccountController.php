<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Events\AuditActionRecorded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\ImportAccountsRequest;
use App\Http\Requests\Accounting\StoreAccountRequest;
use App\Http\Requests\Accounting\UpdateAccountRequest;
use App\Http\Resources\Accounting\AccountResource;
use App\Http\Resources\Accounting\ImportBatchResource;
use App\Http\Responses\ApiResponse;
use App\Jobs\Imports\ImportAccountsJob;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\ImportBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class AccountController extends Controller
{
    public function index(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $accounts = ChartOfAccount::where('company_id', $company->id)
            ->when($request->query('type'), fn ($q) => $q->where('account_type', $request->query('type')))
            ->when($request->query('active_only'), fn ($q) => $q->where('is_active', true))
            ->orderBy('account_code')
            ->get();

        return ApiResponse::success(AccountResource::collection($accounts));
    }

    public function store(StoreAccountRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validated();

        $account = DB::transaction(function () use ($validated, $company, $request): ChartOfAccount {
            $account = ChartOfAccount::create(array_merge($validated, ['company_id' => $company->id]));

            event(new AuditActionRecorded(
                userId: $request->user()->id,
                action: 'account.create',
                companyId: $company->id,
                objectType: 'ChartOfAccount',
                objectId: $account->id,
                after: $account->toArray(),
            ));

            return $account;
        });

        return ApiResponse::created(new AccountResource($account), 'Account created.');
    }

    public function show(Company $company, ChartOfAccount $account): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('view', $account);

        return ApiResponse::success(new AccountResource($account->load('parent', 'children')));
    }

    public function update(UpdateAccountRequest $request, Company $company, ChartOfAccount $account): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $account);

        $validated = $request->validated();

        DB::transaction(function () use ($account, $validated, $company, $request): void {
            $before = $account->toArray();
            $account->update($validated);

            event(new AuditActionRecorded(
                userId: $request->user()->id,
                action: 'account.update',
                companyId: $company->id,
                objectType: 'ChartOfAccount',
                objectId: $account->id,
                before: $before,
                after: $account->fresh()->toArray(),
            ));
        });

        return ApiResponse::success(new AccountResource($account->fresh()), 'Account updated.');
    }

    public function destroy(Request $request, Company $company, ChartOfAccount $account): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('delete', $account);

        if ($account->journalLines()->exists()) {
            throw new \DomainException('Cannot delete account with posted journal lines. Archive it instead.');
        }

        DB::transaction(function () use ($account, $company, $request): void {
            $before = $account->toArray();
            $account->delete();

            event(new AuditActionRecorded(
                userId: $request->user()->id,
                action: 'account.delete',
                companyId: $company->id,
                objectType: 'ChartOfAccount',
                objectId: $account->id,
                before: $before,
            ));
        });

        return ApiResponse::success(null, 'Account deleted.');
    }

    public function import(ImportAccountsRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('imports', 'private');

        if ($path === false) {
            return ApiResponse::serverError('Failed to store uploaded file.');
        }

        try {
            $batch = DB::transaction(function () use ($company, $request, $originalName, $path): ImportBatch {
                $batch = ImportBatch::create([
                    'company_id' => $company->id,
                    'user_id' => $request->user()->id,
                    'import_type' => 'chart_of_accounts',
                    'status' => 'pending',
                    'original_filename' => $originalName,
                    'file_path' => $path,
                ]);

                dispatch(new ImportAccountsJob($company->id, $batch->id, $path))->afterCommit();

                return $batch;
            });
        } catch (\Throwable $exception) {
            Storage::disk('private')->delete($path);

            throw $exception;
        }

        return ApiResponse::created(new ImportBatchResource($batch->fresh()), 'Import queued.');
    }

    public function importStatus(Company $company, int $batch): JsonResponse
    {
        $this->authorize('view', $company);

        $importBatch = $company->batches()->findOrFail($batch);

        return ApiResponse::success(new ImportBatchResource($importBatch));
    }
}
