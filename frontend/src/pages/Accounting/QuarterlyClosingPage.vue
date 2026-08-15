<script setup lang="ts">
import {
  Lock,
  Unlock,
  CheckCircle,
  Circle,
  AlertCircle,
} from "lucide-vue-next";
import { onMounted, ref, watch, computed } from "vue";

import ProgressTracker from "@/components/shared/ProgressTracker.vue";
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppModal from "@/components/ui/AppModal.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import { useNotification } from "@/composables/useNotification";
import { useUiStore } from "@/stores/ui.store";
import { useCompanyStore } from "@/stores/company.store";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { accountingApi } = useLedgerScopeApi();
import type { ChecklistItem, FiscalYear, Quarter } from "@/types";

const ui = useUiStore();
const companyStore = useCompanyStore();
const notification = useNotification();

const fiscalYears = ref<FiscalYear[]>([]);
const selectedFiscalYearId = ref<number | null>(null);
const quarters = ref<Quarter[]>([]);
const selectedQuarterId = ref<number | null>(null);
const selectedQuarter = computed(
  () => quarters.value.find((q) => q.id === selectedQuarterId.value) || null,
);

const checklist = ref<ChecklistItem[]>([]);
const isLoading = ref(false);

const lockModalOpen = ref(false);
const unlockModalOpen = ref(false);
const confirmText = ref("");
const unlockReason = ref("");
const fiscalYearModalOpen = ref(false);
const fiscalYearInput = ref(String(new Date().getFullYear()));

const completionPercentage = computed(() => {
  if (checklist.value.length === 0) return 0;
  const completed = checklist.value.filter((item) => item.is_completed).length;
  return Math.round((completed / checklist.value.length) * 100);
});

async function loadFiscalYears() {
  if (!companyStore.activeCompanyId) return;
  try {
    isLoading.value = true;
    fiscalYears.value = await accountingApi.fiscalYears(
      companyStore.activeCompanyId,
    );
    const firstFiscalYear = fiscalYears.value[0];
    if (firstFiscalYear) {
      selectedFiscalYearId.value = firstFiscalYear.id;
    }
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Failed to load fiscal years.",
    );
  } finally {
    isLoading.value = false;
  }
}

async function loadQuarters() {
  if (!companyStore.activeCompanyId || !selectedFiscalYearId.value) return;
  try {
    isLoading.value = true;
    quarters.value = await accountingApi.quarters(
      companyStore.activeCompanyId,
      selectedFiscalYearId.value,
    );
    const firstQuarter = quarters.value[0];
    if (firstQuarter) {
      selectedQuarterId.value = firstQuarter.id;
    } else {
      selectedQuarterId.value = null;
      checklist.value = [];
    }
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Failed to load quarters.",
    );
  } finally {
    isLoading.value = false;
  }
}

async function loadChecklist() {
  if (!companyStore.activeCompanyId || !selectedQuarterId.value) return;
  try {
    checklist.value = await accountingApi.getQuarterChecklist(
      companyStore.activeCompanyId,
      selectedQuarterId.value,
    );
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Failed to load checklist.",
    );
  }
}

async function toggleChecklistItem(item: ChecklistItem): Promise<void> {
  if (!companyStore.activeCompanyId || !selectedQuarterId.value) return;
  const newStatus = !item.is_completed;
  try {
    const updated = await accountingApi.updateQuarterChecklist(
      companyStore.activeCompanyId,
      selectedQuarterId.value,
      item.checklist_key,
      { is_completed: newStatus, notes: item.notes ?? undefined },
    );
    item.is_completed = updated.is_completed;
    item.completed_at = updated.completed_at;
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Failed to update checklist item.",
    );
  }
}

async function handleUpdateNotes(
  item: ChecklistItem,
  notes: string | null,
): Promise<void> {
  if (!companyStore.activeCompanyId || !selectedQuarterId.value) return;
  try {
    await accountingApi.updateQuarterChecklist(
      companyStore.activeCompanyId,
      selectedQuarterId.value,
      item.checklist_key,
      { is_completed: item.is_completed, notes: notes ?? undefined },
    );
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Failed to update notes.",
    );
  }
}

async function lockQuarter() {
  if (!companyStore.activeCompanyId || !selectedQuarterId.value) return;
  try {
    const updated = await accountingApi.lockQuarter(
      companyStore.activeCompanyId,
      selectedQuarterId.value,
    );
    if (selectedQuarter.value) {
      selectedQuarter.value.is_locked = updated.is_locked;
      selectedQuarter.value.status = updated.status;
    }
    lockModalOpen.value = false;
    confirmText.value = "";
    notification.success("Quarter locked successfully.");
    await loadChecklist();
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Failed to lock quarter.",
    );
  }
}

