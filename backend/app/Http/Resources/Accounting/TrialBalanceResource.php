<?php

declare(strict_types=1);

namespace App\Http\Resources\Accounting;

use App\Models\TrialBalance;
use App\Models\TrialBalanceLine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TrialBalance */
final class TrialBalanceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var TrialBalance $trialBalance */
        $trialBalance = $this->resource;

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'accounting_period_id' => $this->accounting_period_id,
            'status' => $this->status,
            'total_debit' => (string) $this->total_debit,
            'total_credit' => (string) $this->total_credit,
            'is_balanced' => $this->is_balanced,
            'generated_at' => $this->generated_at?->toIso8601String(),
            'lines' => $this->when($trialBalance->relationLoaded('lines'), static function () use ($trialBalance): array {
                /** @var Collection<int, TrialBalanceLine> $lines */
                $lines = $trialBalance->getRelation('lines');

                $result = [];
                foreach ($lines as $line) {
                    $result[] = [
                        'id' => $line->id,
                        'account_id' => $line->account_id,
                        'account_code' => $line->account?->account_code,
                        'account_name' => $line->account?->account_name,
                        'opening_debit' => (string) $line->opening_debit,
                        'opening_credit' => (string) $line->opening_credit,
                        'movement_debit' => (string) $line->period_debit,
                        'movement_credit' => (string) $line->period_credit,
                        'ending_debit' => (string) $line->closing_debit,
                        'ending_credit' => (string) $line->closing_credit,
                    ];
                }

                return $result;
            }),
        ];
    }
}
