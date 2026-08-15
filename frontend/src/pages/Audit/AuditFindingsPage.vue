<script setup lang="ts">
import { onMounted, ref } from "vue";
import {
  Plus,
  CheckCircle2,
  RotateCcw,
  HelpCircle,
  Save,
} from "lucide-vue-next";

import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { useNotification } from "@/composables/useNotification";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { engagementApi } = useLedgerScopeApi();
import { useUiStore } from "@/stores/ui.store";
import { useCompanyStore } from "@/stores/company.store";
import type { Finding } from "@/types";

const ui = useUiStore();
const companyStore = useCompanyStore();
const notification = useNotification();
const confirmDialog = useConfirmDialog();

const findings = ref<Finding[]>([]);
const selectedFinding = ref<Finding | null>(null);
const isLoading = ref(true);
const loadError = ref<string | null>(null);
const engagementId = ref<number | null>(null);

// Modal and Form States
const showCreateModal = ref(false);
const showReopenModal = ref(false);
const reopenReason = ref("");

const newFindingTitle = ref("");
const newFindingDesc = ref("");
const newFindingSeverity = ref("high");
const newFindingCategory = ref("financial");
const newFindingRec = ref("");

// Management Response State
const mgtResponse = ref("");

async function loadEngagement(): Promise<void> {
  try {
    isLoading.value = true;
    loadError.value = null;
    if (!companyStore.activeCompanyId) {
      await companyStore.fetchCompanies();
    }

    const companyId = companyStore.activeCompanyId;
    if (!companyId) {
      loadError.value = "No company is available for this workspace.";
      return;
    }

    const engagements = await engagementApi.list(companyId);
    if (engagements.length > 0) {
      const firstEng = engagements[0];
      if (firstEng && firstEng.id !== undefined) {
        engagementId.value = firstEng.id;
        await loadFindings();
      }
    } else {
      isLoading.value = false;
    }
  } catch {
    loadError.value = "Failed to load the active engagement.";
    notification.error("Failed to load active engagement.");
  } finally {
    isLoading.value = false;
  }
}

async function loadFindings(): Promise<void> {
  if (!engagementId.value) return;
  isLoading.value = true;
  try {
    const data = await engagementApi.listFindings(engagementId.value);
    findings.value = data;
    const firstFinding = data[0];
    const currentFinding = selectedFinding.value;
    if (firstFinding && !currentFinding) {
      selectFinding(firstFinding);
    } else if (currentFinding) {
      const updated = data.find((f) => f.id === currentFinding.id);
      if (updated) selectFinding(updated);
    }
  } catch {
    notification.error("Failed to load findings.");
  } finally {
    isLoading.value = false;
  }
}

function selectFinding(finding: Finding): void {
  selectedFinding.value = finding;
  mgtResponse.value = finding.management_response || "";
}

async function handleCreateFinding(): Promise<void> {
  if (!engagementId.value) return;
  if (!newFindingTitle.value || !newFindingDesc.value) {
    notification.error("Title and description are required.");
    return;
  }

  try {
    await engagementApi.createFinding(engagementId.value, {
      title: newFindingTitle.value,
      description: newFindingDesc.value,
      severity: newFindingSeverity.value,
      category: newFindingCategory.value,
      recommendation: newFindingRec.value,
    });
    notification.success("Finding recorded successfully.");
    showCreateModal.value = false;
    newFindingTitle.value = "";
    newFindingDesc.value = "";
    newFindingRec.value = "";
    await loadFindings();
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Failed to create finding.",
    );
  }
}

async function handleResolve(): Promise<void> {
  if (!engagementId.value || !selectedFinding.value) return;
  const confirmed = await confirmDialog.confirm({
    title: "Resolve Finding",
    message: "Are you sure you want to resolve this audit finding?",
    tone: "danger",
    confirmLabel: "Resolve",
  });
  if (!confirmed) return;

  try {
    await engagementApi.resolveFinding(
      engagementId.value,
      selectedFinding.value.id,
    );
    notification.success("Finding resolved.");
    await loadFindings();
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Failed to resolve finding.",
    );
  }
}

async function handleReopen(): Promise<void> {
  if (!engagementId.value || !selectedFinding.value) return;
  if (reopenReason.value.length < 5) {
    notification.error(
      "Please provide a reopen reason of at least 5 characters.",
    );
    return;
  }

  try {
    await engagementApi.reopenFinding(
      engagementId.value,
      selectedFinding.value.id,
      reopenReason.value,
    );
    notification.success("Finding reopened.");
    showReopenModal.value = false;
    reopenReason.value = "";
    await loadFindings();
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Failed to reopen finding.",
    );
  }
}

