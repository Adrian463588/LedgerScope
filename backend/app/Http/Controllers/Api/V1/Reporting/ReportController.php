<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Reporting;

use App\Events\AuditActionRecorded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\GenerateReportRequest;
use App\Http\Resources\Reporting\ReportResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\Report;
use App\Services\Reporting\ReportGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * ReportController — EPIC 9 PRD §6.24
 * Fixed to use ReportGeneratorService instead of direct model creation.
 */
final class ReportController extends Controller
{
    public function __construct(private readonly ReportGeneratorService $service) {}

    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::paginated(
            Report::where('company_id', $company->id)
                ->with('requestedBy')
                ->orderByDesc('created_at')
                ->paginate(20),
            'Reports loaded.',
            static fn (Report $report): ReportResource => new ReportResource($report),
        );
    }

    public function generate(GenerateReportRequest $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        // EPIC 9: Use service — dispatches GenerateReportJob via queue
        $report = $this->service->queue($request->validated(), $company, $request->user());

        return ApiResponse::created(new ReportResource($report), 'Report generation queued.');
    }

    public function show(Company $company, Report $report): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('view', $report);

        return ApiResponse::success(new ReportResource($report->load('requestedBy')));
    }

    public function approve(Request $request, Company $company, Report $report): JsonResponse
    {
        $this->authorize('update', $company);
        $this->authorize('update', $report);

        return ApiResponse::success(
            new ReportResource($this->service->approve($report, $request->user())),
            'Report approved.',
        );
    }

    public function download(Request $request, Company $company, Report $report): JsonResponse
    {
        $this->authorize('view', $company);
        $this->authorize('view', $report);

        if (! in_array($report->status->value, ['completed', 'approved'], true)) {
            return ApiResponse::domainError('Report is not yet ready for download.');
        }

        if (! $report->file_path) {
            return ApiResponse::domainError('Report file is not available.');
        }

        if (! Storage::disk('private')->exists($report->file_path)) {
            return ApiResponse::domainError('Report file is not available.');
        }

        event(new AuditActionRecorded(
            userId: $request->user()->id,
            action: 'report.download',
            companyId: $company->id,
            objectType: 'Report',
            objectId: $report->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        $url = Storage::disk('private')
            ->temporaryUrl($report->file_path, now()->addMinutes(15));

        return ApiResponse::success(['url' => $url, 'expires_at' => now()->addMinutes(15)->toISOString()], 'Download URL generated.');
    }
}
