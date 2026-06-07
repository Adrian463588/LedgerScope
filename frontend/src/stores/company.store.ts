import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import { companyApi } from '@/api/endpoints';
import type { Company } from '@/types';

export const useCompanyStore = defineStore('company', () => {
  const companies = ref<Company[]>([]);
  const activeCompanyId = ref<number | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const activeCompany = computed(() => companies.value.find((company) => company.id === activeCompanyId.value) ?? companies.value[0] ?? null);

  async function fetchCompanies(): Promise<void> {
    isLoading.value = true;
    error.value = null;
    try {
      companies.value = await companyApi.list();
      if (companies.value.length > 0) {
        activeCompanyId.value = companies.value[0]!.id;
      }
    } catch (caught) {
      error.value = caught instanceof Error ? caught.message : 'API is currently unreachable.';
    } finally {
      isLoading.value = false;
    }
  }

  function switchCompany(id: number): void {
    activeCompanyId.value = id;
  }

  function reset(): void {
    companies.value = [];
    activeCompanyId.value = null;
  }

  return { companies, activeCompanyId, activeCompany, isLoading, error, fetchCompanies, switchCompany, reset };
});
