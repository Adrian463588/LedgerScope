<script setup lang="ts">
import { Download, RefreshCw, ArrowRight } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

import SparklinePanel from '@/components/charts/SparklinePanel.vue';
import PeriodSelector from '@/components/shared/PeriodSelector.vue';
import ProgressTracker from '@/components/shared/ProgressTracker.vue';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { dashboardApi, type DashboardData } from '@/api/endpoints';
import { useCompanyStore } from '@/stores/company.store';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const companyStore = useCompanyStore();

const dashboardData = ref<DashboardData | null>(null);
const isLoading = ref(true);
const error = ref<string | null>(null);

async function loadDashboardData(): Promise<void> {
  isLoading.value = true;
  error.value = null;
  try {
    // Fetch dashboard data and company list in parallel
    const [data] = await Promise.all([
      dashboardApi.getDashboardData(),
      companyStore.fetchCompanies(),
    ]);
    dashboardData.value = data;
  } catch (e) {
    error.value = e instanceof Error ? e.message : 'Failed to load dashboard data';
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  ui.setBreadcrumbs(['Dashboard']);
  loadDashboardData();
});

// All derived data MUST be computed() so they remain reactive after the API resolves.
const kpis = computed(
  () =>
    dashboardData.value?.kpis ?? [
      { label: 'Total Active Engagements', value: '—', change: '', changeType: 'up' as const, isPrimary: true },
      { label: 'Outstanding Document Requests', value: '—', change: '', changeType: 'down' as const, isPrimary: false },
      { label: 'Open Findings', value: '—', change: '', changeType: 'down' as const, isPrimary: false },
    ],
);

const quarterlySnapshot = computed(
  () =>
    dashboardData.value?.quarterlySnapshot ?? [
      { label: 'Revenue', value: 'IDR —', change: '', changeType: 'up' as const },
      { label: 'Expenses', value: 'IDR —', change: '', changeType: 'down' as const },
      { label: 'Net Profit', value: 'IDR —', change: '', changeType: 'up' as const },
    ],
);

const recentActivities = computed(
  () => dashboardData.value?.recentActivities ?? [],
);

const quickAccess = computed(
  () =>
    dashboardData.value?.quickAccess ?? [
      { label: 'Journal Entries', hasData: false },
      { label: 'Trial Balance', hasData: false },
      { label: 'Working Papers', hasData: false },
      { label: 'Reports', hasData: false },
    ],
);
</script>

