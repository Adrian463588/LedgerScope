<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Reporting;

use App\Http\Controllers\Controller;
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
        );
    }

    public function generate(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'report_type' => ['required', 'string', 'in:trial_balance,income_statement,balance_sheet,cash_flow,audit_report,engagement_summary'],
            'title' => ['required', 'string', 'max:200'],
            'format' => ['nullable', 'string', 'in:pdf,xlsx'],
            'parameters' => ['nullable', 'array'],
        ]);

        // EPIC 9: Use service — dispatches GenerateReportJob via queue
        $report = $this->service->queue($validated, $company, $request->user());

        return ApiResponse::created($report, 'Report generation queued.');
    }

    public function show(Company $company, Report $report): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success($report->load('requestedBy'));
    }

    public function download(Company $company, Report $report): JsonResponse
    {
        $this->authorize('view', $company);

        if ($report->status->value !== 'completed') {
            return ApiResponse::domainError('Report is not yet ready for download.');
        }

        if (! $report->file_path) {
            return ApiResponse::domainError('Report file is not available.');
        }

        $url = Storage::disk('private')
            ->temporaryUrl($report->file_path, now()->addMinutes(15));

        return ApiResponse::success(['url' => $url, 'expires_at' => now()->addMinutes(15)->toISOString()], 'Download URL generated.');
    }
}
