<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AuditTrailController — Epic 7C PRD §Audit Trail / Compliance Logs
 *
 * Exposes a paginated, filterable view of the immutable audit_logs table.
 * Only super_admin / firm_admin roles can access this endpoint.
 */
final class AuditTrailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAuditTrail', AuditLog::class);

        $request->validate([
            'action'      => ['nullable', 'string', 'max:100'],
            'user_id'     => ['nullable', 'integer', 'exists:users,id'],
            'company_id'  => ['nullable', 'integer'],
            'object_type' => ['nullable', 'string', 'max:100'],
            'object_id'   => ['nullable', 'integer'],
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page'    => ['nullable', 'integer', 'min:10', 'max:200'],
        ]);

        $logs = AuditLog::with('user:id,name,email')
            ->when($request->input('action'), fn ($q, $v) => $q->where('action', $v))
            ->when($request->input('user_id'), fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->input('company_id'), fn ($q, $v) => $q->where('company_id', $v))
            ->when($request->input('object_type'), fn ($q, $v) => $q->where('object_type', $v))
            ->when($request->input('object_id'), fn ($q, $v) => $q->where('object_id', $v))
            ->when($request->input('date_from'), fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->input('date_to'), fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->orderByDesc('created_at')
            ->paginate((int) $request->input('per_page', 50));

        return ApiResponse::paginated($logs, 'Audit trail loaded.');
    }
}