async function handleMgtResponse(): Promise<void> {
  if (!engagementId.value || !selectedFinding.value) return;
  try {
    await engagementApi.managementResponseFinding(
      engagementId.value,
      selectedFinding.value.id,
      {
        management_response: mgtResponse.value,
      },
    );
    notification.success("Management response recorded.");
    await loadFindings();
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Failed to save management response.",
    );
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Audit", "Findings"]);
  void loadEngagement();
});
</script>

<template>
  <PageHeader
    title="Audit Findings"
    subtitle="Document, tracks, and remediates issues, control weaknesses, and financial misstatements."
  >
    <template #actions>
      <AppButton
        variant="primary"
        :icon="Plus"
        :disabled="!engagementId"
        @click="showCreateModal = true"
      >
        Record Finding
      </AppButton>
    </template>
  </PageHeader>

  <div v-if="isLoading && findings.length === 0" class="loading-state">
    <p>Loading audit findings...</p>
  </div>

  <div v-else-if="loadError" class="empty-state">
    <SectionPanel title="Audit Findings">
      <p>{{ loadError }}</p>
    </SectionPanel>
  </div>

  <div v-else-if="findings.length === 0" class="empty-state">
    <SectionPanel title="Audit Findings">
      <div class="no-findings">
        <CheckCircle2 class="icon-success" />
        <h3>No findings recorded</h3>
        <p>
          All clear! There are no outstanding findings or issues for this
          engagement.
        </p>
      </div>
    </SectionPanel>
  </div>

  <div v-else class="findings-grid">
    <!-- Left column: list of findings -->
    <div class="findings-sidebar">
      <SectionPanel title="Findings List">
        <div class="list-wrapper">
          <button
            v-for="item in findings"
            :key="item.id"
            class="finding-card"
            :class="{ active: selectedFinding?.id === item.id }"
            @click="selectFinding(item)"
          >
            <div class="card-header">
              <span class="finding-title">{{ item.title }}</span>
              <StatusBadge :status="item.severity" />
            </div>
            <div class="card-footer">
              <span class="category">{{ item.category }}</span>
              <StatusBadge :status="item.status" />
            </div>
          </button>
        </div>
      </SectionPanel>
    </div>

    <!-- Right column: finding details -->
    <div class="details-pane">
      <div v-if="!selectedFinding" class="no-selection">
        <HelpCircle class="help-icon" />
        <h3>Select a finding to view details</h3>
        <p>
          Choose an item from the left sidebar to see instructions and
          responses.
        </p>
      </div>

      <div v-else class="details-content">
        <SectionPanel :title="selectedFinding.title">
          <template #actions>
            <StatusBadge :status="selectedFinding.status" />
            <StatusBadge :status="selectedFinding.severity" />
          </template>

          <div class="finding-details">
            <div class="detail-group">
              <h4>Description & Root Cause</h4>
              <p class="detail-text">{{ selectedFinding.description }}</p>
            </div>

            <div v-if="selectedFinding.recommendation" class="detail-group">
              <h4>Auditor Recommendation</h4>
              <p class="detail-text rec-text">
                {{ selectedFinding.recommendation }}
              </p>
            </div>
          </div>

          <div class="action-buttons-row">
            <AppButton
              v-if="
                selectedFinding.status !== 'resolved' &&
                selectedFinding.status !== 'closed'
              "
              variant="primary"
              :icon="CheckCircle2"
              @click="handleResolve"
            >
              Resolve Finding
            </AppButton>
            <AppButton
              v-if="selectedFinding.status === 'resolved'"
              variant="secondary"
              :icon="RotateCcw"
              @click="showReopenModal = true"
            >
              Reopen Finding
            </AppButton>
          </div>
        </SectionPanel>

        <!-- Management Response Section -->
        <SectionPanel title="Management Response">
          <div class="mgt-response-section">
            <p class="description">
              Provide formal management response, detailing action plan,
              responsible person, and target remediation date.
            </p>
            <textarea
              v-model="mgtResponse"
              class="mgt-textarea"
              placeholder="Enter management response and action plan..."
              rows="5"
            ></textarea>
            <div class="response-actions">
              <AppButton
                variant="secondary"
                :icon="Save"
                @click="handleMgtResponse"
              >
                Save Response
              </AppButton>
            </div>
          </div>
        </SectionPanel>
      </div>
    </div>
  </div>

  <!-- Create Finding Modal -->
  <div v-if="showCreateModal" class="modal-overlay">
    <div class="modal-content">
      <h3>Record Audit Finding</h3>
      <div class="form-group">
        <AppInput
          v-model="newFindingTitle"
          label="Title"
          placeholder="e.g. Incomplete Revenue cut-off records"
          required
        />
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea
          v-model="newFindingDesc"
          class="form-textarea"
          placeholder="Detailed description of the issue..."
        ></textarea>
      </div>
      <div class="form-group-row">
        <div class="form-group">
          <label>Severity</label>
          <select v-model="newFindingSeverity" class="form-select">
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>
        <div class="form-group">
          <label>Category</label>
          <select v-model="newFindingCategory" class="form-select">
            <option value="financial">Financial</option>
            <option value="operational">Operational</option>
            <option value="compliance">Compliance</option>
            <option value="internal_control">Internal Control</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Recommendation</label>
        <textarea
          v-model="newFindingRec"
          class="form-textarea"
          placeholder="Recommendation for remediation..."
        ></textarea>
      </div>
      <div class="modal-buttons">
        <AppButton variant="secondary" @click="showCreateModal = false"
          >Cancel</AppButton
        >
        <AppButton variant="primary" @click="handleCreateFinding"
          >Record Finding</AppButton
        >
      </div>
    </div>
  </div>

  <!-- Reopen Finding Modal -->
  <div v-if="showReopenModal" class="modal-overlay">
    <div class="modal-content">
      <h3>Reopen Audit Finding</h3>
      <p>Document the reason for reopening this resolved audit finding.</p>
      <textarea
        v-model="reopenReason"
        class="form-textarea"
        rows="4"
        placeholder="Explain why the issue is not fully remediated..."
      ></textarea>
      <div class="modal-buttons">
        <AppButton variant="secondary" @click="showReopenModal = false"
          >Cancel</AppButton
        >
        <AppButton variant="primary" @click="handleReopen"
          >Reopen Finding</AppButton
        >
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

