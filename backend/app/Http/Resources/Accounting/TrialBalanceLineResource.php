<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\TrialBalanceLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TrialBalanceLine */
final class TrialBalanceLineResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'account_code' => $this->account?->account_code,
            'account_name' => $this->account?->account_name,
            'opening_debit' => (string) $this->opening_debit,
            'opening_credit' => (string) $this->opening_credit,
            'movement_debit' => (string) $this->period_debit,
            'movement_credit' => (string) $this->period_credit,
            'ending_debit' => (string) $this->closing_debit,
            'ending_credit' => (string) $this->closing_credit,
        ];
    }
}
