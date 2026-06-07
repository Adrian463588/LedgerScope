<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Audit;

use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\StoreDocumentRequestRequest;
use App\Http\Responses\ApiResponse;
use App\Models\DocumentRequest;
use App\Models\Engagement;
use App\Services\Audit\DocumentRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * DocumentRequestController — EPIC 5 PRD §6.15 (PBC Portal)
 *
 * PBC = Prepared-By-Client. Manages requests sent to clients for supporting documents.
 *
 * Routes (under /engagements/{engagement}/document-requests):
 *   GET    /               index
 *   POST   /               store
 *   GET    /{request}      show
 *   POST   /{request}/submit
 *   POST   /{request}/accept
 *   POST   /{request}/reject
 *   DELETE /{request}
 */
final class DocumentRequestController extends Controller
{
    public function __construct(private readonly DocumentRequestService $service) {}

    public function index(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(
            $engagement->documentRequests()
                ->with(['requestedBy', 'assignedTo', 'evidenceFile'])
                ->orderByDesc('created_at')
                ->get(),
        );
    }

    public function store(StoreDocumentRequestRequest $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validated();

        $docRequest = $this->service->create($validated, $engagement, $request->user());

        return ApiResponse::created($docRequest, 'Document request created.');
    }

    public function show(Engagement $engagement, DocumentRequest $documentRequest): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(
            $documentRequest->load(['requestedBy', 'assignedTo', 'evidenceFile']),
        );
    }

    public function submit(Request $request, Engagement $engagement, DocumentRequest $documentRequest): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validate([
            'evidence_file_id' => ['required', 'integer', 'exists:evidence_files,id'],
        ]);

        $this->service->submit($documentRequest, $validated['evidence_file_id'], $request->user());

        return ApiResponse::success($documentRequest->fresh(), 'Document submitted.');
    }

    public function accept(Request $request, Engagement $engagement, DocumentRequest $documentRequest): JsonResponse
    {
        $this->authorize('update', $engagement);

        $this->service->accept($documentRequest, $request->user());

        return ApiResponse::success($documentRequest->fresh(), 'Document request accepted.');
    }

    public function reject(Request $request, Engagement $engagement, DocumentRequest $documentRequest): JsonResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $this->service->reject($documentRequest, $request->user(), $validated['reason']);

        return ApiResponse::success($documentRequest->fresh(), 'Document request rejected.');
    }

    public function destroy(Request $request, Engagement $engagement, DocumentRequest $documentRequest): JsonResponse
    {
        $this->authorize('update', $engagement);

        if (! in_array($documentRequest->status, ['draft', 'requested', 'rejected'], true)) {
            return ApiResponse::domainError('Only draft, requested, or rejected requests can be cancelled.');
        }

        $documentRequest->delete();

        return ApiResponse::noContent();
    }
}
