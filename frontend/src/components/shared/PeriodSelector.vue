<script setup lang="ts">
import { computed } from 'vue';
import { useCompanyStore } from '@/stores/company.store';

const companyStore = useCompanyStore();

/**
 * Build period options from the active company's fiscal years.
 * Falls back to a reasonable set of quarters when fiscal year data
 * hasn't been fetched yet (e.g. first load).
 */
const periodOptions = computed<string[]>(() => {
  const activeCompany = companyStore.activeCompany;

  // If the API returned fiscal years with quarters, list them.
  // The company shape from the dashboard API may not include fiscal years,
  // so we guard and fall back gracefully.
  const fyCompany = activeCompany as (typeof activeCompany & {
    fiscal_years?: Array<{
      quarters?: Array<{ label?: string; period_label?: string; name?: string }>;
    }>;
  }) | null;

  const quarters =
    fyCompany?.fiscal_years?.flatMap(
      (fy) =>
        fy.quarters?.map(
          (q) => q.label ?? q.period_label ?? q.name ?? '',
        ) ?? [],
    ) ?? [];

  if (quarters.length > 0) return quarters;

  // Fallback: current and previous quarters.
  const now = new Date();
  const year = now.getFullYear();
  const q = Math.ceil((now.getMonth() + 1) / 3);
  return [
    `Q${q} ${year}`,
    q > 1 ? `Q${q - 1} ${year}` : `Q4 ${year - 1}`,
    `FY ${year - 1}`,
  ];
});

const model = defineModel<string>({ default: '' });

// Auto-select the first option when options load.
import { watch } from 'vue';
watch(
  periodOptions,
  (opts) => {
    if (!model.value && opts.length > 0) {
      model.value = opts[0]!;
    }
  },
  { immediate: true },
);
</script>

<template>
  <label class="period-selector">
    <span>Period</span>
    <select v-model="model">
      <option v-for="option in periodOptions" :key="option" :value="option">
        {{ option }}
      </option>
    </select>
  </label>
</template>

<style scoped>
.period-selector {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--text-secondary);
}

select {
  height: 36px;
  border: 1px solid var(--border-strong);
  border-radius: 4px;
  background: white;
  color: var(--text-primary);
  padding: 0 10px;
}
</style>
