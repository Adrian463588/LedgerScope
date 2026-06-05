<script setup lang="ts">
import { Download, RefreshCw } from 'lucide-vue-next';
import { onMounted } from 'vue';

import SparklinePanel from '@/components/charts/SparklinePanel.vue';
import MetricCard from '@/components/shared/MetricCard.vue';
import PeriodSelector from '@/components/shared/PeriodSelector.vue';
import ProgressTracker from '@/components/shared/ProgressTracker.vue';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { demoKpis } from '@/data/fixtures';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

onMounted(() => ui.setBreadcrumbs(['Dashboard']));
</script>

<template>
  <PageHeader title="Financial Overview" subtitle="High-confidence accounting and audit control center.">
    <template #actions>
      <PeriodSelector />
      <AppButton :icon="RefreshCw">Refresh</AppButton>
      <AppButton variant="primary" :icon="Download">Export Report</AppButton>
    </template>
  </PageHeader>
  <section class="kpi-grid">
    <MetricCard v-for="kpi in demoKpis" :key="kpi.label" v-bind="kpi" />
  </section>
  <section class="dashboard-grid">
    <SectionPanel title="Revenue vs Expenses" subtitle="12-month trend">
      <SparklinePanel title="Operating movement" :values="[42, 55, 48, 72, 66, 82, 76, 88]" />
    </SectionPanel>
    <SectionPanel title="Quarterly Progress" subtitle="Close checklist">
      <ProgressTracker label="Q1 close" :value="78" />
      <ProgressTracker label="Audit completion" :value="64" />
      <ProgressTracker label="Evidence accepted" :value="52" />
    </SectionPanel>
  </section>
  <section class="dashboard-grid">
    <SectionPanel title="Recent Activity">
      <ul class="activity-list">
        <li><StatusBadge status="posted" /> Journal Entry #1092 posted</li>
        <li><StatusBadge status="review" /> Trial balance reviewed</li>
        <li><StatusBadge status="generating" /> Audit report generating</li>
      </ul>
    </SectionPanel>
    <SectionPanel title="Open Tasks">
      <ul class="task-list">
        <li><input type="checkbox" checked /> Journals posted</li>
        <li><input type="checkbox" /> AR reconciliation</li>
        <li><input type="checkbox" /> Manager review</li>
      </ul>
    </SectionPanel>
  </section>
</template>

<style scoped>
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 20px;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.7fr);
  gap: 20px;
}

.activity-list,
.task-list {
  display: grid;
  gap: 14px;
  margin: 0;
  padding: 0;
  list-style: none;
}

li {
  display: flex;
  align-items: center;
  gap: 10px;
}

@media (max-width: 1100px) {
  .kpi-grid,
  .dashboard-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 760px) {
  .kpi-grid,
  .dashboard-grid {
    grid-template-columns: 1fr;
  }
}
</style>
