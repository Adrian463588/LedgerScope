<?php

declare(strict_types=1);

namespace App\Http\Resources\Company;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Company */
final class CompanyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'legal_name' => $this->legal_name ?? $this->name,
            'registration_number' => $this->registration_number,
            'tax_id' => $this->tax_id,
            'industry' => $this->industry,
            'currency' => $this->currency,
            'fiscal_year_start_month' => $this->fiscal_year_start_month,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'fiscal_year_end' => null,
            'reporting_period' => null,
            'is_active' => $this->is_active,
            'status' => $this->is_active ? 'active' : 'inactive',
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
