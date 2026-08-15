<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

final class StoreFiscalYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $company instanceof Company && $this->user()?->can('update', $company) === true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['year' => ['required', 'integer', 'min:2000', 'max:2100']];
    }
}
