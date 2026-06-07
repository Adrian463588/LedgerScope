<?php

declare(strict_types=1);

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRiskAssessmentRequest extends FormRequest
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
            'risk_area' => ['required', 'string', 'max:200'],
            'risk_level' => ['required', 'string', 'in:low,medium,high,critical'],
            'description' => ['nullable', 'string'],
            'mitigation' => ['nullable', 'string'],
            'likelihood' => ['nullable', 'string', 'max:50'],
            'impact' => ['nullable', 'string', 'max:50'],
            'inherent_risk' => ['nullable', 'string', 'max:50'],
            'control_risk' => ['nullable', 'string', 'max:50'],
            'residual_risk' => ['nullable', 'string', 'max:50'],
            'fraud_risk' => ['nullable', 'string', 'max:50'],
            'risk_category' => ['nullable', 'string', 'max:100'],
            'is_significant' => ['nullable', 'boolean'],
        ];
    }
}
