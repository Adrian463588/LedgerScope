import { defineStore } from "pinia";
import { computed, ref } from "vue";

import {
  companyApi,
  type CompanyCreatePayload,
  type CompanyUpdatePayload,
} from "@/api/endpoints";
import type { Company } from "@/types";

export const useCompanyStore = defineStore("company", () => {
  const companies = ref<Company[]>([]);
  const activeCompanyId = ref<number | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const activeCompany = computed(
    () =>
      companies.value.find((company) => company.id === activeCompanyId.value) ??
      null,
  );

  async function fetchCompanies(): Promise<void> {
    isLoading.value = true;
    error.value = null;
    try {
      const page = await companyApi.list();
      companies.value = page.items;
      const selectedCompany = companies.value.find(
        (company) => company.id === activeCompanyId.value,
      );
      if (!selectedCompany) {
        activeCompanyId.value = companies.value[0]?.id ?? null;
      }
    } catch (caught) {
      error.value =
        caught instanceof Error
          ? caught.message
          : "API is currently unreachable.";
    } finally {
      isLoading.value = false;
    }
  }

  async function createCompany(
    payload: CompanyCreatePayload,
  ): Promise<Company> {
    const company = await companyApi.create(payload);
    companies.value = [
      company,
      ...companies.value.filter((item) => item.id !== company.id),
    ];
    activeCompanyId.value = company.id;
    return company;
  }

  async function updateCompany(
    id: number,
    payload: CompanyUpdatePayload,
  ): Promise<Company> {
    const company = await companyApi.update(id, payload);
    companies.value = companies.value.map((item) =>
      item.id === company.id ? company : item,
    );
    return company;
  }

  function switchCompany(id: number): void {
    activeCompanyId.value = id;
  }

  function reset(): void {
    companies.value = [];
    activeCompanyId.value = null;
  }

  return {
    companies,
    activeCompanyId,
    activeCompany,
    isLoading,
    error,
    fetchCompanies,
    createCompany,
    updateCompany,
    switchCompany,
    reset,
  };
});
