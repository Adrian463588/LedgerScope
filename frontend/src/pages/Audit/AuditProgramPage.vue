<script setup lang="ts">
import { Plus } from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { engagementApi } = useLedgerScopeApi();
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppSelect from "@/components/ui/AppSelect.vue";
import EmptyState from "@/components/ui/EmptyState.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { useNotification } from "@/composables/useNotification";
import { useCompanyStore } from "@/stores/company.store";
import { useUiStore } from "@/stores/ui.store";
import type { AuditProgram, Engagement } from "@/types";

const ui = useUiStore();
const companies = useCompanyStore();
const notification = useNotification();

const engagements = ref<Engagement[]>([]);
const selectedEngagementId = ref<string | number>("");
const programs = ref<AuditProgram[]>([]);
const isLoading = ref(false);
const error = ref<string | null>(null);
const showProgramForm = ref(false);
const showStepForm = ref(false);
const programName = ref("");
const programObjectives = ref("");
const selectedProgramId = ref<string | number>("");
const stepNumber = ref("");
const procedure = ref("");
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
const programOptions = computed(() =>
  programs.value.map((program) => ({ label: program.name, value: program.id })),
);

async function loadPrograms(): Promise<void> {
  if (!activeEngagementId.value) return;
  isLoading.value = true;
  error.value = null;
  try {
    programs.value = await engagementApi.listAuditPrograms(
      activeEngagementId.value,
    );
    if (programs.value[0]) selectedProgramId.value = programs.value[0].id;
  } catch (caught) {
    error.value =
      caught instanceof Error
        ? caught.message
        : "Unable to load audit programs.";
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
    error.value = "Select a company before opening the audit program.";
    return;
  }
  isLoading.value = true;
  try {
    engagements.value = await engagementApi.list(companyId);
    if (engagements.value[0])
      selectedEngagementId.value = engagements.value[0].id;
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Unable to load engagements.";
  } finally {
    isLoading.value = false;
  }
}

async function createProgram(): Promise<void> {
  if (!activeEngagementId.value || !programName.value.trim()) {
    notification.error("Select an engagement and enter a program name.");
    return;
  }
  isSaving.value = true;
  try {
    const program = await engagementApi.createAuditProgram(
      activeEngagementId.value,
      {
        name: programName.value.trim(),
        objectives: programObjectives.value.trim() || undefined,
      },
    );
    programs.value = [program, ...programs.value];
    selectedProgramId.value = program.id;
    programName.value = "";
    programObjectives.value = "";
    showProgramForm.value = false;
    notification.success("Audit program created.");
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to create audit program.",
    );
  } finally {
    isSaving.value = false;
  }
}

async function addStep(): Promise<void> {
  if (
    !activeEngagementId.value ||
    !selectedProgramId.value ||
    !stepNumber.value ||
    !procedure.value.trim()
  ) {
    notification.error("Select a program and complete the procedure fields.");
    return;
  }
  isSaving.value = true;
  try {
    const step = await engagementApi.addAuditProgramStep(
      activeEngagementId.value,
      Number(selectedProgramId.value),
      { step_number: stepNumber.value, procedure: procedure.value.trim() },
    );
    const program = programs.value.find(
      (item) => item.id === Number(selectedProgramId.value),
    );
    if (program) program.steps = [...(program.steps ?? []), step];
    stepNumber.value = "";
    procedure.value = "";
    showStepForm.value = false;
    notification.success("Audit procedure added.");
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to add audit procedure.",
    );
  } finally {
    isSaving.value = false;
  }
}

async function completeStep(
  program: AuditProgram,
  stepId: number,
): Promise<void> {
  if (!activeEngagementId.value) return;
  try {
    const step = await engagementApi.completeAuditProgramStep(
      activeEngagementId.value,
      program.id,
      stepId,
    );
    program.steps = (program.steps ?? []).map((item) =>
      item.id === step.id ? step : item,
    );
    notification.success("Audit procedure marked complete.");
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to complete procedure.",
    );
  }
}

