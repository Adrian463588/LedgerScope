<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Accounting\StatementTemplateResource;
use App\Http\Responses\ApiResponse;
use App\Models\Company;
use App\Models\StatementTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StatementTemplateController extends Controller
{
    public function index(Company $company): JsonResponse
    {
        $this->authorize('view', $company);

        return ApiResponse::success(
            StatementTemplateResource::collection(StatementTemplate::where('company_id', $company->id)->get()),
        );
    }

    public function store(Request $request, Company $company): JsonResponse
    {
        $this->authorize('update', $company);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'statement_type' => ['required', 'string', 'in:balance_sheet,income_statement,cash_flow,equity_changes'],
            'structure' => ['nullable', 'array'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $template = StatementTemplate::create(array_merge($validated, ['company_id' => $company->id]));

        return ApiResponse::created(new StatementTemplateResource($template), 'Template created.');
    }
}