.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 250px;
}

.no-findings {
  text-align: center;
  padding: 40px;
}

.icon-success {
  width: 48px;
  height: 48px;
  color: var(--status-success);
  margin-bottom: 16px;
}

.findings-grid {
  display: grid;
  grid-template-columns: 0.8fr 1.2fr;
  gap: 24px;
}

.list-wrapper {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.finding-card {
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

.finding-card:hover {
  border-color: var(--border-strong);
  background-color: var(--surface-hover);
}

.finding-card.active {
  border-color: var(--brand-red);
  box-shadow: 0 0 0 1px var(--brand-red);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 8px;
}

.finding-title {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 14px;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
}

.category {
  color: var(--text-muted);
  text-transform: capitalize;
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

.finding-details {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.detail-group h4 {
  margin: 0 0 8px;
  font-size: 14px;
  color: var(--text-muted);
  font-weight: 500;
}

.detail-text {
  font-size: 15px;
  line-height: 1.6;
  color: var(--text-primary);
  white-space: pre-wrap;
}

.rec-text {
  background-color: var(--surface-hover);
  padding: 12px;
  border-radius: 6px;
  border-left: 3px solid var(--brand-red);
}

.action-buttons-row {
  display: flex;
  gap: 12px;
  margin-top: 24px;
}

.mgt-response-section .description {
  color: var(--text-secondary);
  font-size: 13px;
  margin-bottom: 12px;
}

.mgt-textarea {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background-color: var(--surface-alt);
  color: var(--text-primary);
  resize: vertical;
  font-size: 14px;
  margin-bottom: 14px;
}

.response-actions {
  display: flex;
  justify-content: flex-end;
}

/* Modal and Form styling */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--overlay-backdrop);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: var(--surface);
  padding: 24px;
  border-radius: 12px;
  width: 90%;
  max-width: 600px;
  box-shadow: var(--shadow-modal);
  border: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-primary);
}

.form-textarea {
  width: 100%;
  padding: 10px;
  background-color: var(--surface-alt);
  color: var(--text-primary);
  border-radius: 8px;
  border: 1px solid var(--border);
  resize: vertical;
  font-size: 14px;
}

.form-group-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-select {
  padding: 10px;
  background-color: var(--surface-alt);
  color: var(--text-primary);
  border-radius: 8px;
  border: 1px solid var(--border);
  font-size: 14px;
  outline: none;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 10px;
}

@media (max-width: 900px) {
  .findings-grid {
    grid-template-columns: 1fr;
  }
}
</style>
