<script setup lang="ts">
import { Upload } from "lucide-vue-next";
import { onMounted, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";
import type { CreateAccountPayload } from "@/api/endpoints";

const { accountingApi } = useLedgerScopeApi();
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppSelect from "@/components/ui/AppSelect.vue";
import AppTable from "@/components/ui/AppTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import { useAccountingStore } from "@/stores/accounting.store";
import { useCompanyStore } from "@/stores/company.store";
import { useUiStore } from "@/stores/ui.store";
import { useNotification } from "@/composables/useNotification";

const ui = useUiStore();
const accounting = useAccountingStore();
const companies = useCompanyStore();
const notification = useNotification();
const fileInput = ref<HTMLInputElement | null>(null);
const showCreateForm = ref(false);
const isSaving = ref(false);
const accountCode = ref("");
const accountName = ref("");
const accountType = ref<CreateAccountPayload["account_type"]>("asset");
const accountDescription = ref("");

async function loadAccounts(): Promise<void> {
  if (!companies.activeCompanyId) {
    await companies.fetchCompanies();
  }

  const companyId = companies.activeCompanyId;
  if (!companyId) return;

  await accounting.fetchAccounts(companyId);
}

function openFilePicker(): void {
  fileInput.value?.click();
}

async function importAccounts(event: Event): Promise<void> {
  const companyId = companies.activeCompanyId;
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!companyId || !file) return;

  try {
    await accountingApi.importAccounts(companyId, file);
    notification.success("Account import queued.");
    await accounting.fetchAccounts(companyId);
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Account import failed.",
    );
  } finally {
    (event.target as HTMLInputElement).value = "";
  }
}

async function createAccount(): Promise<void> {
  const companyId = companies.activeCompanyId;
  if (!companyId || !accountCode.value.trim() || !accountName.value.trim()) {
    notification.error("Account code and name are required.");
    return;
  }

  isSaving.value = true;
  try {
    await accountingApi.createAccount(companyId, {
      account_code: accountCode.value.trim(),
      account_name: accountName.value.trim(),
      account_type: accountType.value,
      description: accountDescription.value.trim() || undefined,
    });
    notification.success("Account created.");
    accountCode.value = "";
    accountName.value = "";
    accountDescription.value = "";
    showCreateForm.value = false;
    await accounting.fetchAccounts(companyId);
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Account creation failed.",
    );
  } finally {
    isSaving.value = false;
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Accounting", "Chart of Accounts"]);
  void loadAccounts();
});
</script>

<template>
  <PageHeader
    title="Chart of Accounts"
    subtitle="Mapped, controlled, and audit-ready account structure."
  >
    <template #actions>
      <input
        ref="fileInput"
        class="sr-only"
        type="file"
        accept=".csv,.xls,.xlsx"
        @change="importAccounts"
      />
      <AppButton :icon="Upload" @click="openFilePicker"
        >Import Accounts</AppButton
      >
      <AppButton variant="primary" @click="showCreateForm = !showCreateForm"
        >New Account</AppButton
      >
    </template>
  </PageHeader>
  <SectionPanel
    title="Account structure"
    :subtitle="`${accounting.accounts.length} account(s) loaded from the API.`"
  />
  <SectionPanel v-if="showCreateForm" title="Create account">
    <div class="create-form">
      <AppInput v-model="accountCode" label="Account code" required />
      <AppInput v-model="accountName" label="Account name" required />
      <AppSelect
        v-model="accountType"
        label="Account type"
        :options="[
          { label: 'Asset', value: 'asset' },
          { label: 'Liability', value: 'liability' },
          { label: 'Equity', value: 'equity' },
          { label: 'Revenue', value: 'revenue' },
          { label: 'Expense', value: 'expense' },
        ]"
      />
      <AppInput v-model="accountDescription" label="Description" />
      <AppButton :loading="isSaving" variant="primary" @click="createAccount"
        >Create</AppButton
      >
    </div>
  </SectionPanel>
  <AppTable
    :loading="accounting.isLoading"
    :columns="[
      { key: 'code', label: 'Code' },
      { key: 'name', label: 'Account Name' },
      { key: 'account_type', label: 'Type' },
      { key: 'is_active', label: 'Active', isStatus: true },
    ]"
    :data="accounting.accounts"
  />
</template>

<style scoped>
.create-form {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
  align-items: end;
}

@media (max-width: 720px) {
  .create-form {
    grid-template-columns: 1fr;
  }
}
</style>
