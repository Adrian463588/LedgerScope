<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\Audit\DocumentRequestResource;
use App\Http\Resources\Evidence\EvidenceFileResource;
use App\Http\Responses\ApiResponse;
use App\Models\DocumentRequest;
use App\Models\Engagement;
use App\Services\Audit\DocumentRequestService;
use App\Services\Evidence\EvidenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ClientPortalController — EPIC 14 PRD §6.23
 *
 * Client-facing portal: view assigned document requests and upload evidence.
 * Unlike auditors, clients only see their own company's requests.
 *
 * Routes (under /client/):
 *   GET  /document-requests              — list requests assigned to this user
 *   GET  /document-requests/{id}         — view one request
 *   POST /document-requests/{id}/upload  — upload file and auto-submit
 */
final class ClientPortalController extends Controller
{
    public function __construct(
        private readonly DocumentRequestService $documentRequestService,
        private readonly EvidenceService $evidenceService,
    ) {}

    /**
     * List document requests assigned to (or scoped to) the authenticated client user.
     */
    public function listRequests(Request $request): JsonResponse
    {
        $user = $request->user();
        $userCompanyIds = $user->companies()->pluck('companies.id')->toArray();

        $requests = DocumentRequest::whereIn('company_id', $userCompanyIds)
            ->where(function ($query) use ($user): void {
                $query->where('assigned_to', $user->id)
                    ->orWhereHas('engagement', fn ($q) => $q->whereHas('members', fn ($m) => $m->where('user_id', $user->id)));
            })
            ->with(['engagement', 'requestedBy', 'evidenceFile'])
            ->orderByDesc('due_date')
            ->get();

        return ApiResponse::success(DocumentRequestResource::collection($requests));
    }

    /**
     * View a single document request (client must be assigned or in engagement).
     */
    public function showRequest(Request $request, DocumentRequest $documentRequest): JsonResponse
    {
        $this->authorizeClientAccess($request, $documentRequest);

        if ($documentRequest->status === 'requested') {
            $this->documentRequestService->startWork($documentRequest, $request->user());
        }

        return ApiResponse::success(new DocumentRequestResource($documentRequest->fresh()->load(['engagement', 'requestedBy', 'evidenceFile'])));
    }

    /**
     * Upload a file and automatically submit it against a document request.
     */
    public function uploadAndSubmit(Request $request, DocumentRequest $documentRequest): JsonResponse
    {
        $this->authorizeClientAccess($request, $documentRequest);

        if (! in_array($documentRequest->status, ['requested', 'in_progress', 'rejected'], true)) {
            return ApiResponse::domainError("Request is [{$documentRequest->status}] — only requested, in_progress, or rejected requests can be submitted.");
        }

        $request->validate([
            'file' => ['required', 'file', 'max:20480'], // 20 MB limit
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $engagement = $documentRequest->engagement;
        if (! $engagement instanceof Engagement) {
            return ApiResponse::domainError('Invalid engagement associated with document request.');
        }

        // Upload evidence via EvidenceService (stores to private disk, generates checksum)
        $evidenceFile = $this->evidenceService->upload(
            $request->file('file'),
            $engagement,
            $request->user(),
            $request->input('description'),
        );

        // Submit the document request with the evidence reference
        $this->documentRequestService->submit($documentRequest, $evidenceFile->id, $request->user());

        return ApiResponse::success([
            'document_request' => new DocumentRequestResource($documentRequest->fresh()->load(['engagement', 'evidenceFile'])),
            'evidence_file' => new EvidenceFileResource($evidenceFile),
        ], 'Document submitted successfully.');
    }

    /**
     * Authorize that the current client user can access this document request.
     */
    private function authorizeClientAccess(Request $request, DocumentRequest $documentRequest): void
    {
        $user = $request->user();
        $userCompanyIds = $user->companies()->pluck('companies.id')->toArray();

        if (! in_array($documentRequest->company_id, $userCompanyIds, true)) {
            abort(403, 'You do not have access to this company\'s document requests.');
        }

        $canAccess = $documentRequest->assigned_to === $user->id
            || $documentRequest->engagement->members()->where('user_id', $user->id)->exists();

        if (! $canAccess) {
            abort(403, 'You do not have access to this document request.');
        }
    }
}
