<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { useCompanyStore } from "@/stores/company.store";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { accountingApi } = useLedgerScopeApi();
import { useUiStore } from "@/stores/ui.store";
import { useNotification } from "@/composables/useNotification";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import { getApiError } from "@/api/client";
import { Download, Lock, CheckCircle2, RefreshCw } from "lucide-vue-next";
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AmountDisplay from "@/components/ui/AmountDisplay.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import type {
  AccountingPeriod,
  FinancialStatement,
  FinancialStatementData,
  FiscalYear,
  StatementGroup,
} from "@/types";

const ui = useUiStore();
const companyStore = useCompanyStore();
const notification = useNotification();
const confirmDialog = useConfirmDialog();

const fiscalYears = ref<FiscalYear[]>([]);
const selectedYearId = ref<number | null>(null);
const periods = ref<AccountingPeriod[]>([]);
const selectedPeriodId = ref<number | null>(null);
const statementType = ref<"income_statement" | "balance_sheet">(
  "income_statement",
);
const statement = ref<FinancialStatement | null>(null);
const comparison = ref<FinancialStatement | null>(null);
const comparePeriodId = ref<number | null>(null);
const isGenerating = ref(false);
const isLoading = ref(false);
const incomeGroups = [
  "revenue",
  "cogs",
  "expenses",
  "other_income",
  "other_expenses",
] as const;
const balanceSheetGroups = ["assets", "liabilities_and_equity"] as const;

const companyId = computed(() => companyStore.activeCompanyId);
const loadError = ref<string | null>(null);

async function loadInitialData(): Promise<void> {
  try {
    if (!companyId.value) {
      await companyStore.fetchCompanies();
    }

    const id = companyId.value;
    if (!id) {
      loadError.value = "No company is available for this workspace.";
      return;
    }

    fiscalYears.value = await accountingApi.fiscalYears(id);
    const firstFiscalYear = fiscalYears.value[0];
    if (firstFiscalYear) selectedYearId.value = firstFiscalYear.id;
  } catch (error: unknown) {
    loadError.value = getApiError(error).message;
    notification.error(getApiError(error).message);
  }
}

async function loadPeriods(): Promise<void> {
  if (!selectedYearId.value || !companyId.value) return;
  try {
    periods.value = await accountingApi.periods(
      companyId.value,
      selectedYearId.value,
    );
    const firstPeriod = periods.value[0];
    if (firstPeriod) selectedPeriodId.value = firstPeriod.id;
  } catch (error: unknown) {
    notification.error(getApiError(error).message);
  }
}

async function fetchStatement(): Promise<void> {
  if (!selectedPeriodId.value || !companyId.value) return;
  isLoading.value = true;
  try {
    const list = await accountingApi.getFinancialStatements(companyId.value);
    const match = list.find(
      (s) =>
        s.accounting_period_id === selectedPeriodId.value &&
        s.statement_type === statementType.value,
    );
    if (match) {
      const res = await accountingApi.getFinancialStatement(
        companyId.value,
        match.id,
        { compare_with: comparePeriodId.value || undefined },
      );
      if ("statement" in res) {
        statement.value = res.statement;
        comparison.value = res.comparison;
      } else {
        statement.value = res;
        comparison.value = null;
      }
    } else {
      statement.value = comparison.value = null;
    }
  } catch (error: unknown) {
    notification.error(getApiError(error).message);
  } finally {
    isLoading.value = false;
  }
}

async function generateStatement(): Promise<void> {
  if (!selectedPeriodId.value || !companyId.value) return;
  isGenerating.value = true;
  try {
    await accountingApi.generateFinancialStatement(companyId.value, {
      accounting_period_id: selectedPeriodId.value,
      statement_type: statementType.value,
    });
    notification.success("Statement generated.");
    await fetchStatement();
  } catch (error: unknown) {
    notification.error(getApiError(error).message || "Generation failed.");
  } finally {
    isGenerating.value = false;
  }
}

async function approveStatement(): Promise<void> {
  if (
    !statement.value ||
    !(await confirmDialog.confirm({
      title: "Approve",
      message: "Approve statement?",
      tone: "primary",
      confirmLabel: "Approve",
    }))
  )
    return;
  try {
    if (!companyId.value) return;

    statement.value = await accountingApi.approveFinancialStatement(
      companyId.value,
      statement.value.id,
    );
    notification.success("Approved.");
  } catch (error: unknown) {
    notification.error(getApiError(error).message);
  }
}

