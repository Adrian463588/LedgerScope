<script setup lang="ts">
import { CheckCircle2, Lock } from 'lucide-vue-next';
import { onMounted } from 'vue';

import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AmountDisplay from '@/components/ui/AmountDisplay.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { useNotification } from '@/composables/useNotification';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const confirmDialog = useConfirmDialog();
const notification = useNotification();

async function approve(): Promise<void> {
  const ok = await confirmDialog.confirm({ title: 'Approve Statement', message: 'Approve this financial statement version for reporting?', tone: 'primary', confirmLabel: 'Approve' });
  if (ok) notification.success('Financial statement approved.');
}

onMounted(() => ui.setBreadcrumbs(['Financial', 'Statements']));
</script>

<template>
  <PageHeader title="Financial Statements" subtitle="Statement package with approval and lock workflow.">
    <template #actions>
      <StatusBadge status="Approved" />
      <AppButton :icon="CheckCircle2" @click="approve">Approve</AppButton>
      <AppButton variant="locked" :icon="Lock">Locked</AppButton>
    </template>
  </PageHeader>
  <SectionPanel title="Statement of Profit or Loss" subtitle="Q1 2026">
    <div class="statement-row"><span>Revenue</span><AmountDisplay value="2450000.00" currency /></div>
    <div class="statement-row"><span>Cost of sales</span><AmountDisplay value="980000.00" currency kind="credit" /></div>
    <div class="statement-row total"><span>Gross profit</span><AmountDisplay value="1470000.00" currency /></div>
    <div class="statement-row"><span>Operating expenses</span><AmountDisplay value="910000.00" currency kind="credit" /></div>
    <div class="statement-row total"><span>Net profit</span><AmountDisplay value="560000.00" currency /></div>
  </SectionPanel>
</template>

<style scoped>
.statement-row {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  padding: 12px 0;
}

.total {
  font-weight: 700;
}
</style>
