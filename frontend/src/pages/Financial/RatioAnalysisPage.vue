<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";

import SparklinePanel from "@/components/charts/SparklinePanel.vue";
import RiskHeatmap from "@/components/audit/RiskHeatmap.vue";
import MetricCard from "@/components/shared/MetricCard.vue";
import SectionPanel from "@/components/shared/SectionPanel.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import AmountDisplay from "@/components/ui/AmountDisplay.vue";
import { useUiStore } from "@/stores/ui.store";
import { useCompanyStore } from "@/stores/company.store";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { accountingApi } = useLedgerScopeApi();
import { getApiError } from "@/api/client";
import type { FinancialAnalysisRatios, FinancialTrends } from "@/types";
import { decimalChartHeights } from "@/utils/decimal";

const ui = useUiStore();
const companyStore = useCompanyStore();

const ratioData = ref<FinancialAnalysisRatios | null>(null);
const trendData = ref<FinancialTrends | null>(null);
const isLoading = ref(false);
const loadError = ref<string | null>(null);

const ratios = computed(() => {
  if (!ratioData.value) {
    return [
      {
        label: "Current Ratio",
        value: "N/A",
        trend: "Unavailable",
        tone: "info" as const,
      },
      {
        label: "Net Profit Margin",
        value: "N/A",
        trend: "Unavailable",
        tone: "info" as const,
      },
      {
        label: "Debt to Equity",
        value: "N/A",
        trend: "Unavailable",
        tone: "info" as const,
      },
    ];
  }
  return [
    {
      label: "Current Ratio",
      value: ratioData.value.current_ratio || "N/A",
      trend: ratioData.value.quick_ratio
        ? `Quick: ${ratioData.value.quick_ratio}`
        : "Unavailable",
      tone: "success" as const,
    },
    {
      label: "Net Profit Margin",
      value: ratioData.value.net_profit_margin || "N/A",
      trend: ratioData.value.gross_profit_margin
        ? `Gross: ${ratioData.value.gross_profit_margin}`
        : "Unavailable",
      tone: "success" as const,
    },
    {
      label: "Debt to Equity",
      value: ratioData.value.debt_to_equity || "N/A",
      trend: "Unavailable",
      tone: "info" as const,
    },
  ];
});

const trendValues = computed(() => {
  if (
    !trendData.value ||
    !trendData.value.net_incomes ||
    !trendData.value.net_incomes.length
  ) {
    return [];
  }
  return decimalChartHeights(trendData.value.net_incomes);
});

async function loadData(): Promise<void> {
  if (!companyStore.activeCompanyId) {
    await companyStore.fetchCompanies();
  }

  const companyId = companyStore.activeCompanyId;
  if (!companyId) {
    loadError.value = "No company is available for this workspace.";
    return;
  }

  isLoading.value = true;
  loadError.value = null;
  try {
    const [ratiosRes, trendsRes] = await Promise.all([
      accountingApi.getRatios(companyId),
      accountingApi.getTrends(companyId),
    ]);
    ratioData.value = ratiosRes;
    trendData.value = trendsRes;
  } catch (error: unknown) {
    ratioData.value = null;
    trendData.value = null;
    loadError.value = getApiError(error).message;
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Financial", "Ratio Analysis"]);
  void loadData();
});

watch(
  () => companyStore.activeCompanyId,
  () => {
    void loadData();
  },
);
</script>

<template>
  <PageHeader
    title="Ratio Analysis"
    subtitle="Financial analysis dashboard with risk-aware ratio trends."
  />

  <div v-if="isLoading" class="loading-state">
    Loading ratio analysis data...
  </div>

  <div v-else-if="loadError" class="empty-trend-state">
    <p>Ratio analysis is unavailable.</p>
    <span>{{ loadError }}</span>
  </div>

  <template v-else>
    <section class="ratio-grid">
      <MetricCard v-for="ratio in ratios" :key="ratio.label" v-bind="ratio" />
    </section>

    <section class="analysis-grid">
      <SectionPanel title="Revenue & Expense Trend">
        <div
          v-if="
            !trendData ||
            !trendData.net_incomes ||
            !trendData.net_incomes.length
          "
          class="empty-trend-state"
        >
          <p>No historical trial balance data available to generate trends.</p>
          <span
            >Please import trial balances for multiple periods to view
            trends.</span
          >
        </div>
        <template v-else>
          <SparklinePanel title="Net Income Trend" :values="trendValues" />
          <div class="trend-legend">
            <div
              v-for="(label, i) in trendData.labels"
              :key="label"
              class="legend-item"
            >
              <span>{{ label }}</span>
              <strong
                ><AmountDisplay
                  :value="trendData.net_incomes[i] ?? '0.00'"
                  currency
              /></strong>
            </div>
          </div>
        </template>
      </SectionPanel>
      <SectionPanel title="Solvency Risk Assessment">
        <RiskHeatmap />
      </SectionPanel>
    </section>
  </template>
</template>

<style scoped>
.ratio-grid,
.analysis-grid {
  display: grid;
  gap: 20px;
}

.ratio-grid {
  grid-template-columns: repeat(3, 1fr);
  margin-bottom: 20px;
}

.analysis-grid {
  grid-template-columns: 1.2fr 0.8fr;
}

.loading-state {
  padding: 40px;
  text-align: center;
  color: var(--text-secondary);
}

.empty-trend-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  text-align: center;
  background-color: var(--surface-alt);
  border: 1px dashed var(--border);
  border-radius: 8px;
  color: var(--text-secondary);
}

.empty-trend-state p {
  margin: 0 0 8px 0;
  font-weight: 600;
  color: var(--text-primary);
}

.empty-trend-state span {
  font-size: 0.85rem;
}

.trend-legend {
  display: flex;
  justify-content: space-around;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid var(--border);
}

.legend-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  font-size: 0.85rem;
}

.legend-item span {
  color: var(--text-secondary);
  margin-bottom: 4px;
}

@media (max-width: 900px) {
  .ratio-grid,
  .analysis-grid {
    grid-template-columns: 1fr;
  }
}
</style>