async function unlockQuarter() {
  if (!companyStore.activeCompanyId || !selectedQuarterId.value) return;
  if (unlockReason.value.length < 10) {
    notification.error("Unlock reason must be at least 10 characters.");
    return;
  }
  try {
    const updated = await accountingApi.unlockQuarter(
      companyStore.activeCompanyId,
      selectedQuarterId.value,
      unlockReason.value,
    );
    if (selectedQuarter.value) {
      selectedQuarter.value.is_locked = updated.is_locked;
      selectedQuarter.value.status = updated.status;
    }
    unlockModalOpen.value = false;
    unlockReason.value = "";
    notification.success("Quarter unlocked successfully.");
    await loadChecklist();
  } catch (caught) {
    notification.error(
      caught instanceof Error ? caught.message : "Failed to unlock quarter.",
    );
  }
}

async function createFiscalYear(): Promise<void> {
  if (!companyStore.activeCompanyId) return;
  const year = Number(fiscalYearInput.value);
  if (!Number.isInteger(year) || year < 2000 || year > 2100) {
    notification.error("Enter a fiscal year between 2000 and 2100.");
    return;
  }

  try {
    await accountingApi.createFiscalYear(companyStore.activeCompanyId, year);
    fiscalYearModalOpen.value = false;
    notification.success("Fiscal year created.");
    await loadFiscalYears();
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to create fiscal year.",
    );
  }
}

watch(
  () => companyStore.activeCompanyId,
  () => {
    loadFiscalYears();
  },
);

watch(selectedFiscalYearId, () => {
  loadQuarters();
});

watch(selectedQuarterId, () => {
  loadChecklist();
});

onMounted(async () => {
  ui.setBreadcrumbs(["Accounting", "Quarterly Closing"]);
  if (!companyStore.activeCompanyId) {
    await companyStore.fetchCompanies();
  }
  await loadFiscalYears();
});
</script>

<template>
  <PageHeader
    :title="
      selectedQuarter
        ? `Quarterly Closing - ${selectedQuarter.quarter_name}`
        : 'Quarterly Closing'
    "
    subtitle="Guided checklist before financial statement lock."
  >
    <template #actions>
      <template v-if="selectedQuarter">
        <StatusBadge
          :status="selectedQuarter.is_locked ? 'Locked' : 'In Progress'"
        />
        <AppButton
          v-if="!selectedQuarter.is_locked"
          variant="primary"
          :icon="Lock"
          @click="lockModalOpen = true"
        >
          Lock Quarter
        </AppButton>
        <AppButton
          v-else
          variant="danger"
          :icon="Unlock"
          @click="unlockModalOpen = true"
        >
          Unlock Quarter
        </AppButton>
      </template>
      <AppButton v-else variant="primary" @click="fiscalYearModalOpen = true">
        Create fiscal year
      </AppButton>
    </template>
  </PageHeader>

  <div class="filter-bar">
    <div class="form-group">
      <label>Fiscal Year</label>
      <select v-model="selectedFiscalYearId" :disabled="isLoading">
        <option v-for="fy in fiscalYears" :key="fy.id" :value="fy.id">
          {{ fy.year }}
        </option>
      </select>
    </div>
    <div class="form-group">
      <label>Quarter</label>
      <select
        v-model="selectedQuarterId"
        :disabled="isLoading || quarters.length === 0"
      >
        <option v-for="q in quarters" :key="q.id" :value="q.id">
          {{ q.quarter_name }}
        </option>
      </select>
    </div>
  </div>

  <div v-if="quarters.length === 0 && !isLoading" class="empty-state">
    <AlertCircle class="empty-icon" />
    <h3>No Quarters Found</h3>
    <p>Configure a Fiscal Year first to generate quarters.</p>
  </div>

  <section v-else class="closing-grid">
    <SectionPanel title="Closing Checklist">
      <div v-if="checklist.length === 0" class="no-checklist">
        No checklist items found for this quarter.
      </div>
      <div v-else class="checklist-container">
        <div
          v-for="item in checklist"
          :key="item.id"
          class="checklist-item"
          :class="{ 'item-completed': item.is_completed }"
        >
          <button
            type="button"
            class="checkbox-btn"
            :disabled="selectedQuarter?.is_locked"
            @click="toggleChecklistItem(item)"
          >
            <CheckCircle
              v-if="item.is_completed"
              class="check-icon completed"
            />
            <Circle v-else class="check-icon pending" />
          </button>
          <div class="item-details">
            <div class="item-title">
              {{ item.checklist_name || item.checklist_key }}
              <span v-if="item.is_required" class="required-badge"
                >Required</span
              >
            </div>
            <div class="item-desc">{{ item.description }}</div>
            <input
              v-model="item.notes"
              type="text"
              class="notes-input"
              placeholder="Add notes..."
              :disabled="selectedQuarter?.is_locked"
              @change="handleUpdateNotes(item, item.notes)"
            />
          </div>
        </div>
        <ProgressTracker
          label="Checklist Progress"
          :value="completionPercentage"
        />
      </div>
    </SectionPanel>

    <SectionPanel title="Quarterly Summary" v-if="selectedQuarter">
      <div class="summary-card">
        <div class="summary-row">
          <span class="summary-label">Status</span>
          <span class="summary-val font-semibold">{{
            selectedQuarter.is_locked ? "Locked" : "Open for Posting"
          }}</span>
        </div>
        <div v-if="selectedQuarter.locked_at" class="summary-row">
          <span class="summary-label">Locked Date</span>
          <span class="summary-val">{{
            new Date(selectedQuarter.locked_at).toLocaleDateString()
          }}</span>
        </div>
        <div
          v-if="selectedQuarter.unlock_reason"
          class="summary-row unlock-reason-row"
        >
          <span class="summary-label">Last Unlock Reason</span>
          <span class="summary-val italic"
            >"{{ selectedQuarter.unlock_reason }}"</span
          >
        </div>
      </div>
    </SectionPanel>
  </section>

  <!-- Lock Modal -->
  <AppModal
    :open="lockModalOpen"
    :title="`Lock Quarter ${selectedQuarter?.quarter_name}`"
    @close="lockModalOpen = false"
  >
    <p class="modal-body-text">
      This action will lock all accounting periods within this quarter. No
      further journal entries can be posted. Type "LOCK" to confirm.
    </p>
    <AppInput v-model="confirmText" label="Confirmation" placeholder="LOCK" />
    <template #footer>
      <AppButton @click="lockModalOpen = false">Cancel</AppButton>
      <AppButton
        variant="danger"
        :disabled="confirmText !== 'LOCK'"
        @click="lockQuarter"
        >Lock Now</AppButton
      >
    </template>
  </AppModal>

  <AppModal
    :open="fiscalYearModalOpen"
    title="Create fiscal year"
    @close="fiscalYearModalOpen = false"
  >
    <AppInput v-model="fiscalYearInput" label="Fiscal year" type="number" />
    <template #footer>
      <AppButton @click="fiscalYearModalOpen = false">Cancel</AppButton>
      <AppButton variant="primary" @click="createFiscalYear">
        Create fiscal year
      </AppButton>
    </template>
  </AppModal>

  <!-- Unlock Modal -->
  <AppModal
    :open="unlockModalOpen"
    :title="`Unlock Quarter ${selectedQuarter?.quarter_name}`"
    @close="unlockModalOpen = false"
  >
    <p class="modal-body-text">
      Unlocking is highly restricted and requires auditing a justification.
      Please state the reason for unlocking (min 10 chars):
    </p>
    <AppInput
      v-model="unlockReason"
      label="Unlock Reason"
      placeholder="Enter justification..."
    />
    <template #footer>
      <AppButton @click="unlockModalOpen = false">Cancel</AppButton>
      <AppButton
        variant="danger"
        :disabled="unlockReason.length < 10"
        @click="unlockQuarter"
        >Unlock Now</AppButton
      >
    </template>
  </AppModal>
