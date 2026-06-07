import { defineStore } from 'pinia';
import { ref } from 'vue';

import { reportingApi } from '@/api/endpoints';
import type { ReportItem } from '@/types';

export const useReportingStore = defineStore('reporting', () => {
  const reports = ref<ReportItem[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  async function fetchReports(companyId: number): Promise<void> {
    isLoading.value = true;
    error.value = null;
    try {
      reports.value = await reportingApi.list(companyId);
    } catch (caught) {
      error.value = caught instanceof Error ? caught.message : 'API is currently unreachable.';
    } finally {
      isLoading.value = false;
    }
  }

  async function generateReport(companyId: number, type: string): Promise<void> {
    reports.value.unshift({ id: Date.now(), name: `${type} report`, type, status: 'generating', version: 'v0.1.0', generated_at: new Date().toISOString().slice(0, 10) });
    try {
      await reportingApi.generate(companyId, {
        report_type: type,
        title: `${type} report`,
        parameters: { period: 'Q1 2026' },
      });
    } catch (caught) {
      error.value = caught instanceof Error ? caught.message : 'API is currently unreachable.';
    }
  }

  function reset(): void {
    reports.value = [];
  }

  return { reports, isLoading, error, fetchReports, generateReport, reset };
});
