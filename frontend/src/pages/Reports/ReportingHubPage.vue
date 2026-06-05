<script setup lang="ts">
import { Download, FileBarChart2, Plus } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';

import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useNotification } from '@/composables/useNotification';
import { useCompanyStore } from '@/stores/company.store';
import { useReportingStore } from '@/stores/reporting.store';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const companies = useCompanyStore();
const reporting = useReportingStore();
const notification = useNotification();
const rows = computed(() => reporting.reports);

async function generate(type: string): Promise<void> {
  await reporting.generateReport(companies.activeCompany?.id ?? 1, type);
  notification.success('Report generation started.');
}

onMounted(() => {
  ui.setBreadcrumbs(['Reports']);
  void reporting.fetchReports(companies.activeCompany?.id ?? 1);
});
</script>

<template>
  <PageHeader title="Reporting Hub" subtitle="Generate, track, and download financial and audit outputs.">
    <template #actions>
      <AppButton variant="primary" :icon="Plus" @click="generate('financial_statement')">Generate New Report</AppButton>
    </template>
  </PageHeader>
  <section class="report-grid">
    <SectionPanel title="Financial Statements" subtitle="Core audit outputs">
      <AppButton :icon="FileBarChart2" @click="generate('income_statement')">Income Statement</AppButton>
      <AppButton :icon="FileBarChart2" @click="generate('cash_flow')">Cash Flow Statement</AppButton>
    </SectionPanel>
    <SectionPanel title="Audit Memos" subtitle="Evidence-backed reporting">
      <AppButton :icon="FileBarChart2" @click="generate('audit_findings')">Audit Findings Summary</AppButton>
      <AppButton :icon="FileBarChart2" @click="generate('management_letter')">Management Letter</AppButton>
    </SectionPanel>
  </section>
  <AppTable
    :loading="reporting.isLoading"
    :columns="[
      { key: 'name', label: 'Report' },
      { key: 'version', label: 'Version' },
      { key: 'generated_at', label: 'Generated' },
      { key: 'status', label: 'Status', isStatus: true },
    ]"
    :data="rows"
  />
  <SectionPanel title="Download History & Audit Trail">
    <AppButton :icon="Download">Download selected report</AppButton>
  </SectionPanel>
</template>

<style scoped>
.report-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

@media (max-width: 800px) {
  .report-grid {
    grid-template-columns: 1fr;
  }
}
</style>
