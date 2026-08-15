<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

final class StoreJournalRequest extends FormRequest
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
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
            'description' => ['required', 'string', 'min:3'],
            'journal_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['required', 'decimal:0,2', 'min:0'],
            'lines.*.credit' => ['required', 'decimal:0,2', 'min:0'],
            'lines.*.description' => ['nullable', 'string'],
        ];
    }
}
