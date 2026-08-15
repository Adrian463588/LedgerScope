<script setup lang="ts">
import { computed } from "vue";

import type { RiskAssessment } from "@/types";

const props = defineProps<{
  risks?: RiskAssessment[];
}>();

const cells = Array.from({ length: 25 }, (_, index) => {
  const row = Math.floor(index / 5) + 1;
  const col = (index % 5) + 1;
  const score = row * col;
  return { id: index, label: `${row}x${col}`, score };
});
const recordedScores = computed(
  () =>
    new Set<number>(
      (props.risks ?? []).map((risk) => {
        const level = risk.residual_risk ?? risk.risk_level ?? "low";
        return level === "critical"
          ? 25
          : level === "high"
            ? 16
            : level === "medium"
              ? 9
              : 4;
      }),
    ),
);
</script>

<template>
  <div class="risk-heatmap" aria-label="Risk heatmap">
    <span
      v-for="cell in cells"
      :key="cell.id"
      :class="[
        cell.score > 15 ? 'critical' : cell.score > 8 ? 'high' : 'low',
        { recorded: recordedScores.has(cell.score) },
      ]"
    >
      {{ cell.score }}
    </span>
  </div>
  <p class="heatmap-caption">
    Recorded risk levels: {{ props.risks?.length ?? 0 }}
  </p>
</template>

<style scoped>
.risk-heatmap {
  display: grid;
  grid-template-columns: repeat(5, minmax(42px, 1fr));
  gap: 6px;
}

span {
  display: grid;
  aspect-ratio: 1;
  place-items: center;
  border-radius: 4px;
  font-family: "IBM Plex Mono", monospace;
  font-weight: 700;
}

.recorded {
  outline: 2px solid var(--text-primary);
  outline-offset: 1px;
}

.heatmap-caption {
  margin: 12px 0 0;
  color: var(--text-secondary);
  font-size: 0.8125rem;
}

.low {
  background: var(--status-success-bg);
  color: var(--status-success);
}

.high {
  background: var(--status-warning-bg);
  color: var(--status-warning);
}

.critical {
  background: var(--status-danger-bg);
  color: var(--status-danger);
}
</style>
