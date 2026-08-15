<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

final class StoreReconciliationRequest extends FormRequest
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
            'account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'accounting_period_id' => ['required', 'integer', 'exists:accounting_periods,id'],
            'reconciliation_type' => ['required', 'string', 'in:bank,ar,ap'],
            'book_balance' => ['required', 'decimal:0,2'],
            'bank_balance' => ['required', 'decimal:0,2'],
        ];
    }
}
