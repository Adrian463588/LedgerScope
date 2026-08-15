<script setup lang="ts">
import { Plus, Upload } from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";

import AppButton from "@/components/ui/AppButton.vue";
import AppTable from "@/components/ui/AppTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import { navigateTo } from "@/router";
import { useAccountingStore } from "@/stores/accounting.store";
import { useCompanyStore } from "@/stores/company.store";
import { useNotification } from "@/composables/useNotification";
import { useUiStore } from "@/stores/ui.store";
import { addDecimals } from "@/utils/decimal";

const ui = useUiStore();
const accounting = useAccountingStore();
const companies = useCompanyStore();
const notification = useNotification();
const importInput = ref<HTMLInputElement | null>(null);
const isImporting = ref(false);

const rows = computed(() =>
  accounting.journalEntries.map((journal) => ({
    journal_number: journal.journal_number,
    date: journal.date,
    description: journal.description,
    debit: journal.lines.reduce(
      (total, line) => addDecimals([total, line.debit]),
      "0.00",
    ),
    status: journal.status,
  })),
);

async function loadJournals(): Promise<void> {
  if (!companies.activeCompanyId) {
    await companies.fetchCompanies();
  }

  const companyId = companies.activeCompanyId;
  if (!companyId) return;

  await accounting.fetchJournals(companyId);
}

async function handleImport(event: Event): Promise<void> {
  const file = (event.target as HTMLInputElement).files?.[0];
  const companyId = companies.activeCompanyId;
  if (!file || !companyId) return;

  isImporting.value = true;
  try {
    await accounting.importJournals(companyId, file);
    notification.success("Journal import queued for validation.");
    await loadJournals();
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Unable to import journals.",
    );
  } finally {
    isImporting.value = false;
    if (importInput.value) importInput.value.value = "";
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Accounting", "Journal Entries"]);
  void loadJournals();
});
</script>

<template>
  <PageHeader
    title="Journal Entries"
    subtitle="Search, review, approve, and post accounting entries."
  >
    <template #actions>
      <input
        ref="importInput"
        type="file"
        accept=".csv,.xlsx,.xls"
        hidden
        @change="handleImport"
      />
      <AppButton
        :icon="Upload"
        :loading="isImporting"
        @click="importInput?.click()"
        >Import</AppButton
      >
      <AppButton
        variant="primary"
        :icon="Plus"
        @click="navigateTo('/journal-entries/new')"
        >New Journal</AppButton
      >
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
