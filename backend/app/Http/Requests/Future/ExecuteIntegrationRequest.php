<?php

declare(strict_types=1);

namespace App\Http\Requests\Future;

use Illuminate\Foundation\Http\FormRequest;

final class ExecuteIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'operation' => ['required', 'string', 'max:100'],
            'parameters' => ['nullable', 'array'],
        ];
    }
}
