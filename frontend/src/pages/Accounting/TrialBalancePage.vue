<script setup lang="ts">
import { RefreshCw } from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { accountingApi } = useLedgerScopeApi();
import AppButton from "@/components/ui/AppButton.vue";
import AppSelect from "@/components/ui/AppSelect.vue";
import AppTable from "@/components/ui/AppTable.vue";
import EmptyState from "@/components/ui/EmptyState.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { useCompanyStore } from "@/stores/company.store";
import { useUiStore } from "@/stores/ui.store";
import type { AccountingPeriod, FiscalYear } from "@/types";

const ui = useUiStore();
const companies = useCompanyStore();
const periods = ref<AccountingPeriod[]>([]);
const selectedPeriodId = ref<number | null>(null);
const rows = ref<Awaited<ReturnType<typeof accountingApi.trialBalance>>>([]);
const isLoading = ref(true);
const isGenerating = ref(false);
const error = ref<string | null>(null);

const periodOptions = computed(() =>
  periods.value.map((period) => ({
    label: period.period_name,
    value: period.id,
  })),
);

async function load(): Promise<void> {
  if (!companies.activeCompanyId) await companies.fetchCompanies();
  const companyId = companies.activeCompanyId;
  if (!companyId) {
    error.value = "No company is available for this workspace.";
    isLoading.value = false;
    return;
  }

  isLoading.value = true;
  error.value = null;
  try {
    const fiscalYears: FiscalYear[] =
      await accountingApi.fiscalYears(companyId);
    const year = fiscalYears[0];
    if (year) periods.value = await accountingApi.periods(companyId, year.id);
    selectedPeriodId.value ??= periods.value[0]?.id ?? null;
    rows.value = await accountingApi.trialBalance(companyId);
  } catch (caught) {
    error.value =
      caught instanceof Error
        ? caught.message
        : "Unable to load trial balance.";
  } finally {
    isLoading.value = false;
  }
}

async function generate(): Promise<void> {
  const companyId = companies.activeCompanyId;
  if (!companyId || !selectedPeriodId.value) {
    error.value = "Select an accounting period before generating.";
    return;
  }

  isGenerating.value = true;
  error.value = null;
  try {
    await accountingApi.generateTrialBalance(companyId, selectedPeriodId.value);
    rows.value = await accountingApi.trialBalance(companyId);
  } catch (caught) {
    error.value =
      caught instanceof Error
        ? caught.message
        : "Trial balance generation failed.";
  } finally {
    isGenerating.value = false;
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Accounting", "Trial Balance"]);
  void load();
});
</script>

<template>
  <PageHeader
    title="Trial Balance"
    subtitle="Generated from posted journals for the selected accounting period."
  >
    <template #actions>
      <StatusBadge v-if="rows.length > 0" status="Loaded" />
      <AppButton :icon="RefreshCw" :loading="isGenerating" @click="generate"
        >Generate</AppButton
      >
    </template>
  </PageHeader>

  <div class="toolbar">
    <AppSelect
      v-model="selectedPeriodId"
      label="Accounting period"
      :options="periodOptions"
    />
  </div>

  <div v-if="isLoading" class="state">Loading trial balance...</div>
  <div v-else-if="error" class="state state--error">{{ error }}</div>
  <EmptyState
    v-else-if="rows.length === 0"
    title="No trial balance generated"
    body="Generate the trial balance after posting journals for this period."
  />
  <AppTable
    v-else
    :loading="isLoading"
    :columns="[
      { key: 'account_code', label: 'Code' },
      { key: 'account_name', label: 'Account' },
      {
        key: 'opening_debit',
        label: 'Opening DR',
        align: 'right',
        isAmount: true,
      },
      {
        key: 'movement_debit',
        label: 'Movement DR',
        align: 'right',
        isAmount: true,
      },
      {
        key: 'movement_credit',
        label: 'Movement CR',
        align: 'right',
        isAmount: true,
      },
      {
        key: 'ending_debit',
        label: 'Ending DR',
        align: 'right',
        isAmount: true,
      },
      {
        key: 'ending_credit',
        label: 'Ending CR',
        align: 'right',
        isAmount: true,
      },
    ]"
    :data="rows"
  />
</template>

<style scoped>
.toolbar {
  max-width: 320px;
  margin-bottom: 20px;
}

.state {
  padding: 32px;
  text-align: center;
  color: var(--text-secondary);
}

.state--error {
  color: var(--status-danger);
}
</style>
