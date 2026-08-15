<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $company instanceof Company && $this->user()?->can('update', $company) === true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $company = $this->route('company');

        return [
            'account_code' => ['required', 'string', 'max:80'],
            'account_name' => ['required', 'string', 'max:200'],
            'account_type' => ['required', 'string', 'in:asset,liability,equity,revenue,cost_of_goods_sold,expense,other_income,other_expense'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('chart_of_accounts', 'id')->where(
                    static fn ($query) => $company instanceof Company
                        ? $query->where('company_id', $company->id)
                        : $query->whereRaw('1 = 0'),
                ),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
