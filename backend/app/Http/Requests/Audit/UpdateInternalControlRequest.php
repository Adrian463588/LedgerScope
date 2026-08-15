<?php

declare(strict_types=1);

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateInternalControlRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:200'],
            'control_type' => ['sometimes', 'string', 'in:preventive,detective,corrective'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'frequency' => ['nullable', 'string', 'in:daily,weekly,monthly,quarterly,annual,ad-hoc'],
            'owner' => ['nullable', 'string', 'max:200'],
            'effectiveness' => ['sometimes', 'string', 'in:not_tested,effective,partially_effective,ineffective'],
            'testing_procedure' => ['nullable', 'string'],
            'testing_notes' => ['nullable', 'string'],
            'tested_by' => ['nullable', 'integer', 'exists:users,id'],
            'tested_at' => ['nullable', 'date'],
        ];
    }
}
