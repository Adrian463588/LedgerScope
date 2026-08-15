<?php

declare(strict_types=1);

namespace App\Http\Requests\Audit;

use App\Models\Engagement;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateAuditPlanRequest extends FormRequest
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
            'overall_materiality' => ['nullable', 'decimal:0,2', 'min:0'],
            'performance_materiality' => ['nullable', 'decimal:0,2', 'min:0'],
            'trivial_threshold' => ['nullable', 'decimal:0,2', 'min:0'],
            'audit_strategy' => ['nullable', 'string'],
            'planning_checklist' => ['nullable', 'array'],
        ];
    }
}
