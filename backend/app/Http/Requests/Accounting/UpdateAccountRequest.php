<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateAccountRequest extends FormRequest
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
            'account_name' => ['sometimes', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
