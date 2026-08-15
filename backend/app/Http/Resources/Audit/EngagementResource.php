<?php

declare(strict_types=1);

namespace App\Http\Resources\Audit;

use App\Models\Engagement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Engagement */
final class EngagementResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'engagement_type' => $this->engagement_type,
            'type' => $this->engagement_type,
            'status' => $this->status->value,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'period' => $this->start_date && $this->end_date
                ? $this->start_date->format('Y-m-d').' – '.$this->end_date->format('Y-m-d')
                : null,
            'progress' => $this->progressPercentage(),
            'risk' => 'unassessed',
            'scope' => $this->scope,
            'objectives' => $this->objectives,
            'lead_auditor_id' => $this->lead_auditor_id,
            'manager_id' => $this->manager_id,
            'partner_id' => $this->partner_id,
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }

    private function progressPercentage(): int
    {
        $total = $this->workingPapers()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->workingPapers()
            ->whereIn('status', ['signed_off', 'approved', 'locked'])
            ->count();

        return (int) round(($completed / $total) * 100);
    }
}
