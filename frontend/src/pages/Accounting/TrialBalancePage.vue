<script setup lang="ts">
import { Download, RefreshCw } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';

import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useAccountingStore } from '@/stores/accounting.store';
import { useCompanyStore } from '@/stores/company.store';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const accounting = useAccountingStore();
const companies = useCompanyStore();
const rows = computed(() => accounting.trialBalance);

onMounted(() => {
  ui.setBreadcrumbs(['Accounting', 'Trial Balance']);
  void accounting.fetchTrialBalance(companies.activeCompany?.id ?? 1);
});
</script>

<template>
  <PageHeader title="Q1 2026 Trial Balance" subtitle="Full-width balanced ledger report.">
    <template #actions>
      <StatusBadge status="Balanced" />
      <AppButton :icon="RefreshCw">Generate</AppButton>
      <AppButton variant="primary" :icon="Download">Export</AppButton>
    </template>
  </PageHeader>
  <AppTable
    :loading="accounting.isLoading"
    :columns="[
      { key: 'account_code', label: 'Code' },
      { key: 'account_name', label: 'Account' },
      { key: 'opening_debit', label: 'Opening DR', align: 'right', isAmount: true },
      { key: 'movement_debit', label: 'Movement DR', align: 'right', isAmount: true },
      { key: 'movement_credit', label: 'Movement CR', align: 'right', isAmount: true },
      { key: 'ending_debit', label: 'Ending DR', align: 'right', isAmount: true },
      { key: 'ending_credit', label: 'Ending CR', align: 'right', isAmount: true },
    ]"
    :data="rows"
  />
</template>
