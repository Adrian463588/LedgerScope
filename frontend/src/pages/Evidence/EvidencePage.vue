<script setup lang="ts">
import { Plus } from "lucide-vue-next";
import { computed, onMounted, ref, watch } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { engagementApi } = useLedgerScopeApi();
import FileUploadZone from "@/components/evidence/FileUploadZone.vue";
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
import type { DocumentRequest, Engagement, EvidenceFile } from "@/types";

const ui = useUiStore();
const companies = useCompanyStore();
const notification = useNotification();

const engagements = ref<Engagement[]>([]);
const selectedEngagementId = ref<string | number>("");
const requests = ref<DocumentRequest[]>([]);
const evidenceFiles = ref<EvidenceFile[]>([]);
const isLoading = ref(false);
const error = ref<string | null>(null);
const showRequestForm = ref(false);
const requestTitle = ref("");
const requestDescription = ref("");
const requestDueDate = ref("");
const isCreatingRequest = ref(false);

const engagementOptions = computed(() =>
  engagements.value.map((engagement) => ({
    label: engagement.name,
    value: engagement.id,
  })),
);
const activeEngagementId = computed(
  () => Number(selectedEngagementId.value) || null,
);
const requestRows = computed(() =>
  requests.value.map((request) => ({
    request: request.title,
    due: request.due_date ?? "—",
    status: request.status,
  })),
);
const evidenceRows = computed(() =>
  evidenceFiles.value.map((file) => ({
    request: file.original_name ?? file.file_name,
    due: `${file.file_size_bytes} bytes`,
    status: file.status ?? "pending",
  })),
);

async function loadEngagementData(): Promise<void> {
  if (!activeEngagementId.value) return;

  isLoading.value = true;
  error.value = null;
  try {
    const [loadedRequests, loadedEvidence] = await Promise.all([
      engagementApi.listDocumentRequests(activeEngagementId.value),
      engagementApi.listEvidence(activeEngagementId.value),
    ]);
    requests.value = loadedRequests;
    evidenceFiles.value = loadedEvidence;
  } catch (caught) {
    error.value =
      caught instanceof Error
        ? caught.message
        : "Unable to load evidence data.";
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
    error.value = "Select a company before opening evidence.";
    return;
  }

  isLoading.value = true;
  error.value = null;
  try {
    engagements.value = await engagementApi.list(companyId);
    const firstEngagement = engagements.value[0];
    if (firstEngagement) selectedEngagementId.value = firstEngagement.id;
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Unable to load engagements.";
  } finally {
    isLoading.value = false;
  }
}

async function uploadEvidence(file: File): Promise<void> {
  if (!activeEngagementId.value) throw new Error("Select an engagement first.");

  const formData = new FormData();
  formData.append("file", file);
  const evidence = await engagementApi.uploadEvidence(
    activeEngagementId.value,
    formData,
  );
  evidenceFiles.value = [evidence, ...evidenceFiles.value];
}

async function createRequest(): Promise<void> {
  if (!activeEngagementId.value || !requestTitle.value.trim()) {
    notification.error("Select an engagement and enter a request title.");
    return;
  }

  isCreatingRequest.value = true;
  try {
    const request = await engagementApi.createDocumentRequest(
      activeEngagementId.value,
      {
        title: requestTitle.value.trim(),
        description: requestDescription.value.trim() || undefined,
        due_date: requestDueDate.value || undefined,
      },
    );
    requests.value = [request, ...requests.value];
    requestTitle.value = "";
    requestDescription.value = "";
    requestDueDate.value = "";
    showRequestForm.value = false;
    notification.success("Document request created.");
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Unable to create request.",
    );
  } finally {
    isCreatingRequest.value = false;
  }
}

watch(activeEngagementId, () => void loadEngagementData());

onMounted(() => {
  ui.setBreadcrumbs(["Evidence"]);
  void loadEngagements();
});
</script>

<template>
  <PageHeader
    title="Document Request List"
    subtitle="PBC request tracking, due dates, and private evidence status."
  >
    <template #actions>
      <AppButton
        variant="primary"
        :icon="Plus"
        @click="showRequestForm = !showRequestForm"
      >
        {{ showRequestForm ? "Close" : "New Request" }}
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

  <EmptyState
    v-if="!isLoading && !error && engagements.length === 0"
    title="No engagement available"
    body="Create or assign an engagement before managing evidence requests."
  />
  <SectionPanel v-if="error" title="Evidence unavailable">
    <p class="state-message state-message--error">{{ error }}</p>
  </SectionPanel>

  <SectionPanel
    v-if="showRequestForm && activeEngagementId"
    title="New document request"
  >
    <div class="request-form">
      <AppInput v-model="requestTitle" label="Title" required />
      <AppInput v-model="requestDescription" label="Description" />
      <AppInput v-model="requestDueDate" label="Due date" type="date" />
      <AppButton
        variant="primary"
        :loading="isCreatingRequest"
        @click="createRequest"
      >
        Create request
      </AppButton>
    </div>
  </SectionPanel>

  <template v-if="activeEngagementId && !error">
    <section class="evidence-grid">
      <SectionPanel title="Upload evidence">
        <FileUploadZone :upload="uploadEvidence" />
      </SectionPanel>
      <SectionPanel title="Evidence files">
        <EmptyState
          v-if="!isLoading && evidenceRows.length === 0"
          title="No evidence uploaded"
          body="Uploaded files will appear here after the private upload succeeds."
        />
        <AppTable
          v-else
          :loading="isLoading"
          :columns="[
            { key: 'request', label: 'File' },
            { key: 'due', label: 'Size' },
            { key: 'status', label: 'Status', isStatus: true },
          ]"
          :data="evidenceRows"
        />
      </SectionPanel>
    </section>

    <SectionPanel title="Document requests">
      <EmptyState
        v-if="!isLoading && requestRows.length === 0"
        title="No document requests"
        body="Create a request to start a PBC evidence workflow."
      />
      <AppTable
        v-else
        :loading="isLoading"
        :columns="[
          { key: 'request', label: 'Request name' },
          { key: 'due', label: 'Due date' },
          { key: 'status', label: 'Status', isStatus: true },
        ]"
        :data="requestRows"
      />
    </SectionPanel>
  </template>
</template>

<style scoped>
.evidence-grid {
  display: grid;
  grid-template-columns: 0.8fr 1.2fr;
  gap: 20px;
}

.request-form {
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
  .evidence-grid {
    grid-template-columns: 1fr;
  }
}
</style>
