<script setup lang="ts">
import { CheckCircle2, Lock } from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { accountingApi } = useLedgerScopeApi();
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AmountDisplay from "@/components/ui/AmountDisplay.vue";
import EmptyState from "@/components/ui/EmptyState.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import { useNotification } from "@/composables/useNotification";
import { useCompanyStore } from "@/stores/company.store";
import { useUiStore } from "@/stores/ui.store";
import type {
  AccountingPeriod,
  FinancialStatement,
  FiscalYear,
  StatementGroup,
  StatementType,
} from "@/types";

const ui = useUiStore();
const companyStore = useCompanyStore();
const confirmDialog = useConfirmDialog();
const notification = useNotification();

const fiscalYears = ref<FiscalYear[]>([]);
const periods = ref<AccountingPeriod[]>([]);
const statements = ref<FinancialStatement[]>([]);
const selectedPeriodId = ref<number | null>(null);
const selectedType = ref<StatementType>("income_statement");
const isLoading = ref(true);
const isMutating = ref(false);
const error = ref<string | null>(null);

const selectedStatement = computed(
  () =>
    statements.value.find(
      (statement) =>
        statement.accounting_period_id === selectedPeriodId.value &&
        statement.statement_type === selectedType.value,
    ) ?? null,
);

const statementLines = computed(() => {
  const statement = selectedStatement.value;
  if (!statement) return [];

  return Object.entries(statement.data).flatMap(([group, value]) => {
    if (!value || typeof value !== "object" || !("lines" in value)) {
      return [];
    }

    const statementGroup = value as StatementGroup;
    return statementGroup.lines.map((line) => ({
      group: group.replaceAll("_", " "),
      account_name: line.account_name,
      amount: line.amount,
    }));
  });
});

async function load(): Promise<void> {
  if (!companyStore.activeCompanyId) await companyStore.fetchCompanies();

  const companyId = companyStore.activeCompanyId;
  if (!companyId) {
    error.value = "No company is available for this workspace.";
    isLoading.value = false;
    return;
  }

  isLoading.value = true;
  error.value = null;
  try {
    const [loadedYears, loadedStatements] = await Promise.all([
      accountingApi.fiscalYears(companyId),
      accountingApi.getFinancialStatements(companyId),
    ]);
    fiscalYears.value = loadedYears;
    statements.value = loadedStatements;
    const year = loadedYears[0];
    if (year) periods.value = await accountingApi.periods(companyId, year.id);
    selectedPeriodId.value =
      periods.value.find((period) =>
        loadedStatements.some(
          (statement) => statement.accounting_period_id === period.id,
        ),
      )?.id ??
      periods.value[0]?.id ??
      null;
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Unable to load statements.";
  } finally {
    isLoading.value = false;
  }
}

async function approve(): Promise<void> {
  const statement = selectedStatement.value;
  const companyId = companyStore.activeCompanyId;
  if (!statement || !companyId) return;
  if (
    !(await confirmDialog.confirm({
      title: "Approve Statement",
      message: "Approve this financial statement version for reporting?",
      tone: "primary",
      confirmLabel: "Approve",
    }))
  ) {
    return;
  }

  isMutating.value = true;
  try {
    const updated = await accountingApi.approveFinancialStatement(
      companyId,
      statement.id,
    );
    statements.value = statements.value.map((item) =>
      item.id === updated.id ? updated : item,
    );
    notification.success("Financial statement approved.");
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Statement approval failed.",
    );
  } finally {
    isMutating.value = false;
  }
}

async function lock(): Promise<void> {
  const statement = selectedStatement.value;
  const companyId = companyStore.activeCompanyId;
  if (!statement || !companyId) return;
  if (
    !(await confirmDialog.confirm({
      title: "Lock Statement",
      message: "A locked statement cannot be changed without a new version.",
      tone: "danger",
      confirmLabel: "Lock",
    }))
  ) {
    return;
  }

  isMutating.value = true;
  try {
    const updated = await accountingApi.lockFinancialStatement(
      companyId,
      statement.id,
    );
    statements.value = statements.value.map((item) =>
      item.id === updated.id ? updated : item,
    );
    notification.success("Financial statement locked.");
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Statement lock failed.",
    );
  } finally {
    isMutating.value = false;
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Financial", "Statements"]);
  void load();
});

watch(
  () => companyStore.activeCompanyId,
  () => void load(),
);
</script>

<template>
  <PageHeader
    title="Financial Statements"
    subtitle="Statement package with approval and lock workflow."
  >
    <template #actions>
      <StatusBadge
        v-if="selectedStatement"
        :status="selectedStatement.status"
      />
      <AppButton
        v-if="selectedStatement?.status === 'draft'"
        :icon="CheckCircle2"
        :loading="isMutating"
        @click="approve"
        >Approve</AppButton
      >
      <AppButton
        v-if="selectedStatement && !selectedStatement.is_locked"
        variant="locked"
        :icon="Lock"
        :loading="isMutating"
        @click="lock"
        >Lock</AppButton
      >
    </template>
  </PageHeader>

  <SectionPanel title="Statement scope">
    <div class="controls">
      <label>
        Accounting period
        <select v-model="selectedPeriodId">
          <option v-for="period in periods" :key="period.id" :value="period.id">
            {{ period.period_name }}
          </option>
        </select>
      </label>
      <label>
        Statement type
        <select v-model="selectedType">
          <option value="income_statement">Income statement</option>
          <option value="balance_sheet">Balance sheet</option>
          <option value="cash_flow">Cash flow</option>
          <option value="equity_changes">Equity changes</option>
        </select>
      </label>
    </div>
  </SectionPanel>

  <div v-if="isLoading" class="state">Loading statements...</div>
  <SectionPanel v-else-if="error" title="Statements unavailable">
    <p class="state state--error">{{ error }}</p>
  </SectionPanel>
  <EmptyState
    v-else-if="!selectedStatement"
    title="No statement generated"
    body="Generate a statement from Statement Builder for the selected period."
  />
  <SectionPanel
    v-else
    :title="selectedType.replaceAll('_', ' ')"
    :subtitle="`Status: ${selectedStatement.status}`"
  >
    <div v-if="statementLines.length === 0" class="state">
      Statement has no posted ledger lines yet.
    </div>
    <div v-else class="statement-list">
      <div
        v-for="line in statementLines"
        :key="`${line.group}-${line.account_name}`"
        class="statement-row"
      >
        <span
          ><small>{{ line.group }}</small
          >{{ line.account_name }}</span
        >
        <AmountDisplay :value="line.amount" currency />
      </div>
    </div>
  </SectionPanel>
</template>

<style scoped>
.controls {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

label {
  display: grid;
  gap: 6px;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

select {
  min-height: 40px;
  border: 1px solid var(--border);
  border-radius: 6px;
  background: var(--surface);
  color: var(--text-primary);
  padding: 0 10px;
}

.state {
  padding: 32px;
  text-align: center;
  color: var(--text-secondary);
}

.state--error {
  color: var(--status-danger);
}

.statement-list {
  display: grid;
  gap: 8px;
}

.statement-row {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  border-bottom: 1px solid var(--border);
  padding: 12px 0;
}

.statement-row span {
  display: grid;
  gap: 4px;
}

.statement-row small {
  color: var(--text-muted);
  text-transform: capitalize;
}

@media (max-width: 720px) {
  .controls {
    grid-template-columns: 1fr;
  }
}
</style>
