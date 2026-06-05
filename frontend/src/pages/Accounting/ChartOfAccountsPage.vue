<script setup lang="ts">
import { Upload } from 'lucide-vue-next';
import { onMounted } from 'vue';

import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useAccountingStore } from '@/stores/accounting.store';
import { useCompanyStore } from '@/stores/company.store';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const accounting = useAccountingStore();
const companies = useCompanyStore();

onMounted(() => {
  ui.setBreadcrumbs(['Accounting', 'Chart of Accounts']);
  void accounting.fetchAccounts(companies.activeCompany?.id ?? 1);
});
</script>

<template>
  <PageHeader title="Chart of Accounts" subtitle="Mapped, controlled, and audit-ready account structure.">
    <template #actions>
      <AppButton :icon="Upload">Import Accounts</AppButton>
      <AppButton variant="primary">New Account</AppButton>
    </template>
  </PageHeader>
  <SectionPanel title="Audit Integrity Status" subtitle="3 auditors signed off on COA v2.4" />
  <AppTable
    :loading="accounting.isLoading"
    :columns="[
      { key: 'code', label: 'Code' },
      { key: 'name', label: 'Account Name' },
      { key: 'type', label: 'Type' },
      { key: 'statement', label: 'Financial Statement' },
      { key: 'balance', label: 'Balance', align: 'right', isAmount: true },
      { key: 'status', label: 'Status', isStatus: true },
    ]"
    :data="accounting.accounts"
  />
</template>
