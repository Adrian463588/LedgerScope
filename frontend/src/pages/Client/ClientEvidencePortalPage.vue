<script setup lang="ts">
import { onMounted, ref } from "vue";
import {
  UploadCloud,
  FileText,
  CheckCircle2,
  AlertTriangle,
  HelpCircle,
} from "lucide-vue-next";

import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { useNotification } from "@/composables/useNotification";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { engagementApi } = useLedgerScopeApi();
import { useUiStore } from "@/stores/ui.store";
import type { ClientDocumentRequest } from "@/types";

const ui = useUiStore();
const notification = useNotification();

const requests = ref<ClientDocumentRequest[]>([]);
const selectedRequest = ref<ClientDocumentRequest | null>(null);
const isLoading = ref(true);
const isUploading = ref(false);

const selectedFile = ref<File | null>(null);
const uploadDescription = ref("");

async function loadRequests(): Promise<void> {
  isLoading.value = true;
  try {
    const data = await engagementApi.listClientDocumentRequests();
    requests.value = data;
    const firstRequest = data[0];
    const currentRequest = selectedRequest.value;
    if (firstRequest && !currentRequest) {
      void selectRequest(firstRequest);
    } else if (currentRequest) {
      // Refresh current selection
      const updated = data.find((r) => r.id === currentRequest.id);
      if (updated) selectedRequest.value = updated;
    }
  } catch {
    notification.error("Failed to load document requests.");
  } finally {
    isLoading.value = false;
  }
}

async function selectRequest(req: ClientDocumentRequest): Promise<void> {
  try {
    // Calling getClientDocumentRequest will auto-transition 'requested' status to 'in_progress'
    const detailed = await engagementApi.getClientDocumentRequest(req.id);
    selectedRequest.value = detailed;
    selectedFile.value = null;
    uploadDescription.value = "";

    // Update the request in the list with the updated status
    const index = requests.value.findIndex((r) => r.id === req.id);
    const request = requests.value[index];
    if (request) {
      request.status = detailed.status;
    }
  } catch {
    notification.error("Failed to retrieve request details.");
  }
}

function handleFileSelect(event: Event): void {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (file) {
    selectedFile.value = file;
  }
}

async function handleUpload(): Promise<void> {
  if (!selectedRequest.value) return;
  const file = selectedFile.value;
  if (!file) {
    notification.error("Please select a file to upload.");
    return;
  }

  isUploading.value = true;
  try {
    await engagementApi.uploadClientDocumentRequest(
      selectedRequest.value.id,
      file,
      uploadDescription.value,
    );
    notification.success("Document uploaded and submitted successfully.");
    selectedFile.value = null;
    uploadDescription.value = "";
    await loadRequests();
  } catch (error: unknown) {
    notification.error(
      error instanceof Error ? error.message : "Failed to upload document.",
    );
  } finally {
    isUploading.value = false;
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Client Portal", "Document Requests"]);
  void loadRequests();
});
</script>

<template>
  <PageHeader
    title="Client Evidence Portal"
    subtitle="View document requests from your audit team and securely upload evidence."
  >
  </PageHeader>

  <div v-if="isLoading && requests.length === 0" class="loading-state">
    <p>Loading document requests...</p>
  </div>

  <div v-else class="portal-grid">
    <!-- Left Sidebar: Requests List -->
    <div class="requests-sidebar">
      <SectionPanel title="Requests List">
        <div v-if="requests.length === 0" class="empty-requests">
          <p>No document requests assigned to you.</p>
        </div>
        <div v-else class="requests-list">
          <button
            v-for="req in requests"
            :key="req.id"
            class="request-card"
            :class="{ active: selectedRequest?.id === req.id }"
            @click="selectRequest(req)"
          >
            <div class="card-top">
              <span class="request-title">{{ req.title }}</span>
              <StatusBadge :status="req.status" />
            </div>
            <div class="card-bottom">
              <span v-if="req.due_date" class="due-date"
                >Due: {{ new Date(req.due_date).toLocaleDateString() }}</span
              >
            </div>
          </button>
        </div>
      </SectionPanel>
    </div>

    <!-- Right Pane: Selected Request Details & Upload -->
    <div class="details-pane">
      <div v-if="!selectedRequest" class="no-selection">
        <HelpCircle class="help-icon" />
        <h3>Select a request to view details</h3>
        <p>
          Choose an item from the left sidebar to see instructions and upload
          evidence.
        </p>
      </div>

      <div v-else class="details-content">
        <SectionPanel :title="selectedRequest.title">
          <template #actions>
            <StatusBadge :status="selectedRequest.status" />
          </template>

          <div class="request-details">
            <div class="detail-group">
              <h4>Instructions</h4>
              <p class="instructions-text">
                {{
                  selectedRequest.description ||
                  "No additional instructions provided."
                }}
              </p>
            </div>

            <div v-if="selectedRequest.due_date" class="detail-group">
              <h4>Due Date</h4>
              <p class="due-text">
                {{ new Date(selectedRequest.due_date).toLocaleDateString() }}
              </p>
            </div>

            <!-- Rejection Callout -->
            <div
              v-if="selectedRequest.status === 'rejected'"
              class="rejection-callout"
            >
              <AlertTriangle class="warn-icon" />
              <div>
                <h5>Request Rejected by Auditor</h5>
                <p>
                  {{
                    selectedRequest.rejection_reason ||
                    "No rejection reason specified."
                  }}
                </p>
              </div>
            </div>
          </div>
        </SectionPanel>

        <!-- Current Evidence -->
        <SectionPanel
          v-if="selectedRequest.evidence_file"
          title="Submitted Evidence"
          :icon="FileText"
        >
          <div class="submitted-evidence">
            <CheckCircle2 class="success-icon" />
            <div class="file-info">
              <span class="file-name">{{
                selectedRequest.evidence_file.original_name
              }}</span>
              <span class="file-size"
                >({{
                  (
                    selectedRequest.evidence_file.file_size_bytes / 1024
                  ).toFixed(1)
                }}
                KB)</span
              >
              <p class="upload-time">Submitted via portal</p>
            </div>
          </div>
        </SectionPanel>

        <!-- Upload Form -->
        <SectionPanel
          v-if="
            ['requested', 'in_progress', 'rejected'].includes(
              selectedRequest.status,
            )
          "
          title="Upload Evidence"
          :icon="UploadCloud"
        >
          <div class="upload-form">
            <div class="file-input-zone">
              <UploadCloud class="upload-icon" />
              <p v-if="!selectedFile">
                Drag and drop file here, or click to browse
              </p>
              <p v-else class="selected-file-name">{{ selectedFile.name }}</p>
              <label class="file-label">
                <input type="file" @change="handleFileSelect" />
                <span class="browse-btn">Choose File</span>
              </label>
            </div>

            <div class="form-inputs">
              <AppInput
                v-model="uploadDescription"
                label="Comment / Description"
                placeholder="Explain what this file contains..."
              />
              <div class="form-actions">
                <AppButton
                  variant="primary"
                  :disabled="isUploading || !selectedFile"
                  @click="handleUpload"
                >
                  {{ isUploading ? "Uploading..." : "Submit Evidence" }}
                </AppButton>
              </div>
            </div>
          </div>
        </SectionPanel>
      </div>
    </div>
  </div>