<template>
  <div class="max-w-[1440px] mx-auto w-full p-6 md:p-8 flex flex-col gap-8">
    <PageHeader title="Dashboard" subtitle="High-confidence accounting and audit control center.">
      <template #actions>
        <PeriodSelector />
        <AppButton :icon="RefreshCw" :loading="isLoading" @click="loadDashboardData">Refresh</AppButton>
        <AppButton variant="primary" :icon="Download">Export Report</AppButton>
      </template>
    </PageHeader>

    <!-- Error banner -->
    <div
      v-if="error"
      class="flex items-center gap-3 rounded-md border border-[color:var(--status-danger)] bg-[color:var(--status-danger)]/10 px-4 py-3 text-sm text-[color:var(--status-danger)]"
    >
      <span class="font-medium">Could not load dashboard data:</span>
      {{ error }}
      <button class="ml-auto underline" @click="loadDashboardData">Retry</button>
    </div>

    <!-- KPI Row -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <div
        v-for="kpi in kpis"
        :key="kpi.label"
        class="bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md px-6 py-5 shadow-[var(--shadow-card)] flex flex-col relative"
        :class="kpi.isPrimary ? 'border-l-[3px] border-l-[color:var(--brand-red)] pl-[21px]' : ''"
      >
        <div
          class="text-[color:var(--text-primary)] font-serif text-[30px] font-semibold leading-tight tracking-tight mb-1"
          :class="{ 'animate-pulse text-[color:var(--text-muted)]': isLoading }"
        >
          {{ isLoading ? '···' : kpi.value }}
        </div>
        <div class="text-[color:var(--text-muted)] font-sans text-[11px] uppercase tracking-wider mb-3">
          {{ kpi.label }}
        </div>
        <div
          v-if="kpi.change"
          class="font-mono text-[13px] font-medium"
          :class="kpi.changeType === 'up' ? 'text-[color:var(--status-success)]' : 'text-[color:var(--status-danger)]'"
        >
          {{ kpi.change }}
        </div>
      </div>
    </section>

    <!-- Quarterly Snapshot & Chart -->
    <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
      <div class="lg:col-span-8 flex flex-col gap-5">
        <SectionPanel title="Quarterly Snapshot" class="bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md shadow-[var(--shadow-card)]">
          <div class="p-6 border-b border-[color:var(--border)]">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div v-for="stat in quarterlySnapshot" :key="stat.label" class="flex flex-col">
                <span class="text-[color:var(--text-muted)] font-sans text-xs uppercase tracking-wider mb-1">{{ stat.label }}</span>
                <span
                  class="font-mono text-xl tabular-nums text-left text-[color:var(--text-primary)] font-semibold"
                  :class="{ 'animate-pulse': isLoading }"
                >{{ isLoading ? '···' : stat.value }}</span>
                <span v-if="stat.change" class="font-mono text-sm mt-1" :class="stat.changeType === 'up' ? 'text-[color:var(--debit-color)]' : 'text-[color:var(--credit-color)]'">
                  {{ stat.change }} {{ stat.changeType === 'up' ? '↑' : '↓' }}
                </span>
              </div>
            </div>
          </div>
          <div class="p-6">
            <SparklinePanel title="Revenue vs Expenses (12-month trend)" :values="[42, 55, 48, 72, 66, 82, 76, 88]" />
          </div>
        </SectionPanel>
      </div>

      <div class="lg:col-span-4 flex flex-col gap-5">
        <SectionPanel title="Quarterly Progress" subtitle="Close checklist" class="h-full bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md shadow-[var(--shadow-card)] p-6">
          <div class="flex flex-col gap-4 mt-2">
            <ProgressTracker label="Q1 close" :value="78" />
            <ProgressTracker label="Audit completion" :value="64" />
            <ProgressTracker label="Evidence accepted" :value="52" />
          </div>
        </SectionPanel>
      </div>
    </section>

    <!-- Recent Activity & Quick Access -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <SectionPanel title="Recent Activity" class="bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md shadow-[var(--shadow-card)] overflow-hidden">
        <!-- Empty state -->
        <div v-if="!isLoading && recentActivities.length === 0" class="py-12 text-center text-[color:var(--text-muted)] text-sm">
          No recent activity yet.
        </div>
        <table v-else class="w-full text-sm font-sans text-left border-collapse">
          <thead>
            <tr class="bg-[color:var(--surface-alt)] border-b border-[color:var(--border-strong)]">
              <th class="py-3 px-4 font-medium text-[color:var(--text-muted)] uppercase tracking-wider text-xs">Action</th>
              <th class="py-3 px-4 font-medium text-[color:var(--text-muted)] uppercase tracking-wider text-xs">Status</th>
              <th class="py-3 px-4 font-medium text-[color:var(--text-muted)] uppercase tracking-wider text-xs text-right">Time</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="activity in recentActivities" :key="activity.id" class="border-b border-[color:var(--border)] last:border-0 hover:bg-[color:var(--surface-hover)]">
              <td class="py-4 px-4 text-[color:var(--text-primary)]">
                {{ activity.action }}
                <div class="text-[color:var(--text-muted)] text-xs mt-1">by {{ activity.user }}</div>
              </td>
              <td class="py-4 px-4"><StatusBadge :status="activity.status" /></td>
              <td class="py-4 px-4 text-right font-mono tabular-nums text-xs text-[color:var(--text-muted)]">{{ activity.time }}</td>
            </tr>
          </tbody>
        </table>
      </SectionPanel>

      <SectionPanel title="Quick Access" class="bg-[color:var(--surface)] border border-[color:var(--border)] rounded-md shadow-[var(--shadow-card)] p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
          <button
            v-for="item in quickAccess"
            :key="item.label"
            class="flex items-center justify-between p-4 border border-[color:var(--border)] rounded bg-[color:var(--surface-alt)] hover:bg-[color:var(--surface-hover)] hover:border-[color:var(--border-strong)] transition-all text-[color:var(--text-primary)] shadow-sm"
          >
            <span class="font-medium font-sans text-[14px]">{{ item.label }}</span>
            <ArrowRight class="w-4 h-4 text-[color:var(--brand-red)]" />
          </button>
        </div>
      </SectionPanel>
    </section>
  </div>
</template>