async function lockStatement(): Promise<void> {
  if (
    !statement.value ||
    !(await confirmDialog.confirm({
      title: "Lock",
      message: "Lock statement?",
      tone: "primary",
      confirmLabel: "Lock",
    }))
  )
    return;
  try {
    if (!companyId.value) return;

    statement.value = await accountingApi.lockFinancialStatement(
      companyId.value,
      statement.value.id,
    );
    notification.success("Locked.");
  } catch (error: unknown) {
    notification.error(getApiError(error).message);
  }
}

function exportStatement(format: "pdf" | "xlsx") {
  if (!statement.value || !companyId.value) return;
  window.open(
    accountingApi.getFinancialStatementExportUrl(
      companyId.value,
      statement.value.id,
      format,
    ),
    "_blank",
  );
}

function getCompVal(group: string, acctId: number) {
  return (
    getStatementGroup(comparison.value, group)?.lines.find(
      (line) => line.account_id === acctId,
    )?.amount ?? "0.00"
  );
}

function getStatementGroup(
  statementValue: FinancialStatement | null,
  group: string,
): StatementGroup | undefined {
  if (!statementValue) return undefined;
  const candidate = statementValue.data[group as keyof FinancialStatementData];
  return typeof candidate === "object" &&
    candidate !== null &&
    "lines" in candidate
    ? (candidate as StatementGroup)
    : undefined;
}

onMounted(() => {
  ui.setBreadcrumbs(["Financial", "Statement Builder"]);
  void loadInitialData();
});

watch(selectedYearId, () => void loadPeriods());
watch(
  [selectedPeriodId, statementType, comparePeriodId, companyId],
  () => void fetchStatement(),
);
watch(companyId, () => void loadInitialData());
</script>

