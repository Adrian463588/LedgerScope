<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Reporting;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::paginated(
            Report::where('company_id', $company->id)
                ->orderByDesc('created_at')
                ->paginate(20),
        );
    }

    public function generate(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'report_type' => ['required', 'string'],
            'title' => ['required', 'string', 'max:200'],
            'format' => ['nullable', 'string', 'in:pdf,xlsx'],
            'parameters' => ['nullable', 'array'],
        ]);

        $report = Report::create(array_merge($validated, [
            'company_id' => $company->id,
            'requested_by' => $request->user()->id,
            'status' => 'pending',
        ]));

        // Dispatch generation job here (Phase 9 full impl)
        return ApiResponse::created($report, 'Report generation queued.');
    }
}
