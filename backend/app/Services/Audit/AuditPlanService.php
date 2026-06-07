<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\AuditPlan;
use App\Models\Engagement;
use Illuminate\Support\Facades\DB;

final class AuditPlanService
{
    /**
     * Get or create a planning document for the given engagement.
     */
    public function getOrCreate(Engagement $engagement): AuditPlan
    {
        return DB::transaction(function () use ($engagement): AuditPlan {
            /** @var AuditPlan|null $plan */
            $plan = AuditPlan::where('engagement_id', $engagement->id)->first();

            if (! $plan) {
                $defaultChecklist = [
                    ['key' => 'understand_entity', 'name' => 'Understand the Entity and Its Environment', 'is_completed' => false],
                    ['key' => 'calculate_materiality', 'name' => 'Determine Materiality Thresholds', 'is_completed' => false],
                    ['key' => 'risk_assessment_link', 'name' => 'Link Risk Assessment and Materiality', 'is_completed' => false],
                    ['key' => 'audit_strategy_design', 'name' => 'Design General Audit Strategy', 'is_completed' => false],
                ];

                $plan = AuditPlan::create([
                    'engagement_id' => $engagement->id,
                    'company_id' => $engagement->company_id,
                    'overall_materiality' => 0.00,
                    'performance_materiality' => 0.00,
                    'trivial_threshold' => 0.00,
                    'audit_strategy' => '',
                    'planning_checklist' => $defaultChecklist,
                ]);
            }

            return $plan;
        });
    }

    /**
     * Update the materiality and strategy planning document.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(AuditPlan $plan, array $data): AuditPlan
    {
        $plan->update($data);

        return $plan->fresh();
    }
}
