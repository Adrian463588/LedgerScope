<script setup lang="ts">
import { computed, onMounted, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { accountingApi } = useLedgerScopeApi();
import BalanceIndicator from "@/components/accounting/BalanceIndicator.vue";
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppSelect from "@/components/ui/AppSelect.vue";
import AmountDisplay from "@/components/ui/AmountDisplay.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import { useNotification } from "@/composables/useNotification";
import { navigateTo } from "@/router";
import { useAuthStore } from "@/stores/auth.store";
import { useCompanyStore } from "@/stores/company.store";
import { useUiStore } from "@/stores/ui.store";
import type { AccountingPeriod, Account } from "@/types";
import { addDecimals, compareDecimals, isZeroDecimal } from "@/utils/decimal";
import { journalSchema } from "@/schemas/journal.schema";

const ui = useUiStore();
const auth = useAuthStore();
const companies = useCompanyStore();
const notification = useNotification();
const confirmDialog = useConfirmDialog();

const description = ref("");
const reference = ref("");
const journalDate = ref(new Date().toISOString().slice(0, 10));
const debit = ref("0.00");
const credit = ref("0.00");
const debitAccountId = ref<string | number>("");
const creditAccountId = ref<string | number>("");
const selectedPeriodId = ref<string | number>("");
const periods = ref<AccountingPeriod[]>([]);
const accounts = ref<Account[]>([]);
const isLoading = ref(false);
const isSaving = ref(false);
const loadError = ref<string | null>(null);

const activeCompanyId = computed(() => companies.activeCompanyId);
const selectedPeriod = computed(() =>
  periods.value.find((period) => period.id === Number(selectedPeriodId.value)),
);
const selectedDebitAccount = computed(() =>
  accounts.value.find((account) => account.id === Number(debitAccountId.value)),
);
const selectedCreditAccount = computed(() =>
  accounts.value.find(
    (account) => account.id === Number(creditAccountId.value),
  ),
);
const accountOptions = computed(() =>
  accounts.value
    .filter((account) => account.is_active !== false)
    .map((account) => ({
      label: `${account.account_code ?? account.code} · ${account.account_name ?? account.name}`,
      value: account.id,
    })),
);
const periodOptions = computed(() =>
  periods.value.map((period) => ({
    label: `${period.period_name} (${period.start_date} – ${period.end_date})`,
    value: period.id,
  })),
);
const totalDebit = computed(() => addDecimals([debit.value]));
const totalCredit = computed(() => addDecimals([credit.value]));
const isBalanced = computed(
  () => compareDecimals(totalDebit.value, totalCredit.value) === 0,
);
const canSave = computed(
  () =>
    Boolean(activeCompanyId.value) &&
    Boolean(selectedPeriodId.value) &&
    Boolean(debitAccountId.value) &&
    Boolean(creditAccountId.value) &&
    Boolean(description.value.trim()) &&
    !isZeroDecimal(debit.value) &&
    !isZeroDecimal(credit.value) &&
    isBalanced.value &&
    !selectedPeriod.value?.is_locked,
);

function accountLabel(account: Account | undefined): string {
  if (!account) return "Account not selected";
  return `${account.account_code ?? account.code} ${account.account_name ?? account.name}`;
}

async function loadReferenceData(): Promise<void> {
  const companyId = activeCompanyId.value;
  if (!companyId) {
    loadError.value = "Select a company before creating a journal.";
    return;
  }

  isLoading.value = true;
  loadError.value = null;
  try {
    const [loadedAccounts, loadedFiscalYears] = await Promise.all([
      accountingApi.accounts(companyId),
      accountingApi.fiscalYears(companyId),
    ]);
    accounts.value = loadedAccounts;
    const fiscalYear = loadedFiscalYears[0];
    if (!fiscalYear) {
      loadError.value = "No fiscal year is configured for this company.";
      return;
    }

    periods.value = await accountingApi.periods(companyId, fiscalYear.id);
    const openPeriod = periods.value.find((period) => !period.is_locked);
    if (openPeriod) selectedPeriodId.value = openPeriod.id;

    const [firstAccount, secondAccount] = accounts.value;
    if (firstAccount) debitAccountId.value = firstAccount.id;
    if (secondAccount) creditAccountId.value = secondAccount.id;
  } catch (caught) {
    loadError.value =
      caught instanceof Error
        ? caught.message
        : "Unable to load journal setup.";
  } finally {
    isLoading.value = false;
  }
}

async function saveDraft(): Promise<void> {
  if (!canSave.value || !activeCompanyId.value) {
    notification.error("Complete the journal details and balance both lines.");
    return;
  }

  const confirmed = await confirmDialog.confirm({
    title: "Create Journal Draft",
    message:
      "This will create a draft journal. It must be submitted and approved before posting.",
    tone: "primary",
    confirmLabel: "Create Draft",
  });
  if (!confirmed) return;

  const parsed = journalSchema.safeParse({
    date: journalDate.value,
    description: description.value,
    lines: [
      {
        account_id: Number(debitAccountId.value),
        debit: debit.value,
        credit: "0.00",
        description: null,
      },
      {
        account_id: Number(creditAccountId.value),
        debit: "0.00",
        credit: credit.value,
        description: null,
      },
    ],
  });
  if (!parsed.success) {
    notification.error(
      parsed.error.issues[0]?.message ?? "Complete a balanced journal.",
    );
    return;
  }

  isSaving.value = true;
  try {
    await accountingApi.createJournal(activeCompanyId.value, {
      accounting_period_id: Number(selectedPeriodId.value),
      description: parsed.data.description.trim(),
      journal_date: parsed.data.date,
      reference: reference.value.trim() || undefined,
      lines: parsed.data.lines.map((line) => ({
        account_id: line.account_id,
        debit: line.debit,
        credit: line.credit,
        description: line.description ?? undefined,
      })),
    });
    notification.success("Journal draft created.");
    navigateTo("/journal-entries");
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to create journal draft.",
    );
  } finally {
    isSaving.value = false;
  }
}

