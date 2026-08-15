<script setup lang="ts">
import { Plus } from "lucide-vue-next";
import { computed, onMounted, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { engagementApi } = useLedgerScopeApi();
import ProgressTracker from "@/components/shared/ProgressTracker.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppSelect from "@/components/ui/AppSelect.vue";
import AppTable from "@/components/ui/AppTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import { useCompanyStore } from "@/stores/company.store";
import { useEngagementStore } from "@/stores/engagement.store";
import { useUiStore } from "@/stores/ui.store";
import { useNotification } from "@/composables/useNotification";

const ui = useUiStore();
const companies = useCompanyStore();
const engagements = useEngagementStore();
const notification = useNotification();
const showCreateForm = ref(false);
const isSaving = ref(false);
const name = ref("");
const engagementType = ref("external_audit");
const startDate = ref("");
const endDate = ref("");
const overallProgress = computed(() => {
  if (engagements.engagements.length === 0) return 0;
  const values = engagements.engagements.map((item) => item.progress ?? 0);
  return Math.round(
    values.reduce((total, value) => total + value, 0) / values.length,
  );
});
const rows = computed(() =>
  engagements.engagements.map((item) => ({
    name: item.name,
    type: item.type,
    period: item.period,
    progress: item.progress == null ? "—" : `${item.progress}%`,
    status: item.status,
    risk: item.risk,
  })),
);

async function loadEngagements(): Promise<void> {
  if (!companies.activeCompanyId) {
    await companies.fetchCompanies();
  }

  const companyId = companies.activeCompanyId;
  if (!companyId) return;

  await engagements.fetchEngagements(companyId);
}

async function createEngagement(): Promise<void> {
  const companyId = companies.activeCompanyId;
  if (!companyId || !name.value.trim() || !startDate.value || !endDate.value) {
    notification.error("Name, start date, and end date are required.");
    return;
  }

  isSaving.value = true;
  try {
    await engagementApi.create(companyId, {
      name: name.value.trim(),
      engagement_type: engagementType.value,
      start_date: startDate.value,
      end_date: endDate.value,
    });
    notification.success("Engagement created.");
    showCreateForm.value = false;
    name.value = "";
    startDate.value = "";
    endDate.value = "";
    await loadEngagements();
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Engagement creation failed.",
    );
  } finally {
    isSaving.value = false;
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Audit", "Engagements"]);
  void loadEngagements();
});
</script>

<template>
  <PageHeader
    title="Audit Engagements"
    subtitle="Engagement planning, assignment, progress, and risk exposure."
  >
    <template #actions>
      <AppButton
        variant="primary"
        :icon="Plus"
        @click="showCreateForm = !showCreateForm"
        >Create Engagement</AppButton
      >
    </template>
  </PageHeader>
  <SectionPanel v-if="showCreateForm" title="Create engagement">
    <div class="create-form">
      <AppInput v-model="name" label="Name" required />
      <AppSelect
        v-model="engagementType"
        label="Type"
        :options="[
          { label: 'External audit', value: 'external_audit' },
          { label: 'Internal audit', value: 'internal_audit' },
          { label: 'Review engagement', value: 'review_engagement' },
          { label: 'Compilation', value: 'compilation_engagement' },
        ]"
      />
      <AppInput v-model="startDate" label="Start date" type="date" required />
      <AppInput v-model="endDate" label="End date" type="date" required />
      <AppButton :loading="isSaving" variant="primary" @click="createEngagement"
        >Save engagement</AppButton
      >
    </div>
  </SectionPanel>
  <ProgressTracker label="Fieldwork completion" :value="overallProgress" />
  <AppTable
    :loading="engagements.isLoading"
    :columns="[
      { key: 'name', label: 'Engagement' },
      { key: 'type', label: 'Type' },
      { key: 'period', label: 'Reporting Period' },
      { key: 'progress', label: 'Progress' },
      { key: 'status', label: 'Status', isStatus: true },
      { key: 'risk', label: 'Risk', isStatus: true },
    ]"
    :data="rows"
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
