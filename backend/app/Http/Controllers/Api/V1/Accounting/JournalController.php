<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\ImportJournalsRequest;
use App\Http\Requests\Accounting\JournalTransitionRequest;
use App\Http\Requests\Accounting\StoreJournalRequest;
use App\Http\Requests\Accounting\UpdateJournalRequest;
use App\Http\Resources\Accounting\ImportBatchResource;
use App\Http\Resources\Accounting\JournalEntryResource;
use App\Http\Responses\ApiResponse;
use App\Jobs\Imports\ImportJournalsJob;
use App\Models\Company;
use App\Models\ImportBatch;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class JournalController extends Controller
{
    public function __construct(private readonly JournalService $service) {}

    public function index(Request $request, Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        $journals = JournalEntry::where('company_id', $company->id)
            ->with('lines.account')
            ->when($request->query('status'), fn ($q) => $q->where('status', (string) $request->query('status')))
            ->when($request->query('period_id'), fn ($q) => $q->where('accounting_period_id', (string) $request->query('period_id')))
            ->orderByDesc('journal_date')
            ->paginate((int) $request->query('per_page', '20'));

        return ApiResponse::paginated(
            $journals,
            'Journals loaded.',
            static fn (JournalEntry $journal): JournalEntryResource => new JournalEntryResource($journal),
        );
    }

    public function store(StoreJournalRequest $request, Company $company): JsonResponse
    {
        /**
         * @var array{
         *     accounting_period_id: int,
         *     description: string,
         *     journal_date: string,
         *     reference?: string,
         *     lines: array<int, array{account_id: int, description?: string, debit: string, credit: string}>
         * } $validated
         */
        $validated = $request->validated();
        $journal = $this->service->create($validated, $request->user(), $company);

        return ApiResponse::created(new JournalEntryResource($journal), 'Journal entry created.');
    }

    public function show(Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('view', $journal);

        return ApiResponse::success(new JournalEntryResource($journal->load('lines.account')));
    }

    public function update(UpdateJournalRequest $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $journal);

        if (! $journal->isDraft()) {
            throw new \DomainException('Only draft journals can be edited.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($journal, $validated): void {
            $journal->update($validated);
        });

        return ApiResponse::success(new JournalEntryResource($journal->fresh()), 'Journal updated.');
    }

    public function submit(Request $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $journal);
        $this->service->submit($journal, $request->user());

        return ApiResponse::success(new JournalEntryResource($journal->fresh()), 'Journal submitted.');
    }

    public function approve(Request $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $journal);
        $this->service->approve($journal, $request->user());

        return ApiResponse::success(new JournalEntryResource($journal->fresh()), 'Journal approved.');
    }

    public function post(Request $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $journal);
        $this->service->post($journal, $request->user());

        return ApiResponse::success(new JournalEntryResource($journal->fresh()), 'Journal posted.');
    }

    public function reverse(JournalTransitionRequest $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $journal);

        $reversal = $this->service->reverse($journal, $request->user(), $request->validated()['reason']);

        return ApiResponse::created(new JournalEntryResource($reversal), 'Journal reversed.');
    }

    public function reject(JournalTransitionRequest $request, Company $company, JournalEntry $journal): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $journal);

        $this->service->reject($journal, $request->user(), $request->validated()['reason']);

        return ApiResponse::success(new JournalEntryResource($journal->fresh()), 'Journal rejected.');
    }

    public function import(ImportJournalsRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('imports', 'private');

        if ($path === false) {
            return ApiResponse::serverError('Failed to store uploaded file.');
        }

        $batch = DB::transaction(function () use ($company, $request, $originalName, $path): ImportBatch {
            $batch = ImportBatch::create([
                'company_id' => $company->id,
                'user_id' => $request->user()->id,
                'import_type' => 'journal_entries',
                'status' => 'pending',
                'original_filename' => $originalName,
                'file_path' => $path,
            ]);

            ImportJournalsJob::dispatch($company->id, $batch->id, $path, $request->user()->id)->afterCommit();

            return $batch;
        });

        return ApiResponse::created(new ImportBatchResource($batch->fresh()), 'Import queued.');
    }
}
