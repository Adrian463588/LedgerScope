<?php

declare(strict_types=1);

namespace App\Http\Requests\Audit;

use App\Models\Engagement;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateEngagementRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:200'],
            'status' => ['sometimes', 'string'],
            'end_date' => ['sometimes', 'date'],
            'scope' => ['nullable', 'string'],
            'objectives' => ['nullable', 'string'],
        ];
    }
}
