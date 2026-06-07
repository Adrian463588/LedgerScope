<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\ImportBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Jobs\Imports\ImportAccountsJob;
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

        return ApiResponse::success($accounts);
    }

    public function store(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'account_code' => ['required', 'string', 'max:80'],
            'account_name' => ['required', 'string', 'max:200'],
            'account_type' => ['required', 'string', 'in:asset,liability,equity,revenue,cost_of_goods_sold,expense,other_income,other_expense'],
            'parent_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $account = ChartOfAccount::create(array_merge($validated, ['company_id' => $company->id]));

        return ApiResponse::created($account, 'Account created.');
    }

    public function show(Company $company, ChartOfAccount $account): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success($account->load('parent', 'children'));
    }

    public function update(Request $request, Company $company, ChartOfAccount $account): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'account_name' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $account->update($validated);

        return ApiResponse::success($account->fresh(), 'Account updated.');
    }

    public function destroy(Company $company, ChartOfAccount $account): JsonResponse
    {
        $this->authorize('update', $company);

        if ($account->journalLines()->exists()) {
            throw new \DomainException('Cannot delete account with posted journal lines. Archive it instead.');
        }

        $account->delete();

        return ApiResponse::success(null, 'Account deleted.');
    }

    public function import(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('imports', 'local');

        if ($path === false) {
            return ApiResponse::error('Failed to store uploaded file.', 500);
        }

        $batch = ImportBatch::create([
            'company_id' => $company->id,
            'user_id' => $request->user()->id,
            'import_type' => 'chart_of_accounts',
            'status' => 'pending',
            'original_filename' => $originalName,
            'file_path' => $path,
        ]);

        dispatch(new ImportAccountsJob($company->id, $batch->id, $path));

        return ApiResponse::created($batch->fresh(), 'Import queued.');
    }

    public function importStatus(Company $company, int $batch): JsonResponse
    {
        $this->authorize('view', $company);

        $importBatch = ImportBatch::findOrFail($batch);

        return ApiResponse::success($importBatch);
    }
}
