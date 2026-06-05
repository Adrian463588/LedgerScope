<script setup lang="ts">
import { Plus, Upload } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';

import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { navigateTo } from '@/router';
import { useAccountingStore } from '@/stores/accounting.store';
import { useCompanyStore } from '@/stores/company.store';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const accounting = useAccountingStore();
const companies = useCompanyStore();

const rows = computed(() =>
  accounting.journalEntries.map((journal) => ({
    journal_number: journal.journal_number,
    date: journal.date,
    description: journal.description,
    debit: journal.lines.reduce((total, line) => total + Number(line.debit), 0).toFixed(2),
    status: journal.status,
  })),
);

onMounted(() => {
  ui.setBreadcrumbs(['Accounting', 'Journal Entries']);
  void accounting.fetchJournals(companies.activeCompany?.id ?? 1);
});
</script>

<template>
  <PageHeader title="Journal Entries" subtitle="Search, review, approve, and post accounting entries.">
    <template #actions>
      <AppButton :icon="Upload">Import</AppButton>
      <AppButton variant="primary" :icon="Plus" @click="navigateTo('/journal-entries/new')">New Journal</AppButton>
    </template>
  </PageHeader>
  <AppTable
    :loading="accounting.isLoading"
    :columns="[
      { key: 'journal_number', label: '#' },
      { key: 'date', label: 'Date' },
      { key: 'description', label: 'Description' },
      { key: 'debit', label: 'Debit', align: 'right', isAmount: true },
      { key: 'status', label: 'Status', isStatus: true },
    ]"
    :data="rows"
  />
</template>
