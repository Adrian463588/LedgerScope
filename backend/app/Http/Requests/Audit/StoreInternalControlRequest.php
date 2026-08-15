<?php

declare(strict_types=1);

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;

final class StoreInternalControlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'control_type' => ['required', 'string', 'in:preventive,detective,corrective'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'frequency' => ['nullable', 'string', 'in:daily,weekly,monthly,quarterly,annual,ad-hoc'],
            'owner' => ['nullable', 'string', 'max:200'],
            'effectiveness' => ['nullable', 'string', 'in:not_tested,effective,partially_effective,ineffective'],
            'testing_procedure' => ['nullable', 'string'],
            'testing_notes' => ['nullable', 'string'],
        ];
    }
}
