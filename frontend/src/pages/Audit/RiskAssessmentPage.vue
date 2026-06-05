<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import { onMounted } from 'vue';

import RiskHeatmap from '@/components/audit/RiskHeatmap.vue';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import UnsupportedFeaturePanel from '@/components/shared/UnsupportedFeaturePanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { unsupportedFeatures } from '@/data/fixtures';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

onMounted(() => ui.setBreadcrumbs(['Audit', 'Risk Assessment']));
</script>

<template>
  <PageHeader eyebrow="Audit Planning & Risk" title="Risk Assessment & Heatmap" subtitle="Significant risk register and residual score visibility.">
    <template #actions>
      <AppButton variant="primary" :icon="Plus">Add New Risk</AppButton>
    </template>
  </PageHeader>
  <section class="risk-grid">
    <SectionPanel title="Risk Heatmap"><RiskHeatmap /></SectionPanel>
    <UnsupportedFeaturePanel :feature="unsupportedFeatures.risk" />
  </section>
  <AppTable
    :columns="[
      { key: 'title', label: 'Risk Title' },
      { key: 'inherent', label: 'Inherent Risk', isStatus: true },
      { key: 'residual', label: 'Residual Risk', isStatus: true },
    ]"
    :data="[
      { title: 'Financial reporting accuracy', inherent: 'critical', residual: 'high' },
      { title: 'Privileged ERP access', inherent: 'high', residual: 'medium' },
    ]"
  />
</template>

<style scoped>
.risk-grid {
  display: grid;
  grid-template-columns: 0.8fr 1.2fr;
  gap: 20px;
}
</style>
