<?php

declare(strict_types=1);

namespace App\Http\Requests\Audit;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

final class StoreEngagementRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:200'],
            'engagement_type' => ['required', 'string', 'in:accounting_service,financial_analysis,external_audit,internal_audit,review_engagement,compilation_engagement,tax_compliance,risk_advisory,internal_control_review'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'scope' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
        ];
    }
}
