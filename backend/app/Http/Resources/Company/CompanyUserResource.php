<?php

declare(strict_types=1);

namespace App\Http\Resources\Company;

use App\Models\CompanyUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CompanyUser */
final class CompanyUserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
            'job_title' => $this->job_title,
            'is_primary' => $this->is_primary,
            'joined_at' => $this->joined_at?->toIso8601String(),
        ];
    }
}
