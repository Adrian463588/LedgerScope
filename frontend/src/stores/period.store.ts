import { computed, ref } from "vue";
import { defineStore } from "pinia";

import { accountingApi } from "@/api/endpoints";
import type { AccountingPeriod, FiscalYear } from "@/types";

export interface PeriodOption {
  id: number;
  label: string;
  status: string;
}

export const usePeriodStore = defineStore("period", () => {
  const fiscalYears = ref<FiscalYear[]>([]);
  const periods = ref<AccountingPeriod[]>([]);
  const selectedPeriodId = ref<number | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  let requestVersion = 0;

  const options = computed<PeriodOption[]>(() => {
    const yearsById = new Map(
      fiscalYears.value.map((fiscalYear) => [fiscalYear.id, fiscalYear.year]),
    );

    return periods.value.map((period) => ({
      id: period.id,
      label:
        `${period.period_name} ${yearsById.get(period.fiscal_year_id) ?? ""}`.trim(),
      status: period.status,
    }));
  });

  async function fetchForCompany(companyId: number): Promise<void> {
    const version = ++requestVersion;
    isLoading.value = true;
    error.value = null;
    selectedPeriodId.value = null;

    try {
      const loadedFiscalYears = await accountingApi.fiscalYears(companyId);
      const loadedPeriods = (
        await Promise.all(
          loadedFiscalYears.map((fiscalYear) =>
            accountingApi.periods(companyId, fiscalYear.id),
          ),
        )
      ).flat();

      if (version !== requestVersion) return;

      fiscalYears.value = loadedFiscalYears;
      periods.value = loadedPeriods;
      selectedPeriodId.value = loadedPeriods[0]?.id ?? null;
    } catch (caught) {
      if (version !== requestVersion) return;

      fiscalYears.value = [];
      periods.value = [];
      error.value =
        caught instanceof Error
          ? caught.message
          : "Accounting periods are currently unavailable.";
    } finally {
      if (version === requestVersion) isLoading.value = false;
    }
  }

  function selectPeriod(periodId: number | null): void {
    if (
      periodId !== null &&
      !periods.value.some((period) => period.id === periodId)
    ) {
      return;
    }

    selectedPeriodId.value = periodId;
  }

  function reset(): void {
    requestVersion += 1;
    fiscalYears.value = [];
    periods.value = [];
    selectedPeriodId.value = null;
    isLoading.value = false;
    error.value = null;
  }

  return {
    fiscalYears,
    periods,
    options,
    selectedPeriodId,
    isLoading,
    error,
    fetchForCompany,
    selectPeriod,
    reset,
  };
});
