<?php

declare(strict_types=1);

namespace App\Http\Requests\Reporting;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

final class GenerateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $company instanceof Company && $this->user()?->can('update', $company) === true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'report_type' => ['required', 'string', 'in:trial_balance,income_statement,balance_sheet,cash_flow,equity_changes,audit_report,engagement_summary'],
            'title' => ['required', 'string', 'max:200'],
            'format' => ['nullable', 'string', 'in:pdf,xlsx,csv'],
            'parameters' => ['nullable', 'array'],
            'parameters.accounting_period_id' => ['nullable', 'integer'],
            'parameters.engagement_id' => ['nullable', 'integer'],
            'parameters.financial_statement_id' => ['nullable', 'integer'],
        ];
    }
}
