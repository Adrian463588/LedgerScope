<script setup lang="ts">
import { Download, FileBarChart2 } from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { accountingApi, engagementApi, reportingApi } = useLedgerScopeApi();
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppSelect from "@/components/ui/AppSelect.vue";
import EmptyState from "@/components/ui/EmptyState.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { useNotification } from "@/composables/useNotification";
import { useCompanyStore } from "@/stores/company.store";
import { useReportingStore } from "@/stores/reporting.store";
import { useUiStore } from "@/stores/ui.store";
import type {
  AccountingPeriod,
  Engagement,
  FiscalYear,
  ReportFormat,
  ReportItem,
  ReportType,
} from "@/types";

const ui = useUiStore();
const companies = useCompanyStore();
const reporting = useReportingStore();
const notification = useNotification();

const periods = ref<AccountingPeriod[]>([]);
const engagements = ref<Engagement[]>([]);
const selectedPeriodId = ref<string | number>("");
const selectedEngagementId = ref<string | number>("");
const selectedFormat = ref<ReportFormat>("pdf");
const setupError = ref<string | null>(null);

const activeCompanyId = computed(() => companies.activeCompanyId);
const periodOptions = computed(() =>
  periods.value.map((period) => ({
    label: period.period_name,
    value: period.id,
  })),
);
const engagementOptions = computed(() =>
  engagements.value.map((engagement) => ({
    label: engagement.name,
    value: engagement.id,
  })),
);
const canGenerateAccountingReport = computed(() =>
  Boolean(activeCompanyId.value && selectedPeriodId.value),
);

async function load(): Promise<void> {
  if (!companies.activeCompanyId) {
    await companies.fetchCompanies();
  }

  if (!activeCompanyId.value) {
    setupError.value = "Select a company before opening reports.";
    return;
  }
  setupError.value = null;
  try {
    const [loadedReports, loadedFiscalYears, loadedEngagements] =
      await Promise.all([
        reporting.fetchReports(activeCompanyId.value),
        accountingApi.fiscalYears(activeCompanyId.value),
        engagementApi.list(activeCompanyId.value),
      ]);
    void loadedReports;
    engagements.value = loadedEngagements;
    if (loadedEngagements[0])
      selectedEngagementId.value = loadedEngagements[0].id;
    const fiscalYear: FiscalYear | undefined = loadedFiscalYears[0];
    if (fiscalYear) {
      periods.value = await accountingApi.periods(
        activeCompanyId.value,
        fiscalYear.id,
      );
      const openPeriod = periods.value.find((period) => !period.is_locked);
      if (openPeriod) selectedPeriodId.value = openPeriod.id;
    }
  } catch (caught) {
    setupError.value =
      caught instanceof Error ? caught.message : "Unable to load report setup.";
  }
}

async function generate(
  reportType: ReportType,
  title: string,
  parameters: { accounting_period_id?: number; engagement_id?: number },
): Promise<void> {
  if (!activeCompanyId.value) {
    notification.error("Select a company before generating a report.");
    return;
  }
  const generated = await reporting.generateReport(activeCompanyId.value, {
    report_type: reportType,
    title,
    format: selectedFormat.value,
    parameters,
  });
  if (generated) notification.success("Report generation queued.");
  else notification.error(reporting.error ?? "Report generation failed.");
}

async function download(report: ReportItem): Promise<void> {
  if (!activeCompanyId.value) return;
  try {
    const result = await reportingApi.download(
      activeCompanyId.value,
      report.id,
    );
    window.open(result.url, "_blank", "noopener,noreferrer");
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Report is not ready for download.",
    );
  }
}

async function approve(report: ReportItem): Promise<void> {
  if (!activeCompanyId.value) return;
  try {
    const updated = await reporting.approveReport(
      activeCompanyId.value,
      report.id,
    );
    if (updated) notification.success("Report approved.");
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Report approval failed.",
    );
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Reports"]);
  void load();
});
</script>

