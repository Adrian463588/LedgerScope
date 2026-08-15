<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Evidence;

use App\Events\AuditActionRecorded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Evidence\RejectEvidenceRequest;
use App\Http\Requests\Evidence\UploadEvidenceRequest;
use App\Http\Resources\Evidence\EvidenceFileResource;
use App\Http\Responses\ApiResponse;
use App\Models\Engagement;
use App\Models\EvidenceFile;
use App\Services\Evidence\EvidenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * EvidenceController — EPIC 4 PRD §6.16
 *
 * Routes (all scoped under /engagements/{engagement}/evidence):
 *   GET    /               index
 *   POST   /               store (upload)
 *   GET    /{evidence}     show
 *   POST   /{evidence}/accept
 *   POST   /{evidence}/reject
 *   GET    /{evidence}/download
 *   DELETE /{evidence}
 */
final class EvidenceController extends Controller
{
    public function __construct(private readonly EvidenceService $service) {}

    public function index(Engagement $engagement): JsonResponse
    {
        $this->authorize('view', $engagement);

        return ApiResponse::success(EvidenceFileResource::collection(
            $engagement->evidenceFiles()->with(['uploadedBy', 'acceptedBy', 'rejectedBy'])->get(),
        ));
    }

    public function store(UploadEvidenceRequest $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement);

        $evidence = $this->service->upload(
            file: $request->file('file'),
            engagement: $engagement,
            uploadedBy: $request->user(),
            description: $request->input('description'),
        );

        return ApiResponse::created(new EvidenceFileResource($evidence), 'Evidence file uploaded.');
    }

    public function show(Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        if ($evidence->engagement_id !== $engagement->id) {
            return ApiResponse::notFound('Evidence not found.');
        }

        $this->authorize('view', $engagement);
        $this->authorize('view', $evidence);

        return ApiResponse::success(new EvidenceFileResource($evidence->load(['uploadedBy', 'acceptedBy', 'rejectedBy'])));
    }

    public function accept(Request $request, Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        if ($evidence->engagement_id !== $engagement->id) {
            return ApiResponse::notFound('Evidence not found.');
        }

        $this->authorize('update', $engagement);
        $this->authorize('review', $evidence);

        $this->service->accept($evidence, $request->user());

        return ApiResponse::success(new EvidenceFileResource($evidence->fresh()), 'Evidence accepted.');
    }

    public function reject(RejectEvidenceRequest $request, Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        if ($evidence->engagement_id !== $engagement->id) {
            return ApiResponse::notFound('Evidence not found.');
        }

        $this->authorize('update', $engagement);
        $this->authorize('review', $evidence);

        $this->service->reject($evidence, $request->user(), $request->validated()['reason']);

        return ApiResponse::success(new EvidenceFileResource($evidence->fresh()), 'Evidence rejected.');
    }

    public function download(Request $request, Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        if ($evidence->engagement_id !== $engagement->id) {
            return ApiResponse::notFound('Evidence not found.');
        }

        $this->authorize('view', $engagement);
        $this->authorize('download', $evidence);

        event(new AuditActionRecorded(
            userId: $request->user()->id,
            action: 'evidence.download',
            companyId: $engagement->company_id,
            objectType: 'EvidenceFile',
            objectId: $evidence->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        return ApiResponse::success($this->service->getDownloadUrl($evidence), 'Download URL generated.');
    }

    public function destroy(Request $request, Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        if ($evidence->engagement_id !== $engagement->id) {
            return ApiResponse::notFound('Evidence not found.');
        }

        $this->authorize('update', $engagement);
        $this->authorize('delete', $evidence);

        $this->service->delete($evidence, $request->user());

        return ApiResponse::noContent();
    }
}
