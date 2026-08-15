<?php

declare(strict_types=1);

namespace App\Http\Requests\Evidence;

use Illuminate\Foundation\Http\FormRequest;

final class RejectEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:5', 'max:2000']];
    }
}