onMounted(async () => {
  ui.setBreadcrumbs(["Accounting", "Journal Entries", "New Journal"]);
  if (!companies.activeCompanyId) {
    await companies.fetchCompanies();
  }
  await loadReferenceData();
});
</script>

<template>
  <PageHeader
    title="Create New Journal Entry"
    subtitle="Create a balanced draft before review and posting."
  >
    <template #actions>
      <AppButton @click="navigateTo('/journal-entries')">Cancel</AppButton>
      <AppButton
        variant="primary"
        :loading="isSaving"
        :disabled="!canSave"
        @click="saveDraft"
        >Create Draft</AppButton
      >
    </template>
  </PageHeader>

  <SectionPanel v-if="isLoading" title="Loading journal setup">
    <p class="state-message">Loading accounts and open accounting periods…</p>
  </SectionPanel>
  <SectionPanel v-else-if="loadError" title="Journal unavailable">
    <p class="state-message state-message--error">{{ loadError }}</p>
  </SectionPanel>

  <template v-else>
    <section v-if="selectedPeriod?.is_locked" class="lock-banner" role="alert">
      The selected accounting period is locked. Choose an open period to create
      a draft.
    </section>
    <section class="journal-grid">
      <SectionPanel title="Journal Information">
        <div class="form-grid">
          <AppInput v-model="description" label="Description" required />
          <AppInput v-model="reference" label="Reference" />
          <AppInput
            v-model="journalDate"
            label="Journal date"
            type="date"
            required
          />
          <AppSelect
            v-model="selectedPeriodId"
            label="Accounting period"
            :options="periodOptions"
          />
        </div>
      </SectionPanel>
      <SectionPanel title="Workflow">
        <p>
          Prepared by {{ auth.user?.name ?? "current user" }} · Draft workflow.
        </p>
        <BalanceIndicator :debit="totalDebit" :credit="totalCredit" />
      </SectionPanel>
    </section>

    <SectionPanel title="Journal Lines">
      <div class="line-row line-row--header">
        <span>Account</span><span>Debit</span><span>Credit</span>
      </div>
      <div class="line-row">
        <AppSelect
          v-model="debitAccountId"
          label="Debit account"
          :options="accountOptions"
        />
        <AppInput v-model="debit" label="Debit" amount required />
        <AmountDisplay value="0.00" kind="credit" />
      </div>
      <div class="line-row">
        <AppSelect
          v-model="creditAccountId"
          label="Credit account"
          :options="accountOptions"
        />
        <AmountDisplay value="0.00" kind="debit" />
        <AppInput v-model="credit" label="Credit" amount required />
      </div>
      <p class="line-summary">
        {{ accountLabel(selectedDebitAccount) }} →
        {{ accountLabel(selectedCreditAccount) }}
      </p>
    </SectionPanel>
  </template>
</template>

<style scoped>
.journal-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-grid {
  display: grid;
  gap: 14px;
}

p {
  color: var(--text-secondary);
}

.state-message {
  margin: 0;
  color: var(--text-secondary);
}

.state-message--error {
  color: var(--status-danger);
}

.lock-banner {
  margin-bottom: 20px;
  border: 1px solid var(--brand-red-border);
  border-radius: 8px;
  background: var(--brand-red-muted);
  color: var(--text-primary);
  padding: 14px 16px;
}

.line-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 140px 140px;
  gap: 16px;
  align-items: end;
  border-bottom: 1px solid var(--border);
  padding: 12px 0;
}

.line-row--header {
  align-items: center;
  border-bottom: 0;
  color: var(--text-secondary);
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.line-summary {
  margin: 16px 0 0;
  font-family: "IBM Plex Mono", monospace;
  font-size: 0.8125rem;
}

@media (max-width: 900px) {
  .journal-grid {
    grid-template-columns: 1fr;
  }

  .line-row {
    grid-template-columns: 1fr;
  }
}
</style>
