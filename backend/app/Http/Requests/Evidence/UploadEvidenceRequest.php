<?php

declare(strict_types=1);

namespace App\Http\Requests\Evidence;

use App\Models\Engagement;
use Illuminate\Foundation\Http\FormRequest;

final class UploadEvidenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $engagement = $this->route('engagement');

        return $engagement instanceof Engagement && $this->user()?->can('update', $engagement) === true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:51200'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
