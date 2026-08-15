<script setup lang="ts">
import { Building2, Plus } from "lucide-vue-next";
import { reactive, ref, onMounted } from "vue";

import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppModal from "@/components/ui/AppModal.vue";
import AppTable, { type TableRow } from "@/components/ui/AppTable.vue";
import EmptyState from "@/components/ui/EmptyState.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import { navigateTo } from "@/router";
import { useCompanyStore } from "@/stores/company.store";
import { useUiStore } from "@/stores/ui.store";
import { companyCreateSchema } from "@/schemas/company.schema";

const ui = useUiStore();
const companies = useCompanyStore();
const isCreateOpen = ref(false);
const isSaving = ref(false);
const formError = ref<string | null>(null);
const form = reactive({
  name: "",
  legal_name: "",
  industry: "",
  currency: "IDR",
});

function resetForm(): void {
  form.name = "";
  form.legal_name = "";
  form.industry = "";
  form.currency = "IDR";
  formError.value = null;
}

function openCreate(): void {
  resetForm();
  isCreateOpen.value = true;
}

function handleRowClick(row: TableRow): void {
  if (typeof row["id"] === "number") {
    companies.switchCompany(row["id"]);
    navigateTo(`/companies/${row["id"]}`);
  }
}

async function createCompany(): Promise<void> {
  const parsed = companyCreateSchema.safeParse({
    name: form.name,
    legal_name: form.legal_name,
    industry: form.industry,
    currency: form.currency,
  });
  if (!parsed.success) {
    formError.value =
      parsed.error.issues[0]?.message ?? "Invalid company data.";
    return;
  }

  isSaving.value = true;
  formError.value = null;
  try {
    const company = await companies.createCompany(parsed.data);
    await companies.fetchCompanies();
    companies.switchCompany(company.id);
    isCreateOpen.value = false;
    navigateTo(`/companies/${company.id}`);
  } catch (caught) {
    formError.value =
      caught instanceof Error ? caught.message : "Unable to create company.";
  } finally {
    isSaving.value = false;
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Companies"]);
  void companies.fetchCompanies();
});
</script>

<template>
  <PageHeader
    title="Companies Master Data"
    subtitle="Manage assigned entities, fiscal reporting profile, and access."
  >
    <template #actions>
      <AppButton variant="primary" :icon="Plus" @click="openCreate"
        >New Company</AppButton
      >
    </template>
  </PageHeader>

  <div v-if="companies.error" class="state state--error">
    {{ companies.error }}
  </div>
  <EmptyState
    v-else-if="!companies.isLoading && companies.companies.length === 0"
    :icon="Building2"
    title="No companies yet"
    body="Create a company to start bookkeeping and audit workflows."
  />
  <AppTable
    v-else
    :loading="companies.isLoading"
    :columns="[
      { key: 'name', label: 'Company' },
      { key: 'industry', label: 'Industry' },
      { key: 'reporting_period', label: 'Reporting Period' },
      { key: 'status', label: 'Status', isStatus: true },
    ]"
    :data="companies.companies"
    @row-click="handleRowClick"
  />

  <AppModal
    :open="isCreateOpen"
    title="Create company"
    @close="isCreateOpen = false"
  >
    <div class="form-grid">
      <AppInput v-model="form.name" label="Company name" required />
      <AppInput v-model="form.legal_name" label="Legal name" />
      <AppInput v-model="form.industry" label="Industry" />
      <AppInput
        v-model="form.currency"
        label="Currency"
        hint="ISO code, e.g. IDR"
      />
    </div>
    <p v-if="formError" class="state state--error">{{ formError }}</p>
    <template #footer>
      <AppButton @click="isCreateOpen = false">Cancel</AppButton>
      <AppButton variant="primary" :loading="isSaving" @click="createCompany">
        Create company
      </AppButton>
    </template>
  </AppModal>
</template>

<style scoped>
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.state {
  padding: 24px;
  color: var(--text-secondary);
}

.state--error {
  color: var(--status-danger);
}

@media (max-width: 700px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
