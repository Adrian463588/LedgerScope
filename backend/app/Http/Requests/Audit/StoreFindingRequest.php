<?php

declare(strict_types=1);

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;

final class StoreFindingRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'severity' => ['required', 'string', 'in:low,medium,high,critical'],
            'root_cause' => ['nullable', 'string'],
            'impact' => ['nullable', 'string'],
            'recommendation' => ['nullable', 'string'],
            'action_plan' => ['nullable', 'string'],
            'responsible_person' => ['nullable', 'string', 'max:200'],
            'due_date' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
