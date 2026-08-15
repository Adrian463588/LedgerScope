import { defineStore } from "pinia";
import { ref } from "vue";

import { dashboardApi, type DashboardData } from "@/api/endpoints";
import { getApiError } from "@/api/client";

export const useDashboardStore = defineStore("dashboard", () => {
  const data = ref<DashboardData | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  let requestVersion = 0;

  async function fetchDashboard(
    companyId: number,
    periodId?: number,
  ): Promise<DashboardData> {
    const version = ++requestVersion;
    isLoading.value = true;
    error.value = null;

    try {
      const result = await dashboardApi.getDashboardData({
        companyId,
        periodId,
      });
      if (version === requestVersion) data.value = result;
      return result;
    } catch (caught) {
      if (version === requestVersion) error.value = getApiError(caught).message;
      throw caught;
    } finally {
      if (version === requestVersion) isLoading.value = false;
    }
  }

  function reset(): void {
    requestVersion += 1;
    data.value = null;
    isLoading.value = false;
    error.value = null;
  }

  return { data, isLoading, error, fetchDashboard, reset };
});
