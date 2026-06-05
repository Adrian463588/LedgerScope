<script setup lang="ts">
import { onMounted, ref } from 'vue';

import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppTabs from '@/components/ui/AppTabs.vue';
import AmountDisplay from '@/components/ui/AmountDisplay.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const tab = ref('pl');

onMounted(() => ui.setBreadcrumbs(['Financial', 'Statement Builder']));
</script>

<template>
  <PageHeader title="Statement of Profit or Loss" subtitle="Collapsible statement builder with mapped account groups." />
  <AppTabs v-model="tab" :tabs="[{ label: 'Profit or Loss', value: 'pl' }, { label: 'Balance Sheet', value: 'bs' }, { label: 'Cash Flow', value: 'cf' }]" />
  <SectionPanel title="Current Version" subtitle="v1.1.2">
    <div class="builder-row"><strong>Revenue</strong><AmountDisplay value="2450000.00" currency /></div>
    <div class="builder-row"><span>Operating Expenses</span><AmountDisplay value="1890000.00" currency kind="credit" /></div>
    <div class="builder-row total"><strong>Net Profit</strong><AmountDisplay value="560000.00" currency /></div>
  </SectionPanel>
</template>

<style scoped>
.builder-row {
  display: flex;
  justify-content: space-between;
  padding: 14px 0;
}

.total {
  border-top: 2px solid var(--text-primary);
}
</style>
