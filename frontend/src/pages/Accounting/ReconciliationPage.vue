<script setup lang="ts">
import { Plus } from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { accountingApi } = useLedgerScopeApi();
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppSelect from "@/components/ui/AppSelect.vue";
import EmptyState from "@/components/ui/EmptyState.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { useNotification } from "@/composables/useNotification";
import { useCompanyStore } from "@/stores/company.store";
import { useUiStore } from "@/stores/ui.store";
import type { AccountingPeriod, Account, Reconciliation } from "@/types";
import { formatDecimal } from "@/utils/decimal";
import { reconciliationSchema } from "@/schemas/reconciliation.schema";

const ui = useUiStore();
const companies = useCompanyStore();
const notification = useNotification();

const accounts = ref<Account[]>([]);
const periods = ref<AccountingPeriod[]>([]);
const reconciliations = ref<Reconciliation[]>([]);
const selectedAccountId = ref<string | number>("");
const selectedPeriodId = ref<string | number>("");
const reconciliationType = ref<"bank" | "ar" | "ap">("bank");
const bookBalance = ref("0.00");
const bankBalance = ref("0.00");
const showForm = ref(false);
const isLoading = ref(false);
const isSaving = ref(false);
const error = ref<string | null>(null);

const accountOptions = computed(() =>
  accounts.value.map((account) => ({
    label: `${account.account_code ?? account.code} · ${account.account_name ?? account.name}`,
    value: account.id,
  })),
);
const periodOptions = computed(() =>
  periods.value.map((period) => ({
    label: period.period_name,
    value: period.id,
  })),
);
const activeCompanyId = computed(() => companies.activeCompanyId);

async function load(): Promise<void> {
  const companyId = activeCompanyId.value;
  if (!companyId) {
    error.value = "Select a company before opening reconciliation.";
    return;
  }
  isLoading.value = true;
  error.value = null;
  try {
    const [loadedAccounts, loadedFiscalYears, loadedReconciliations] =
      await Promise.all([
        accountingApi.accounts(companyId),
        accountingApi.fiscalYears(companyId),
        accountingApi.reconciliations(companyId),
      ]);
    accounts.value = loadedAccounts;
    reconciliations.value = loadedReconciliations;
    const fiscalYear = loadedFiscalYears[0];
    if (fiscalYear) {
      periods.value = await accountingApi.periods(companyId, fiscalYear.id);
    }
    if (accounts.value[0]) selectedAccountId.value = accounts.value[0].id;
    if (periods.value[0]) selectedPeriodId.value = periods.value[0].id;
  } catch (caught) {
    error.value =
      caught instanceof Error
        ? caught.message
        : "Unable to load reconciliation data.";
  } finally {
    isLoading.value = false;
  }
}

async function createReconciliation(): Promise<void> {
  if (!activeCompanyId.value) {
    notification.error("Select a company before creating a reconciliation.");
    return;
  }
  const parsed = reconciliationSchema.safeParse({
    account_id: Number(selectedAccountId.value),
    accounting_period_id: Number(selectedPeriodId.value),
    reconciliation_type: reconciliationType.value,
    book_balance: bookBalance.value,
    bank_balance: bankBalance.value,
  });
  if (!parsed.success) {
    notification.error(
      parsed.error.issues[0]?.message ?? "Complete the reconciliation form.",
    );
    return;
  }
  isSaving.value = true;
  try {
    const reconciliation = await accountingApi.createReconciliation(
      activeCompanyId.value,
      parsed.data,
    );
    reconciliations.value = [reconciliation, ...reconciliations.value];
    showForm.value = false;
    notification.success("Reconciliation created.");
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to create reconciliation.",
    );
  } finally {
    isSaving.value = false;
  }
}

