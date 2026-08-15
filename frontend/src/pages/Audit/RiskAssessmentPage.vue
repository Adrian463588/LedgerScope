<script setup lang="ts">
import { Plus } from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { engagementApi } = useLedgerScopeApi();
import RiskHeatmap from "@/components/audit/RiskHeatmap.vue";
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppSelect from "@/components/ui/AppSelect.vue";
import AppTable from "@/components/ui/AppTable.vue";
import EmptyState from "@/components/ui/EmptyState.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import { useNotification } from "@/composables/useNotification";
import { useCompanyStore } from "@/stores/company.store";
import { useUiStore } from "@/stores/ui.store";
import type { Engagement, RiskAssessment } from "@/types";

const ui = useUiStore();
const companies = useCompanyStore();
const notification = useNotification();

const engagements = ref<Engagement[]>([]);
const selectedEngagementId = ref<string | number>("");
const risks = ref<RiskAssessment[]>([]);
const isLoading = ref(false);
const error = ref<string | null>(null);
const showForm = ref(false);
const riskArea = ref("");
const riskLevel = ref<"low" | "medium" | "high" | "critical">("medium");
const riskDescription = ref("");
const riskMitigation = ref("");
const isSaving = ref(false);

const activeEngagementId = computed(
  () => Number(selectedEngagementId.value) || null,
);
const engagementOptions = computed(() =>
  engagements.value.map((engagement) => ({
    label: engagement.name,
    value: engagement.id,
  })),
);
const riskRows = computed(() =>
  risks.value.map((risk) => ({
    title: risk.risk_area,
    inherent: risk.inherent_risk ?? risk.risk_level ?? "—",
    residual: risk.residual_risk ?? "—",
    category: risk.risk_category ?? "—",
  })),
);

async function loadRisks(): Promise<void> {
  if (!activeEngagementId.value) return;
  isLoading.value = true;
  error.value = null;
  try {
    risks.value = await engagementApi.listRiskAssessments(
      activeEngagementId.value,
    );
  } catch (caught) {
    error.value =
      caught instanceof Error
        ? caught.message
        : "Unable to load risk assessments.";
  } finally {
    isLoading.value = false;
  }
}

async function loadEngagements(): Promise<void> {
  if (!companies.activeCompanyId) {
    await companies.fetchCompanies();
  }

  const companyId = companies.activeCompanyId;
  if (!companyId) {
    error.value = "Select a company before opening risk assessment.";
    return;
  }
  isLoading.value = true;
  try {
    engagements.value = await engagementApi.list(companyId);
    const first = engagements.value[0];
    if (first) selectedEngagementId.value = first.id;
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Unable to load engagements.";
  } finally {
    isLoading.value = false;
  }
}

async function saveRisk(): Promise<void> {
  if (!activeEngagementId.value || !riskArea.value.trim()) {
    notification.error("Select an engagement and enter a risk area.");
    return;
  }
  isSaving.value = true;
  try {
    const risk = await engagementApi.createRiskAssessment(
      activeEngagementId.value,
      {
        risk_area: riskArea.value.trim(),
        risk_level: riskLevel.value,
        description: riskDescription.value.trim() || undefined,
        mitigation: riskMitigation.value.trim() || undefined,
        inherent_risk: riskLevel.value,
        residual_risk: riskLevel.value,
      },
    );
    risks.value = [risk, ...risks.value];
    riskArea.value = "";
    riskDescription.value = "";
    riskMitigation.value = "";
    showForm.value = false;
    notification.success("Risk assessment created.");
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to create risk assessment.",
    );
  } finally {
    isSaving.value = false;
  }
}

watch(activeEngagementId, () => void loadRisks());

onMounted(() => {
  ui.setBreadcrumbs(["Audit", "Risk Assessment"]);
  void loadEngagements();
});
</script>

<template>
  <PageHeader
    eyebrow="Audit Planning & Risk"
    title="Risk Assessment & Heatmap"
    subtitle="Risk register and residual score visibility from the engagement API."
  >
    <template #actions>
      <AppButton variant="primary" :icon="Plus" @click="showForm = !showForm">
        {{ showForm ? "Close" : "Add New Risk" }}
      </AppButton>
    </template>
  </PageHeader>

  <SectionPanel v-if="engagements.length > 0" title="Engagement scope">
    <AppSelect
      v-model="selectedEngagementId"
      label="Engagement"
      :options="engagementOptions"
    />
  </SectionPanel>
  <SectionPanel v-if="error" title="Risk assessment unavailable">
    <p class="state-message state-message--error">{{ error }}</p>
  </SectionPanel>
  <EmptyState
    v-if="!isLoading && !error && engagements.length === 0"
    title="No engagement available"
    body="Create or assign an engagement before recording audit risks."
  />

  <SectionPanel
    v-if="showForm && activeEngagementId"
    title="Add risk assessment"
  >
    <div class="risk-form">
      <AppInput v-model="riskArea" label="Risk area" required />
      <AppSelect
        v-model="riskLevel"
        label="Risk level"
        :options="[
          { label: 'Low', value: 'low' },
          { label: 'Medium', value: 'medium' },
          { label: 'High', value: 'high' },
          { label: 'Critical', value: 'critical' },
        ]"
      />
      <AppInput v-model="riskDescription" label="Description" />
      <AppInput v-model="riskMitigation" label="Mitigation" />
      <AppButton variant="primary" :loading="isSaving" @click="saveRisk">
        Save risk
      </AppButton>
    </div>
  </SectionPanel>

  <template v-if="activeEngagementId && !error">
    <section class="risk-grid">
      <SectionPanel title="Risk Heatmap"
        ><RiskHeatmap :risks="risks"
      /></SectionPanel>
      <SectionPanel title="Risk register summary">
        <p class="state-message">
          {{ risks.length }} recorded risk(s) for this engagement.
        </p>
      </SectionPanel>
    </section>
    <SectionPanel title="Risk register">
      <EmptyState
        v-if="!isLoading && riskRows.length === 0"
        title="No risks recorded"
        body="Add a risk assessment to populate the engagement risk register."
      />
      <AppTable
        v-else
        :loading="isLoading"
        :columns="[
          { key: 'title', label: 'Risk area' },
          { key: 'category', label: 'Category' },
          { key: 'inherent', label: 'Inherent risk', isStatus: true },
          { key: 'residual', label: 'Residual risk', isStatus: true },
        ]"
        :data="riskRows"
      />
    </SectionPanel>
  </template>
</template>

<style scoped>
.risk-grid {
  display: grid;
  grid-template-columns: 0.8fr 1.2fr;
  gap: 20px;
}

.risk-form {
  display: grid;
  gap: 14px;
}

.state-message {
  margin: 0;
  color: var(--text-secondary);
}

.state-message--error {
  color: var(--status-danger);
}

@media (max-width: 900px) {
  .risk-grid {
    grid-template-columns: 1fr;
  }
}
</style>