</template>

<style scoped>
.loading-state,
.no-selection {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 300px;
  color: var(--text-secondary);
  text-align: center;
}

.help-icon {
  width: 48px;
  height: 48px;
  color: var(--text-muted);
  margin-bottom: 16px;
}

.portal-grid {
  display: grid;
  grid-template-columns: 0.8fr 1.2fr;
  gap: 24px;
}

.requests-sidebar {
  display: flex;
  flex-direction: column;
}

.requests-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.request-card {
  display: flex;
  flex-direction: column;
  text-align: left;
  background-color: var(--surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 14px;
  cursor: pointer;
  transition: all 0.2s;
}

.request-card:hover {
  border-color: var(--border-strong);
  background-color: var(--surface-hover);
}

.request-card.active {
  border-color: var(--brand-red);
  box-shadow: 0 0 0 1px var(--brand-red);
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 8px;
}

.request-title {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 14px;
}

.card-bottom {
  display: flex;
  font-size: 12px;
  color: var(--text-muted);
}

.details-pane {
  display: flex;
  flex-direction: column;
}

.details-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.request-details {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-group h4 {
  margin: 0 0 6px;
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 500;
}

.instructions-text {
  font-size: 15px;
  line-height: 1.6;
  color: var(--text-primary);
}

.rejection-callout {
  display: flex;
  gap: 12px;
  background-color: var(--status-danger-bg);
  border: 1px solid var(--status-danger);
  border-radius: 8px;
  padding: 14px;
  color: var(--text-primary);
  margin-top: 8px;
}

.rejection-callout h5 {
  margin: 0 0 4px;
  color: var(--status-danger);
  font-weight: 600;
}

.rejection-callout p {
  margin: 0;
  font-size: 13px;
  color: var(--text-secondary);
}

.warn-icon {
  color: var(--status-danger);
  flex-shrink: 0;
  width: 20px;
  height: 20px;
}

.submitted-evidence {
  display: flex;
  align-items: center;
  gap: 16px;
  background-color: var(--surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 16px;
}

.success-icon {
  color: var(--status-success);
  width: 24px;
  height: 24px;
}

.file-info {
  display: flex;
  flex-direction: column;
}

.file-name {
  font-weight: 600;
  color: var(--text-primary);
}

.file-size {
  font-size: 12px;
  color: var(--text-muted);
  margin-left: 6px;
}

.upload-time {
  margin: 4px 0 0;
  font-size: 12px;
  color: var(--text-secondary);
}

.file-input-zone {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 2px dashed var(--border-strong);
  border-radius: 8px;
  padding: 30px;
  text-align: center;
  background-color: var(--surface);
  margin-bottom: 20px;
}

.upload-icon {
  width: 36px;
  height: 36px;
  color: var(--text-muted);
  margin-bottom: 10px;
}

.file-input-zone input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.browse-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 36px;
  border: 1px solid var(--border-strong);
  border-radius: 6px;
  background-color: var(--surface-alt);
  color: var(--text-primary);
  font-weight: 500;
  padding: 0 16px;
  cursor: pointer;
  margin-top: 12px;
}

.browse-btn:hover {
  background-color: var(--surface-hover);
}

.selected-file-name {
  font-weight: 600;
  color: var(--brand-red);
  font-family: "IBM Plex Mono", monospace;
}

.form-inputs {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
}

@media (max-width: 900px) {
  .portal-grid {
    grid-template-columns: 1fr;
  }
}
</style>
