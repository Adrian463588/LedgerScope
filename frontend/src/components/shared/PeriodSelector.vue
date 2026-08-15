<script setup lang="ts">
import { watch } from "vue";

import { useCompanyStore } from "@/stores/company.store";
import { usePeriodStore } from "@/stores/period.store";

const companyStore = useCompanyStore();
const periodStore = usePeriodStore();
const model = defineModel<number | null>({ default: null });

watch(
  () => companyStore.activeCompanyId,
  (companyId) => {
    model.value = null;
    if (companyId !== null) void periodStore.fetchForCompany(companyId);
    else periodStore.reset();
  },
  { immediate: true },
);

watch(
  () => periodStore.selectedPeriodId,
  (periodId) => {
    if (model.value === null && periodId !== null) model.value = periodId;
  },
  { immediate: true },
);
</script>

<template>
  <label class="period-selector">
    <span>Period</span>
    <select
      v-if="periodStore.options.length > 0"
      v-model="model"
      :disabled="periodStore.isLoading"
      aria-label="Reporting period"
    >
      <option
        v-for="option in periodStore.options"
        :key="option.id"
        :value="option.id"
      >
        {{ option.label }}
      </option>
    </select>
    <span v-else class="period-status" role="status">
      {{
        periodStore.isLoading
          ? "Loading…"
          : periodStore.error
            ? "Unavailable"
            : "No periods"
      }}
    </span>
  </label>
</template>

<style scoped>
.period-selector {
  display: inline-flex;
  align-items: center;
  gap: var(--spacing-2);
  color: var(--text-secondary);
}

select {
  height: 36px;
  border: 1px solid var(--border-strong);
  border-radius: var(--radius-sm);
  background: var(--surface);
  color: var(--text-primary);
  padding: 0 var(--spacing-3);
}

.period-status {
  color: var(--text-muted);
  font-size: 0.8125rem;
}
</style>
