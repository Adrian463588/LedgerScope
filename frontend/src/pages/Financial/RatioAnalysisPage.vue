<script setup lang="ts">
import { onMounted } from 'vue';

import SparklinePanel from '@/components/charts/SparklinePanel.vue';
import RiskHeatmap from '@/components/audit/RiskHeatmap.vue';
import MetricCard from '@/components/shared/MetricCard.vue';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

const ratios = [
  { label: 'Current Ratio', value: '1.84x', trend: '+0.22', tone: 'success' as const },
  { label: 'Net Profit Margin', value: '22.9%', trend: '+4.1%', tone: 'success' as const },
  { label: 'Debt to Equity', value: '0.62x', trend: 'Stable', tone: 'info' as const },
];

onMounted(() => ui.setBreadcrumbs(['Financial', 'Ratio Analysis']));
</script>

<template>
  <PageHeader title="Ratio Analysis" subtitle="Financial analysis dashboard with risk-aware ratio trends." />
  <section class="ratio-grid">
    <MetricCard v-for="ratio in ratios" :key="ratio.label" v-bind="ratio" />
  </section>
  <section class="analysis-grid">
    <SectionPanel title="Revenue & Expense Trend">
      <SparklinePanel title="Four-quarter trend" :values="[54, 62, 68, 82, 46, 52, 58, 64]" />
    </SectionPanel>
    <SectionPanel title="Solvency Risk Assessment">
      <RiskHeatmap />
    </SectionPanel>
  </section>
</template>

<style scoped>
.ratio-grid,
.analysis-grid {
  display: grid;
  gap: 20px;
}

.ratio-grid {
  grid-template-columns: repeat(3, 1fr);
}

.analysis-grid {
  grid-template-columns: 1.2fr 0.8fr;
}

@media (max-width: 900px) {
  .ratio-grid,
  .analysis-grid {
    grid-template-columns: 1fr;
  }
}
</style>
