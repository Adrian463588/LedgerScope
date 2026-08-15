import { defineStore } from "pinia";
import { ref } from "vue";

import { reportingApi } from "@/api/endpoints";
import { getApiError } from "@/api/client";
import type { ReportFormat, ReportItem, ReportType } from "@/types";

export const useReportingStore = defineStore("reporting", () => {
  const reports = ref<ReportItem[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  async function fetchReports(companyId: number): Promise<void> {
    isLoading.value = true;
    error.value = null;
    try {
      reports.value = (await reportingApi.list(companyId)).items;
    } catch (caught) {
      error.value = getApiError(caught).message;
    } finally {
      isLoading.value = false;
    }
  }

  async function generateReport(
    companyId: number,
    payload: {
      report_type: ReportType;
      title: string;
      format?: ReportFormat;
      parameters?: {
        accounting_period_id?: number;
        engagement_id?: number;
        financial_statement_id?: number;
      };
    },
  ): Promise<ReportItem | null> {
    error.value = null;
    try {
      const report = await reportingApi.generate(companyId, payload);
      reports.value = [report, ...reports.value];
      return report;
    } catch (caught) {
      error.value = getApiError(caught).message;
      return null;
    }
  }

  async function approveReport(
    companyId: number,
    reportId: number,
  ): Promise<ReportItem | null> {
    error.value = null;
    try {
      const report = await reportingApi.approve(companyId, reportId);
      reports.value = reports.value.map((item) =>
        item.id === report.id ? report : item,
      );
      return report;
    } catch (caught) {
      error.value = getApiError(caught).message;
      return null;
    }
  }

  function reset(): void {
    reports.value = [];
  }

  return {
    reports,
    isLoading,
    error,
    fetchReports,
    generateReport,
    approveReport,
    reset,
  };
});
