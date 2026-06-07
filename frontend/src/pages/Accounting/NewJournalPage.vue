<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import BalanceIndicator from '@/components/accounting/BalanceIndicator.vue';
import LockBanner from '@/components/shared/LockBanner.vue';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';
import AmountDisplay from '@/components/ui/AmountDisplay.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useNotification } from '@/composables/useNotification';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { navigateTo } from '@/router';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const notification = useNotification();
const confirmDialog = useConfirmDialog();
const description = ref('Accrued salary Q4 close');
const debit = ref('2500000.00');
const credit = ref('2500000.00');
const debitNumber = computed(() => Number(debit.value));
const creditNumber = computed(() => Number(credit.value));

const isBalanced = computed(() => {
  const dVal = parseFloat(debit.value) || 0;
  const cVal = parseFloat(credit.value) || 0;
  return Math.abs(dVal - cVal) < 0.005;
});

async function post(): Promise<void> {
  if (!isBalanced.value) {
    notification.error('Debit and credit must be balanced before posting.');
    return;
  }
  const confirmed = await confirmDialog.confirm({
    title: 'Post Journal',
    message: 'Posted journals become immutable. Continue only after debit and credit are balanced.',
    tone: 'danger',
    confirmLabel: 'Post Journal',
  });
  if (!confirmed) return;
  notification.success('Journal posted.');
  navigateTo('/journal-entries');
}

onMounted(() => ui.setBreadcrumbs(['Accounting', 'Journal Entries', 'New Journal']));
</script>

<template>
  <PageHeader title="Create New Journal Entry" subtitle="Validated double-entry form with live balance feedback.">
    <template #actions>
      <AppButton @click="navigateTo('/journal-entries')">Cancel</AppButton>
      <AppButton variant="primary" :disabled="!isBalanced" @click="post">Post Journal</AppButton>
    </template>
  </PageHeader>
  <LockBanner />
  <section class="journal-grid">
    <SectionPanel title="Journal Information">
      <AppInput v-model="description" label="Description" required />
      <AppInput v-model="debit" label="Debit" amount required />
      <AppInput v-model="credit" label="Credit" amount required />
    </SectionPanel>
    <SectionPanel title="Workflow">
      <p>Prepared by Rina Sari · Pending reviewer sign-off.</p>
      <BalanceIndicator :debit="debitNumber" :credit="creditNumber" />
    </SectionPanel>
  </section>
  <SectionPanel title="Journal Lines">
    <div class="line-row"><span>5110 Salary Expense</span><AmountDisplay :value="debit" kind="debit" /><AmountDisplay value="0.00" kind="credit" /></div>
    <div class="line-row"><span>2120 Accrued Liabilities</span><AmountDisplay value="0.00" kind="debit" /><AmountDisplay :value="credit" kind="credit" /></div>
  </SectionPanel>
</template>

<style scoped>
.journal-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

p {
  color: var(--text-secondary);
}

.line-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 140px 140px;
  gap: 16px;
  border-bottom: 1px solid var(--border);
  padding: 12px 0;
}

@media (max-width: 900px) {
  .journal-grid {
    grid-template-columns: 1fr;
  }
}
</style>