<template>
  <PageHeader
    title="Reporting Hub"
    subtitle="Generate, track, approve, and download evidence-backed outputs."
  />

  <SectionPanel v-if="setupError" title="Reporting unavailable">
    <p class="state-message state-message--error">{{ setupError }}</p>
  </SectionPanel>
  <section v-else class="report-setup">
    <SectionPanel
      title="Report scope"
      subtitle="Every internal report requires a persisted accounting or engagement scope."
    >
      <div class="scope-grid">
        <AppSelect
          v-model="selectedPeriodId"
          label="Accounting period"
          :options="periodOptions"
        />
        <AppSelect
          v-model="selectedEngagementId"
          label="Engagement"
          :options="engagementOptions"
        />
        <AppSelect
          v-model="selectedFormat"
          label="File format"
          :options="[
            { label: 'PDF', value: 'pdf' },
            { label: 'Excel workbook', value: 'xlsx' },
            { label: 'CSV', value: 'csv' },
          ]"
        />
      </div>
    </SectionPanel>
    <SectionPanel
      title="Financial reports"
      subtitle="Generated from posted ledger data."
    >
      <div class="button-grid">
        <AppButton
          :icon="FileBarChart2"
          :disabled="!canGenerateAccountingReport"
          @click="
            generate('trial_balance', 'Trial balance report', {
              accounting_period_id: Number(selectedPeriodId),
            })
          "
          >Trial balance</AppButton
        >
        <AppButton
          :icon="FileBarChart2"
          :disabled="!canGenerateAccountingReport"
          @click="
            generate('income_statement', 'Income statement report', {
              accounting_period_id: Number(selectedPeriodId),
            })
          "
          >Income statement</AppButton
        >
        <AppButton
          :icon="FileBarChart2"
          :disabled="!canGenerateAccountingReport"
          @click="
            generate('balance_sheet', 'Balance sheet report', {
              accounting_period_id: Number(selectedPeriodId),
            })
          "
          >Balance sheet</AppButton
        >
        <AppButton
          :icon="FileBarChart2"
          :disabled="!canGenerateAccountingReport"
          @click="
            generate('cash_flow', 'Cash flow report', {
              accounting_period_id: Number(selectedPeriodId),
            })
          "
          >Cash flow</AppButton
        >
        <AppButton
          :icon="FileBarChart2"
          :disabled="!canGenerateAccountingReport"
          @click="
            generate('equity_changes', 'Statement of equity changes report', {
              accounting_period_id: Number(selectedPeriodId),
            })
          "
          >Equity changes</AppButton
        >
      </div>
    </SectionPanel>
    <SectionPanel
      title="Audit reports"
      subtitle="Generated from the selected engagement data."
    >
      <AppButton
        :icon="FileBarChart2"
        :disabled="!selectedEngagementId"
        @click="
          generate('audit_report', 'Audit report', {
            engagement_id: Number(selectedEngagementId),
          })
        "
        >Audit report</AppButton
      >
      <AppButton
        :icon="FileBarChart2"
        :disabled="!selectedEngagementId"
        @click="
          generate('engagement_summary', 'Engagement summary', {
            engagement_id: Number(selectedEngagementId),
          })
        "
        >Engagement summary</AppButton
      >
    </SectionPanel>
  </section>

  <SectionPanel title="Generated reports">
    <EmptyState
      v-if="!reporting.isLoading && reporting.reports.length === 0"
      title="No reports generated"
      body="Choose a valid accounting period or engagement to generate a report."
    />
    <p v-if="reporting.error" class="state-message state-message--error">
      {{ reporting.error }}
    </p>
    <ul v-else class="report-list">
      <li v-for="report in reporting.reports" :key="report.id">
        <div>
          <strong>{{ report.name }}</strong>
          <small
            >{{ report.generated_at ?? "Queued" }} ·
            {{ report.format?.toUpperCase() ?? "PDF" }}</small
          >
        </div>
        <div class="report-actions">
          <StatusBadge :status="report.status" />
          <AppButton
            v-if="report.status === 'completed'"
            size="sm"
            @click="approve(report)"
            >Approve</AppButton
          >
          <AppButton
            v-if="report.status === 'completed' || report.status === 'approved'"
            size="sm"
            :icon="Download"
            @click="download(report)"
            >Download</AppButton
          >
        </div>
      </li>
    </ul>
  </SectionPanel>
</template>

<style scoped>
.report-setup {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 20px;
}

.scope-grid,
.button-grid {
  display: grid;
  gap: 12px;
}

.report-list {
  display: grid;
  gap: 8px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.report-list li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  border-top: 1px solid var(--border);
  padding: 12px 0;
}

.report-list strong,
.report-list small {
  display: block;
}

.report-list small,
.state-message {
  margin-top: 4px;
  color: var(--text-secondary);
}

.state-message--error {
  color: var(--status-danger);
}

.report-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

@media (max-width: 1000px) {
  .report-setup {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 720px) {
  .report-list li {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