watch(activeEngagementId, () => void loadPrograms());

onMounted(() => {
  ui.setBreadcrumbs(["Audit", "Audit Program"]);
  void loadEngagements();
});
</script>

<template>
  <PageHeader
    eyebrow="Audit Program"
    title="Substantive Testing Matrix"
    subtitle="Procedure coverage and completion status from the engagement API."
  >
    <template #actions>
      <AppButton :icon="Plus" @click="showStepForm = !showStepForm">
        {{ showStepForm ? "Close" : "Add Procedure" }}
      </AppButton>
      <AppButton
        variant="primary"
        :icon="Plus"
        @click="showProgramForm = !showProgramForm"
      >
        {{ showProgramForm ? "Close" : "New Program" }}
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
  <SectionPanel v-if="error" title="Audit program unavailable">
    <p class="state-message state-message--error">{{ error }}</p>
  </SectionPanel>
  <EmptyState
    v-if="!isLoading && !error && engagements.length === 0"
    title="No engagement available"
    body="Create or assign an engagement before configuring procedures."
  />

  <SectionPanel
    v-if="showProgramForm && activeEngagementId"
    title="New audit program"
  >
    <div class="form-grid">
      <AppInput v-model="programName" label="Program name" required />
      <AppInput v-model="programObjectives" label="Objectives" />
      <AppButton variant="primary" :loading="isSaving" @click="createProgram"
        >Create program</AppButton
      >
    </div>
  </SectionPanel>

  <SectionPanel
    v-if="showStepForm && activeEngagementId"
    title="Add audit procedure"
  >
    <div class="form-grid">
      <AppSelect
        v-model="selectedProgramId"
        label="Program"
        :options="programOptions"
      />
      <AppInput v-model="stepNumber" label="Step number" required />
      <AppInput v-model="procedure" label="Procedure and objective" required />
      <AppButton variant="primary" :loading="isSaving" @click="addStep"
        >Add procedure</AppButton
      >
    </div>
  </SectionPanel>

  <EmptyState
    v-if="!isLoading && activeEngagementId && programs.length === 0 && !error"
    title="No audit programs"
    body="Create an audit program to start recording procedures."
  />
  <section
    v-if="activeEngagementId && programs.length > 0"
    class="program-list"
  >
    <SectionPanel
      v-for="program in programs"
      :key="program.id"
      :title="program.name"
    >
      <div class="program-header">
        <p>{{ program.objectives || "No objectives recorded." }}</p>
        <StatusBadge :status="program.status || 'draft'" />
      </div>
      <EmptyState
        v-if="!program.steps?.length"
        title="No procedures"
        body="Add a procedure to this audit program."
      />
      <ul v-else class="step-list">
        <li v-for="step in program.steps" :key="step.id">
          <div>
            <strong>{{ step.step_number }} · {{ step.procedure }}</strong>
            <small>{{ step.is_completed ? "Completed" : "Pending" }}</small>
          </div>
          <AppButton
            v-if="!step.is_completed"
            size="sm"
            variant="secondary"
            @click="completeStep(program, step.id)"
          >
            Complete
          </AppButton>
        </li>
      </ul>
    </SectionPanel>
  </section>
</template>

<style scoped>
.program-list {
  display: grid;
  gap: 20px;
}

.form-grid {
  display: grid;
  gap: 14px;
}

.program-header,
.step-list li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.program-header p,
.state-message {
  margin: 0;
  color: var(--text-secondary);
}

.state-message--error {
  color: var(--status-danger);
}

.step-list {
  display: grid;
  gap: 8px;
  margin: 16px 0 0;
  padding: 0;
  list-style: none;
}

.step-list li {
  border-top: 1px solid var(--border);
  padding: 12px 0;
}

.step-list strong,
.step-list small {
  display: block;
}

.step-list small {
  margin-top: 4px;
  color: var(--text-muted);
}
</style>
