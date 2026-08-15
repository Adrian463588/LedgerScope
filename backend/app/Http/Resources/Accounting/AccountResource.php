<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ChartOfAccount */
final class AccountResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'code' => $this->account_code,
            'name' => $this->account_name,
            'account_code' => $this->account_code,
            'account_name' => $this->account_name,
            'type' => $this->account_type->value,
            'account_type' => $this->account_type->value,
            'is_active' => $this->is_active,
            'allow_journal_entries' => $this->allow_journal_entries,
            'parent_id' => $this->parent_id,
            'description' => $this->description,
        ];
    }
}