</template>

<style scoped>
.filter-bar {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
  background: var(--surface);
  padding: 16px;
  border-radius: 8px;
  border: 1px solid var(--border);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text-secondary);
}

.form-group select {
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid var(--border);
  background: var(--surface-alt);
  color: var(--text-primary);
  min-width: 150px;
}

.closing-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 20px;
}

.checklist-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.checklist-item {
  display: flex;
  gap: 12px;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: var(--surface);
  transition: all 0.2s ease;
}

.checklist-item:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-card);
}

.item-completed {
  border-color: var(--status-success-border);
  background: var(--status-success-bg);
}

.checkbox-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: flex-start;
  margin-top: 2px;
}

.check-icon {
  width: 20px;
  height: 20px;
  transition: color 0.2s ease;
}

.check-icon.completed {
  color: var(--status-success);
}

.check-icon.pending {
  color: var(--text-secondary);
}

.item-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex-grow: 1;
}

.item-title {
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.required-badge {
  font-size: 0.7rem;
  padding: 2px 6px;
  background: var(--status-danger-bg);
  color: var(--status-danger);
  border-radius: 4px;
  font-weight: 500;
}

.item-desc {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.notes-input {
  margin-top: 8px;
  padding: 6px 10px;
  border-radius: 4px;
  border: 1px solid var(--border);
  background: var(--surface-alt);
  color: var(--text-primary);
  font-size: 0.85rem;
  width: 100%;
}

.summary-card {
  display: flex;
  flex-direction: column;
  gap: 16px;
  background: var(--surface);
  padding: 20px;
  border-radius: 8px;
  border: 1px solid var(--border);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  padding-bottom: 12px;
}

.summary-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.unlock-reason-row {
  flex-direction: column;
  gap: 6px;
}

.summary-label {
  color: var(--text-secondary);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px;
  background: var(--surface);
  border-radius: 8px;
  border: 1px solid var(--border);
  text-align: center;
}

.empty-icon {
  width: 48px;
  height: 48px;
  color: var(--text-secondary);
  margin-bottom: 16px;
}

.modal-body-text {
  font-size: 0.95rem;
  color: var(--text-secondary);
  margin-bottom: 16px;
  line-height: 1.5;
}
</style>
