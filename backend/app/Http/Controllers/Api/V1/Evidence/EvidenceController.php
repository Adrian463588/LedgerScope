<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Evidence;

use App\Http\Controllers\Controller;
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
        $this->authorize('view', $engagement->company);

        return ApiResponse::success(
            $engagement->evidenceFiles()->with(['uploadedBy', 'acceptedBy', 'rejectedBy'])->get(),
        );
    }

    public function store(Request $request, Engagement $engagement): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $request->validate([
            'file' => ['required', 'file', 'max:51200'], // 50 MB in KB
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $evidence = $this->service->upload(
            file: $request->file('file'),
            engagement: $engagement,
            uploadedBy: $request->user(),
            description: $request->input('description'),
        );

        return ApiResponse::created($evidence, 'Evidence file uploaded.');
    }

    public function show(Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        $this->authorize('view', $engagement->company);

        return ApiResponse::success($evidence->load(['uploadedBy', 'acceptedBy', 'rejectedBy']));
    }

    public function accept(Request $request, Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $this->service->accept($evidence, $request->user());

        return ApiResponse::success($evidence->fresh(), 'Evidence accepted.');
    }

    public function reject(Request $request, Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5'],
        ]);

        $this->service->reject($evidence, $request->user(), $validated['reason']);

        return ApiResponse::success($evidence->fresh(), 'Evidence rejected.');
    }

    public function download(Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        $this->authorize('view', $engagement->company);

        return ApiResponse::success($this->service->getDownloadUrl($evidence), 'Download URL generated.');
    }

    public function destroy(Request $request, Engagement $engagement, EvidenceFile $evidence): JsonResponse
    {
        $this->authorize('update', $engagement->company);

        $this->service->delete($evidence, $request->user());

        return ApiResponse::noContent();
    }
}