async function transition(
  reconciliation: Reconciliation,
  action: "approve" | "lock",
): Promise<void> {
  if (!activeCompanyId.value) return;
  try {
    const updated =
      action === "approve"
        ? await accountingApi.approveReconciliation(
            activeCompanyId.value,
            reconciliation.id,
          )
        : await accountingApi.lockReconciliation(
            activeCompanyId.value,
            reconciliation.id,
          );
    reconciliations.value = reconciliations.value.map((item) =>
      item.id === updated.id ? updated : item,
    );
    notification.success(`Reconciliation ${action}d.`);
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to update reconciliation.",
    );
  }
}

onMounted(async () => {
  ui.setBreadcrumbs(["Accounting", "Reconciliation"]);
  if (!companies.activeCompanyId) {
    await companies.fetchCompanies();
  }
  await load();
});
</script>

<template>
  <PageHeader
    title="Reconciliation"
    subtitle="Book-to-bank balance comparison with approval and locking workflow."
  >
    <template #actions>
      <AppButton variant="primary" :icon="Plus" @click="showForm = !showForm">
        {{ showForm ? "Close" : "New reconciliation" }}
      </AppButton>
    </template>
  </PageHeader>

  <SectionPanel v-if="error" title="Reconciliation unavailable">
    <p class="state-message state-message--error">{{ error }}</p>
  </SectionPanel>

  <SectionPanel v-if="showForm && activeCompanyId" title="New reconciliation">
    <div class="form-grid">
      <AppSelect
        v-model="selectedAccountId"
        label="Account"
        :options="accountOptions"
      />
      <AppSelect
        v-model="selectedPeriodId"
        label="Accounting period"
        :options="periodOptions"
      />
      <AppSelect
        v-model="reconciliationType"
        label="Type"
        :options="[
          { label: 'Bank', value: 'bank' },
          { label: 'Accounts receivable', value: 'ar' },
          { label: 'Accounts payable', value: 'ap' },
        ]"
      />
      <AppInput v-model="bookBalance" label="Book balance" amount required />
      <AppInput v-model="bankBalance" label="Bank balance" amount required />
      <AppButton
        variant="primary"
        :loading="isSaving"
        @click="createReconciliation"
      >
        Create reconciliation
      </AppButton>
    </div>
  </SectionPanel>

  <SectionPanel title="Reconciliation register">
    <EmptyState
      v-if="!isLoading && reconciliations.length === 0"
      title="No reconciliations"
      body="Create a reconciliation to compare book and external balances."
    />
    <p v-if="isLoading" class="state-message">Loading reconciliations…</p>
    <ul v-else-if="reconciliations.length > 0" class="reconciliation-list">
      <li v-for="reconciliation in reconciliations" :key="reconciliation.id">
        <div>
          <strong
            >{{ reconciliation.reconciliation_type.toUpperCase() }} · #{{
              reconciliation.id
            }}</strong
          >
          <p>
            Book {{ formatDecimal(reconciliation.book_balance) }} · External
            {{ formatDecimal(reconciliation.bank_balance) }} · Difference
            {{ formatDecimal(reconciliation.difference) }}
          </p>
        </div>
        <div class="row-actions">
          <StatusBadge :status="reconciliation.status" />
          <AppButton
            v-if="reconciliation.status === 'draft'"
            size="sm"
            @click="transition(reconciliation, 'approve')"
          >
            Approve
          </AppButton>
          <AppButton
            v-if="reconciliation.status === 'approved'"
            size="sm"
            variant="locked"
            @click="transition(reconciliation, 'lock')"
          >
            Lock
          </AppButton>
        </div>
      </li>
    </ul>
  </SectionPanel>
</template>

<style scoped>
.form-grid {
  display: grid;
  gap: 14px;
}

.reconciliation-list {
  display: grid;
  gap: 8px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.reconciliation-list li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  border-top: 1px solid var(--border);
  padding: 14px 0;
}

.reconciliation-list strong,
.reconciliation-list p {
  display: block;
}

.reconciliation-list p,
.state-message {
  margin: 4px 0 0;
  color: var(--text-secondary);
}

.state-message--error {
  color: var(--status-danger);
}

.row-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

@media (max-width: 720px) {
  .reconciliation-list li {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
