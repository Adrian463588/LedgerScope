<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\MatchReconciliationRequest;
use App\Http\Requests\Accounting\StoreReconciliationRequest;
use App\Http\Resources\Accounting\ReconciliationItemResource;
use App\Http\Resources\Accounting\ReconciliationResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\Reconciliation;
use App\Services\Accounting\ReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReconciliationController extends Controller
{
    public function __construct(private readonly ReconciliationService $service) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(
            ReconciliationResource::collection(
                Reconciliation::where('company_id', $company->id)->with('items')->get(),
            ),
        );
    }

    public function store(StoreReconciliationRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validated();

        $accountBelongsToCompany = $company->accounts()
            ->whereKey($validated['account_id'])
            ->exists();
        $periodBelongsToCompany = $company->accountingPeriods()
            ->whereKey($validated['accounting_period_id'])
            ->exists();

        if (! $accountBelongsToCompany || ! $periodBelongsToCompany) {
            return ApiResponse::notFound('Accounting resource not found for this company.');
        }

        $rec = $this->service->create($validated, $company, $request->user());

        return ApiResponse::created(new ReconciliationResource($rec), 'Reconciliation created.');
    }

    public function autoMatch(Request $request, Company $company, Reconciliation $reconciliation): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $reconciliation);

        $matched = $this->service->autoMatch($reconciliation, $request->user());

        return ApiResponse::success(new ReconciliationResource($matched), 'Reconciliation auto-match completed.');
    }

    public function match(MatchReconciliationRequest $request, Company $company, Reconciliation $reconciliation): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $reconciliation);

        $validated = $request->validated();

        $item = $this->service->match(
            $reconciliation,
            (int) $validated['item_id'],
            (int) $validated['journal_line_id'],
            $request->user(),
        );

        return ApiResponse::success(new ReconciliationItemResource($item), 'Reconciliation item matched.');
    }

    public function approve(Request $request, Company $company, Reconciliation $reconciliation): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $reconciliation);

        $this->service->approve($reconciliation, $request->user());

        return ApiResponse::success(new ReconciliationResource($reconciliation->fresh(['items'])), 'Reconciliation approved.');
    }

    public function lock(Request $request, Company $company, Reconciliation $reconciliation): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $reconciliation);

        $this->service->lock($reconciliation, $request->user());

        return ApiResponse::success(new ReconciliationResource($reconciliation->fresh(['items'])), 'Reconciliation locked.');
    }
}
