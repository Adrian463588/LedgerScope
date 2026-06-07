<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\ImportBatch;
use App\Jobs\Imports\ImportJournalsJob;

final class JournalController extends Controller
{
    public function __construct(private readonly JournalService $service) {}

    public function index(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $journals = JournalEntry::where('company_id', $company->id)
            ->when($request->query('status'), fn ($q) => $q->where('status', (string) $request->query('status')))
            ->when($request->query('period_id'), fn ($q) => $q->where('accounting_period_id', (string) $request->query('period_id')))
            ->orderByDesc('journal_date')
            ->paginate((int) $request->query('per_page', '20'));

        return ApiResponse::paginated($journals);
    }

    public function store(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
            'description' => ['required', 'string'],
            'journal_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string'],
        ]);

        $journal = $this->service->create($validated, $request->user());

        return ApiResponse::created($journal, 'Journal entry created.');
    }

    public function show(Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success($journal->load('lines.account'));
    }

    public function update(Request $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);

        if (! $journal->isDraft()) {
            throw new \DomainException('Only draft journals can be edited.');
        }

        $validated = $request->validate([
            'description' => ['sometimes', 'string'],
            'journal_date' => ['sometimes', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $journal->update($validated);

        return ApiResponse::success($journal->fresh(), 'Journal updated.');
    }

    public function submit(Request $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);
        $this->service->submit($journal, $request->user());

        return ApiResponse::success($journal->fresh(), 'Journal submitted.');
    }

    public function approve(Request $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);
        $this->service->approve($journal, $request->user());

        return ApiResponse::success($journal->fresh(), 'Journal approved.');
    }

    public function post(Request $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);
        $this->service->post($journal, $request->user());

        return ApiResponse::success($journal->fresh(), 'Journal posted.');
    }

    public function reverse(Request $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $reversal = $this->service->reverse($journal, $request->user(), $validated['reason']);

        return ApiResponse::created($reversal, 'Journal reversed.');
    }

    public function reject(Request $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $this->service->reject($journal, $request->user(), $validated['reason']);

        return ApiResponse::success($journal->fresh(), 'Journal rejected.');
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
            'import_type' => 'journal_entries',
            'status' => 'pending',
            'original_filename' => $originalName,
            'file_path' => $path,
        ]);

        dispatch(new ImportJournalsJob($company->id, $batch->id, $path, $request->user()->id));

        return ApiResponse::created($batch->fresh(), 'Import queued.');
    }
}
