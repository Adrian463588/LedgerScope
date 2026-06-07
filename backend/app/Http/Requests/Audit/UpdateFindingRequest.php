<?php

declare(strict_types=1);

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateFindingRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:200'],
            'description' => ['sometimes', 'string'],
            'severity' => ['sometimes', 'string', 'in:low,medium,high,critical'],
            'recommendation' => ['nullable', 'string'],
            'action_plan' => ['nullable', 'string'],
            'responsible_person' => ['nullable', 'string', 'max:200'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
