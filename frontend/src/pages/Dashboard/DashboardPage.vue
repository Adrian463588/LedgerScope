<script setup lang="ts">
import { Download, RefreshCw, ArrowRight } from "lucide-vue-next";
import { computed, onMounted, watch } from "vue";

import SparklinePanel from "@/components/charts/SparklinePanel.vue";
import PeriodSelector from "@/components/shared/PeriodSelector.vue";
import ProgressTracker from "@/components/shared/ProgressTracker.vue";
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { useCompanyStore } from "@/stores/company.store";
import { useDashboardStore } from "@/stores/dashboard.store";
import { usePeriodStore } from "@/stores/period.store";
import { useUiStore } from "@/stores/ui.store";
import { navigateTo } from "@/router";

const ui = useUiStore();
const companyStore = useCompanyStore();
const dashboardStore = useDashboardStore();
const periodStore = usePeriodStore();

const dashboardData = computed(() => dashboardStore.data);
const isLoading = computed(() => dashboardStore.isLoading);
const error = computed(() => dashboardStore.error);

const selectedPeriodId = computed({
  get: () => periodStore.selectedPeriodId,
  set: (periodId: number | null) => periodStore.selectPeriod(periodId),
});

async function loadDashboardData(): Promise<void> {
  try {
    await companyStore.fetchCompanies();
    const companyId = companyStore.activeCompanyId;
    if (companyId === null) {
      dashboardStore.reset();
      return;
    }

    await dashboardStore.fetchDashboard(
      companyId,
      selectedPeriodId.value ?? undefined,
    );
  } catch {
    // The store owns the typed API error state; the page only renders it.
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Dashboard"]);
  loadDashboardData();
});

watch(selectedPeriodId, (periodId, previousPeriodId) => {
  if (periodId !== null && periodId !== previousPeriodId) {
    void loadDashboardData();
  }
});

// All derived data MUST be computed() so they remain reactive after the API resolves.
const kpis = computed(() => dashboardData.value?.kpis ?? []);

const quarterlySnapshot = computed(
  () => dashboardData.value?.quarterlySnapshot ?? [],
);

const recentActivities = computed(
  () => dashboardData.value?.recentActivities ?? [],
);

const quickAccess = computed(() => dashboardData.value?.quickAccess ?? []);

const quickAccessRoutes: Record<string, string> = {
  "Journal Entries": "/journal-entries",
  "Trial Balance": "/trial-balance",
  "Working Papers": "/working-paper",
  Reports: "/reports",
};

function openQuickAccess(label: string): void {
  const path = quickAccessRoutes[label];
  if (path !== undefined) navigateTo(path);
}

const quarterlyProgress = computed(() => dashboardData.value?.progress ?? []);
</script>

