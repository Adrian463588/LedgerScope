<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateJournalRequest extends FormRequest
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
            'description' => ['sometimes', 'string', 'min:3'],
            'journal_date' => ['sometimes', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}