<template>
  <PageHeader
    title="Statement Builder"
    subtitle="Build, compare, and export financial statements."
  >
    <template #actions v-if="statement">
      <AppButton
        v-if="statement.status === 'draft'"
        :icon="CheckCircle2"
        @click="approveStatement"
        >Approve</AppButton
      >
      <AppButton v-if="!statement.is_locked" :icon="Lock" @click="lockStatement"
        >Lock</AppButton
      >
      <AppButton
        variant="secondary"
        :icon="Download"
        @click="exportStatement('pdf')"
        >PDF</AppButton
      >
      <AppButton
        variant="secondary"
        :icon="Download"
        @click="exportStatement('xlsx')"
        >Excel</AppButton
      >
    </template>
  </PageHeader>

  <div class="control-panel">
    <div class="fg">
      <label>Fiscal Year</label>
      <select v-model="selectedYearId">
        <option v-for="y in fiscalYears" :key="y.id" :value="y.id">
          {{ y.year }}
        </option>
      </select>
    </div>
    <div class="fg">
      <label>Period</label>
      <select v-model="selectedPeriodId">
        <option v-for="p in periods" :key="p.id" :value="p.id">
          {{ p.period_name }}
        </option>
      </select>
    </div>
    <div class="fg">
      <label>Type</label>
      <select v-model="statementType">
        <option value="income_statement">Profit or Loss</option>
        <option value="balance_sheet">Balance Sheet</option>
      </select>
    </div>
    <div class="fg" v-if="statement">
      <label>Compare With</label>
      <select v-model="comparePeriodId">
        <option :value="null">None</option>
        <option
          v-for="p in periods.filter((x) => x.id !== selectedPeriodId)"
          :key="p.id"
          :value="p.id"
        >
          {{ p.period_name }}
        </option>
      </select>
    </div>
  </div>

  <div v-if="isLoading" class="loading-state">Loading statement data...</div>
  <div v-else-if="loadError" class="empty-state">
    <p>{{ loadError }}</p>
  </div>
  <div v-else-if="!statement" class="empty-state">
    <p>No financial statement built for this period.</p>
    <AppButton
      :icon="RefreshCw"
      :loading="isGenerating"
      @click="generateStatement"
      >Generate Statement</AppButton
    >
  </div>

  <div v-else class="statement-container">
    <SectionPanel
      :title="
        statementType === 'income_statement' ? 'Profit & Loss' : 'Balance Sheet'
      "
      :subtitle="`Status: ${statement.status}`"
    >
      <div class="tbl-hdr">
        <span>Account Group / Name</span>
        <div class="amts">
          <span v-if="comparison" class="col">Compare</span>
          <span class="col">Current</span>
        </div>
      </div>
      <template v-if="statementType === 'income_statement'">
        <div v-for="g in incomeGroups" :key="g">
          <template v-if="getStatementGroup(statement, g)">
            <div class="grp-hdr">{{ g.toUpperCase() }}</div>
            <div
              v-for="l in getStatementGroup(statement, g)?.lines ?? []"
              :key="l.account_id"
              class="row"
            >
              <span class="ind">{{ l.account_name }}</span>
              <div class="amts">
                <span v-if="comparison" class="col txt-mut"
                  ><AmountDisplay :value="getCompVal(g, l.account_id)" currency
                /></span>
                <span class="col"
                  ><AmountDisplay :value="l.amount" currency
                /></span>
              </div>
            </div>
            <div class="row total">
              <span>Total {{ g }}</span>
              <div class="amts">
                <span v-if="comparison" class="col"
                  ><AmountDisplay
                    :value="getStatementGroup(comparison, g)?.total ?? '0.00'"
                    currency
                /></span>
                <span class="col"
                  ><AmountDisplay
                    :value="getStatementGroup(statement, g)?.total ?? '0.00'"
                    currency
                /></span>
              </div>
            </div>
          </template>
        </div>
        <div class="row total net">
          <strong>NET INCOME</strong>
          <div class="amts">
            <span v-if="comparison" class="col"
              ><AmountDisplay
                :value="comparison.data?.net_income || '0.00'"
                currency
            /></span>
            <span class="col"
              ><AmountDisplay
                :value="statement.data?.net_income ?? '0.00'"
                currency
            /></span>
          </div>
        </div>
      </template>
      <template v-else>
        <div v-for="g in balanceSheetGroups" :key="g">
          <template v-if="getStatementGroup(statement, g)">
            <div class="grp-hdr">{{ g.toUpperCase().replace("_", " ") }}</div>
            <div
              v-for="l in getStatementGroup(statement, g)?.lines ?? []"
              :key="l.account_id"
              class="row"
            >
              <span class="ind">{{ l.account_name }}</span>
              <div class="amts">
                <span v-if="comparison" class="col txt-mut"
                  ><AmountDisplay :value="getCompVal(g, l.account_id)" currency
                /></span>
                <span class="col"
                  ><AmountDisplay :value="l.amount" currency
                /></span>
              </div>
            </div>
            <div class="row total">
              <span>Total {{ g.replace("_", " ") }}</span>
              <div class="amts">
                <span v-if="comparison" class="col"
                  ><AmountDisplay
                    :value="getStatementGroup(comparison, g)?.total ?? '0.00'"
                    currency
                /></span>
                <span class="col"
                  ><AmountDisplay
                    :value="getStatementGroup(statement, g)?.total ?? '0.00'"
                    currency
                /></span>
              </div>
            </div>
          </template>
        </div>
      </template>
    </SectionPanel>
  </div>
</template>

<style scoped>
.control-panel {
  display: flex;
  gap: 20px;
  margin-bottom: 25px;
  background: var(--surface);
  padding: 16px;
  border-radius: 8px;
  border: 1px solid var(--border);
}
.fg {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
select {
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid var(--border);
  background: var(--page-bg);
  color: var(--text-primary);
  min-width: 150px;
}
.tbl-hdr {
  display: flex;
  justify-content: space-between;
  border-bottom: 2px solid var(--border);
  padding-bottom: 10px;
  font-weight: bold;
}
.grp-hdr {
  font-weight: bold;
  background: var(--border);
  padding: 8px;
  margin-top: 15px;
  border-radius: 4px;
}
.row {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  padding: 12px 0;
}
.ind {
  padding-left: 20px;
}
.amts {
  display: flex;
  gap: 40px;
}
.col {
  width: 120px;
  text-align: right;
}
.total {
  font-weight: bold;
  border-bottom: 2px solid var(--text-primary);
}
.net {
  background: var(--border);
  padding: 12px;
  margin-top: 20px;
}
.loading-state,
.empty-state {
  padding: 40px;
  text-align: center;
}
.txt-mut {
  color: var(--text-secondary);
}
</style>