<template>
  <div class="max-w-[1440px] mx-auto w-full p-6 md:p-8 flex flex-col gap-8">
    <PageHeader
      title="Dashboard"
      subtitle="High-confidence accounting and audit control center."
    >
      <template #actions>
        <PeriodSelector v-model="selectedPeriodId" />
        <AppButton
          :icon="RefreshCw"
          :loading="isLoading"
          @click="loadDashboardData"
          >Refresh</AppButton
        >
        <AppButton
          variant="primary"
          :icon="Download"
          @click="navigateTo('/reports')"
          >Export Report</AppButton
        >
      </template>
    </PageHeader>

    <!-- Error banner -->
    <div
      v-if="error"
      class="flex items-center gap-3 rounded-md border border-[color:var(--status-danger)] bg-[color:var(--status-danger)]/10 px-4 py-3 text-sm text-[color:var(--status-danger)]"
    >
      <span class="font-medium">Could not load dashboard data:</span>
      {{ error }}
      <button class="ml-auto underline" @click="loadDashboardData">
        Retry
      </button>
    </div>

    <!-- KPI Row -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <template v-if="isLoading">
        <div
          v-for="slot in 3"
          :key="`kpi-skeleton-${slot}`"
          class="bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md px-6 py-5 shadow-[var(--shadow-card)]"
        >
          <div class="skeleton h-8 w-24 mb-3" aria-hidden="true"></div>
          <div class="skeleton h-3 w-40" aria-hidden="true"></div>
        </div>
      </template>
      <div
        v-else-if="kpis.length === 0"
        class="md:col-span-3 border border-[color:var(--border)] rounded-md p-6 text-sm text-[color:var(--text-muted)]"
      >
        KPI data is not available for the selected company.
      </div>
      <div
        v-else
        v-for="kpi in kpis"
        :key="kpi.label"
        class="bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md px-6 py-5 shadow-[var(--shadow-card)] flex flex-col relative"
        :class="
          kpi.isPrimary
            ? 'border-l-[3px] border-l-[color:var(--brand-red)] pl-[21px]'
            : ''
        "
      >
        <div
          class="text-[color:var(--text-primary)] font-serif text-[30px] font-semibold leading-tight tracking-tight mb-1"
          :class="{ 'animate-pulse text-[color:var(--text-muted)]': isLoading }"
        >
          {{ isLoading ? "···" : kpi.value }}
        </div>
        <div
          class="text-[color:var(--text-muted)] font-sans text-[11px] uppercase tracking-wider mb-3"
        >
          {{ kpi.label }}
        </div>
        <div
          v-if="kpi.change"
          class="font-mono text-[13px] font-medium"
          :class="
            kpi.changeType === 'up'
              ? 'text-[color:var(--status-success)]'
              : 'text-[color:var(--status-danger)]'
          "
        >
          {{ kpi.change }}
        </div>
      </div>
    </section>

    <!-- Quarterly Snapshot & Chart -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
      <div class="lg:col-span-8 flex flex-col gap-5">
        <SectionPanel
          title="Quarterly Snapshot"
          class="bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md shadow-[var(--shadow-card)]"
        >
          <div class="p-6 border-b border-[color:var(--border)]">
            <div
              v-if="quarterlySnapshot.length"
              class="grid grid-cols-1 md:grid-cols-3 gap-6"
            >
              <div
                v-for="stat in quarterlySnapshot"
                :key="stat.label"
                class="flex flex-col"
              >
                <span
                  class="text-[color:var(--text-muted)] font-sans text-xs uppercase tracking-wider mb-1"
                  >{{ stat.label }}</span
                >
                <span
                  class="font-mono text-xl tabular-nums text-left text-[color:var(--text-primary)] font-semibold"
                  :class="{ 'animate-pulse': isLoading }"
                  >{{ isLoading ? "···" : stat.value }}</span
                >
                <span
                  v-if="stat.change"
                  class="font-mono text-sm mt-1"
                  :class="
                    stat.changeType === 'up'
                      ? 'text-[color:var(--debit-color)]'
                      : 'text-[color:var(--credit-color)]'
                  "
                >
                  {{ stat.change }} {{ stat.changeType === "up" ? "↑" : "↓" }}
                </span>
              </div>
            </div>
            <div
              v-else
              class="text-sm text-[color:var(--text-muted)]"
              role="status"
            >
              {{
                isLoading
                  ? "Loading snapshot…"
                  : "Snapshot data is not available for the selected period."
              }}
            </div>
          </div>
          <div v-if="dashboardData?.trend?.length" class="p-6">
            <SparklinePanel
              title="Revenue vs Expenses (12-month trend)"
              :values="dashboardData.trend"
            />
          </div>
          <div v-else class="p-6 text-sm text-[color:var(--text-muted)]">
            Trend data is not available for the selected period.
          </div>
        </SectionPanel>
      </div>

      <div class="lg:col-span-4 flex flex-col gap-5">
        <SectionPanel
          title="Quarterly Progress"
          subtitle="Close checklist"
          class="h-full bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md shadow-[var(--shadow-card)] p-6"
        >
          <div v-if="quarterlyProgress.length" class="flex flex-col gap-4 mt-2">
            <ProgressTracker
              v-for="item in quarterlyProgress"
              :key="item.label"
              :label="item.label"
              :value="item.value"
            />
          </div>
          <div v-else class="mt-2 text-sm text-[color:var(--text-muted)]">
            Progress data is not available for the selected period.
          </div>
        </SectionPanel>
      </div>
    </section>

    <!-- Recent Activity & Quick Access -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <SectionPanel
        title="Recent Activity"
        class="bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md shadow-[var(--shadow-card)] overflow-hidden"
      >
        <!-- Empty state -->
        <div
          v-if="!isLoading && recentActivities.length === 0"
          class="py-12 text-center text-[color:var(--text-muted)] text-sm"
        >
          No recent activity yet.
        </div>
        <table
          v-else
          class="w-full text-sm font-sans text-left border-collapse"
        >
          <thead>
            <tr
              class="bg-[color:var(--surface-alt)] border-b border-[color:var(--border-strong)]"
            >
              <th
                class="py-3 px-4 font-medium text-[color:var(--text-muted)] uppercase tracking-wider text-xs"
              >
                Action
              </th>
              <th
                class="py-3 px-4 font-medium text-[color:var(--text-muted)] uppercase tracking-wider text-xs"
              >
                Status
              </th>
              <th
                class="py-3 px-4 font-medium text-[color:var(--text-muted)] uppercase tracking-wider text-xs text-right"
              >
                Time
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="activity in recentActivities"
              :key="activity.id"
              class="border-b border-[color:var(--border)] last:border-0 hover:bg-[color:var(--surface-hover)]"
            >
              <td class="py-4 px-4 text-[color:var(--text-primary)]">
                {{ activity.action }}
                <div class="text-[color:var(--text-muted)] text-xs mt-1">
                  by {{ activity.user }}
                </div>
              </td>
              <td class="py-4 px-4">
                <StatusBadge :status="activity.status" />
              </td>
              <td
                class="py-4 px-4 text-right font-mono tabular-nums text-xs text-[color:var(--text-muted)]"
              >
                {{ activity.time }}
              </td>
            </tr>
          </tbody>
        </table>
      </SectionPanel>

      <SectionPanel
        title="Quick Access"
        class="bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md shadow-[var(--shadow-card)] p-6"
      >
        <div
          v-if="quickAccess.length"
          class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2"
        >
          <button
            v-for="item in quickAccess"
            :key="item.label"
            type="button"
            :disabled="quickAccessRoutes[item.label] === undefined"
            @click="openQuickAccess(item.label)"
            class="flex items-center justify-between p-4 border border-[color:var(--border)] rounded bg-[color:var(--surface-alt)] hover:bg-[color:var(--surface-hover)] hover:border-[color:var(--border-strong)] transition-all text-[color:var(--text-primary)] shadow-sm"
          >
            <span class="font-medium font-sans text-[14px]">{{
              item.label
            }}</span>
            <ArrowRight class="w-4 h-4 text-[color:var(--brand-red)]" />
          </button>
        </div>
        <div v-else class="mt-2 text-sm text-[color:var(--text-muted)]">
          Quick access is not available until dashboard data loads.
        </div>
      </SectionPanel>
    </section>
  </div>
</template>
