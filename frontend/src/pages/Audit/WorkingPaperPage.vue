<script setup lang="ts">
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter as useVueRouter } from "vue-router";
import {
  Lock,
  Unlock,
  MessageSquare,
  AlertCircle,
  FileText,
  Send,
  Plus,
  CornerDownRight,
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
import type { UserSummary, WorkingPaper } from "@/types";

const route = useRoute();
const vueRouter = useVueRouter();
const ui = useUiStore();
const companyStore = useCompanyStore();
const notification = useNotification();
const confirmDialog = useConfirmDialog();

function parseRouteId(value: string | string[] | undefined): number | null {
  return typeof value === "string" && /^\d+$/.test(value)
    ? Number(value)
    : null;
}

const engagementId = computed(() => parseRouteId(route.params["id"]));
const wpId = computed(() => parseRouteId(route.params["wpId"]));

const isLoading = ref(true);
const loadError = ref<string | null>(null);
const wp = ref<WorkingPaper | null>(null);
const wpList = ref<WorkingPaper[]>([]);

// Review Notes State
const newNoteContent = ref("");
const replyMessages = ref<Record<number, string>>({});
const showUnlockModal = ref(false);
const unlockReason = ref("");

// Form Edit State
const isEditing = ref(false);
const editTitle = ref("");
const editContent = ref("");

async function loadData(): Promise<void> {
  isLoading.value = true;
  loadError.value = null;
  try {
    if (engagementId.value && wpId.value) {
      const data = await engagementApi.getWorkingPaper(
        engagementId.value,
        wpId.value,
      );
      wp.value = data;
      editTitle.value = data.title;
      editContent.value = data.content || "";
      ui.setBreadcrumbs([
        "Audit",
        "Working Papers",
        data.paper_ref || "Detail",
      ]);
    } else {
      // List view or redirect to first working paper
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
          const papers = await engagementApi.listWorkingPapers(firstEng.id);
          wpList.value = papers;
          if (papers.length > 0 && papers[0] && papers[0].id !== undefined) {
            void vueRouter.push(
              `/engagements/${firstEng.id}/working-papers/${papers[0].id}`,
            );
            return;
          }
        }
      }
      ui.setBreadcrumbs(["Audit", "Working Papers"]);
    }
  } catch (caught) {
    loadError.value =
      caught instanceof Error
        ? caught.message
        : "Failed to load working paper details.";
    notification.error("Failed to load working paper details.");
  } finally {
    isLoading.value = false;
  }
}

async function handleSignOff(): Promise<void> {
  if (!engagementId.value || !wpId.value) return;

  try {
    const data = await engagementApi.signOffWorkingPaper(
      engagementId.value,
      wpId.value,
    );
    wp.value = data;
    notification.success("Working paper signed off successfully.");
  } catch (error: unknown) {
    notification.error(
      error instanceof Error
        ? error.message
        : "Failed to sign off working paper.",
    );
  }
}

async function handleLock(): Promise<void> {
  if (!engagementId.value || !wpId.value) return;

  const confirmed = await confirmDialog.confirm({
    title: "Lock Working Paper",
    message:
      "Once locked, this working paper cannot be modified without documenting an unlock reason.",
    tone: "danger",
    confirmLabel: "Lock Working Paper",
  });
  if (!confirmed) return;

  try {
    const data = await engagementApi.lockWorkingPaper(
      engagementId.value,
      wpId.value,
    );
    wp.value = data;
    notification.success("Working paper locked.");
  } catch (error: unknown) {
    notification.error(
      error instanceof Error ? error.message : "Failed to lock working paper.",
    );
  }
}

async function handleUnlock(): Promise<void> {
  if (!engagementId.value || !wpId.value) return;

  if (unlockReason.value.length < 5) {
    notification.error("Please provide a reason of at least 5 characters.");
    return;
  }

  try {
    const data = await engagementApi.unlockWorkingPaper(
      engagementId.value,
      wpId.value,
    );
    wp.value = data;
    notification.success("Working paper unlocked.");
    showUnlockModal.value = false;
    unlockReason.value = "";
  } catch (error: unknown) {
    notification.error(
      error instanceof Error
        ? error.message
        : "Failed to unlock working paper.",
    );
  }
}

async function saveChanges(): Promise<void> {
  if (!engagementId.value || !wpId.value) return;

  try {
    const data = await engagementApi.updateWorkingPaper(
      engagementId.value,
      wpId.value,
      {
        title: editTitle.value,
        content: editContent.value,
      },
    );
    wp.value = data;
    isEditing.value = false;
    notification.success("Working paper updated successfully.");
  } catch (error: unknown) {
    notification.error(
      error instanceof Error ? error.message : "Failed to save changes.",
    );
  }
}

async function addReviewNote(): Promise<void> {
  if (!engagementId.value || !wpId.value) return;

  if (newNoteContent.value.trim().length < 5) {
    notification.error("Review note content must be at least 5 characters.");
    return;
  }

  try {
    await engagementApi.createReviewNote(engagementId.value, {
      working_paper_id: wpId.value,
      content: newNoteContent.value,
    });
    newNoteContent.value = "";
    notification.success("Review note added.");
    await loadData(); // Reload to fetch notes
  } catch (error: unknown) {
    notification.error(
      error instanceof Error ? error.message : "Failed to add review note.",
    );
  }
}

async function resolveNote(noteId: number): Promise<void> {
  if (!engagementId.value) return;

  try {
    await engagementApi.resolveReviewNote(engagementId.value, noteId);
    notification.success("Review note resolved.");
    await loadData();
  } catch (error: unknown) {
    notification.error(
      error instanceof Error ? error.message : "Failed to resolve review note.",
    );
  }
}

async function sendReply(noteId: number): Promise<void> {
  if (!engagementId.value) return;

  const msg = replyMessages.value[noteId] || "";
  if (msg.trim().length < 2) {
    notification.error("Reply must be at least 2 characters.");
    return;
  }

  try {
    await engagementApi.replyReviewNote(engagementId.value, noteId, msg);
    replyMessages.value[noteId] = "";
    notification.success("Reply posted.");
    await loadData();
  } catch (error: unknown) {
    notification.error(
      error instanceof Error ? error.message : "Failed to send reply.",
    );
  }
}

function displayUser(
  user: UserSummary | string | null | undefined,
  fallback = "Not assigned",
): string {
  if (typeof user === "string") return user;
  return user?.name ?? fallback;
}

watch([engagementId, wpId], () => {
  void loadData();
});

onMounted(() => {
  void loadData();
});
</script>

<template>
  <div v-if="isLoading" class="loading-state">
    <p>Loading working paper...</p>
  </div>

  <div v-else-if="loadError" class="empty-state">
    <p>{{ loadError }}</p>
  </div>

  <div v-else-if="!wp" class="empty-state">
    <SectionPanel title="Working Papers">
      <p>No active working paper selected.</p>
      <div v-if="wpList.length > 0" class="wp-list">
        <h4>Available Working Papers:</h4>
        <ul>
          <li v-for="paper in wpList" :key="paper.id">
            <router-link
              :to="`/engagements/${paper.engagement_id}/working-papers/${paper.id}`"
            >
              {{ paper.paper_ref }} — {{ paper.title }}
            </router-link>
          </li>
        </ul>
      </div>
    </SectionPanel>
  </div>

  <div v-else>
    <PageHeader
      :title="`${wp.paper_ref || 'WP'} ${wp.title}`"
      subtitle="Working paper audit trail, sign-off controls, and review notes."
    >
      <template #actions>
        <StatusBadge :status="wp.status" />
        <AppButton
          v-if="wp.is_locked"
          variant="secondary"
          :icon="Unlock"
          @click="showUnlockModal = true"
        >
          Unlock
        </AppButton>
        <AppButton v-else variant="secondary" :icon="Lock" @click="handleLock">
          Lock
        </AppButton>
        <AppButton
          v-if="!wp.is_locked && wp.status !== 'reviewed'"
          variant="primary"
          @click="handleSignOff"
        >
          Sign Off Working Paper
        </AppButton>
      </template>
    </PageHeader>

    <div v-if="wp.is_locked" class="lock-banner">
      <AlertCircle class="icon" />
      <span
        >This working paper is locked. Modifying content requires
        unlocking.</span
      >
    </div>

    <section class="wp-grid">
      <div class="main-column">
        <SectionPanel title="Working Paper content">
          <template #actions>
            <AppButton
              v-if="!isEditing && !wp.is_locked"
              variant="secondary"
              size="sm"
              @click="isEditing = true"
            >
              Edit Content
            </AppButton>
            <div v-else-if="isEditing" class="edit-actions">
              <AppButton
                variant="secondary"
                size="sm"
                @click="isEditing = false"
                >Cancel</AppButton
              >
              <AppButton variant="primary" size="sm" @click="saveChanges"
                >Save</AppButton
              >
            </div>
          </template>

          <div v-if="!isEditing" class="wp-content-view">
            <h3>Audit Content</h3>
            <p class="content-text">
              {{ wp.content || "No content written yet." }}
            </p>
          </div>

          <div v-else class="wp-content-edit">
            <AppInput v-model="editTitle" label="Title" required />
            <div class="textarea-wrapper">
              <label>Content</label>
              <textarea
                v-model="editContent"
                rows="10"
                class="strategy-textarea"
              ></textarea>
            </div>
          </div>
        </SectionPanel>

        <!-- Evidence section -->
        <SectionPanel title="Evidence Files" :icon="FileText">
          <div
            v-if="wp.evidence_files && wp.evidence_files.length > 0"
            class="evidence-list"
          >
            <div
              v-for="file in wp.evidence_files"
              :key="file.id"
              class="evidence-item"
            >
              <span class="file-name">{{
                file.original_name ?? file.file_name
              }}</span>
              <span class="file-meta"
                >({{ (file.file_size_bytes / 1024).toFixed(1) }} KB)</span
              >
              <StatusBadge :status="file.status ?? 'unknown'" />
            </div>
          </div>
          <p v-else class="text-muted">
            No evidence files linked to this working paper.
          </p>
        </SectionPanel>
      </div>

      <div class="sidebar-column">
        <SectionPanel title="Sign-off Trail">
          <div class="trail-item">
            <span class="trail-label">Prepared By:</span>
            <span class="trail-value">{{ displayUser(wp.prepared_by) }}</span>
          </div>
          <div v-if="wp.reviewed_by" class="trail-item">
            <span class="trail-label">Reviewed By:</span>
            <span class="trail-value">{{ displayUser(wp.reviewed_by) }}</span>
          </div>
          <div v-if="wp.reviewed_at" class="trail-item">
            <span class="trail-label">Reviewed At:</span>
            <span class="trail-value">{{
              new Date(wp.reviewed_at).toLocaleString()
            }}</span>
          </div>
        </SectionPanel>

        <!-- Review Notes -->
        <SectionPanel title="Review Notes" :icon="MessageSquare">
          <div class="review-notes-list">
            <div
              v-for="note in wp.review_notes"
              :key="note.id"
              class="note-card"
            >
              <div class="note-header">
                <span class="note-author">{{
                  note.created_by?.name || "User"
                }}</span>
                <StatusBadge :status="note.status" />
              </div>
              <p class="note-text">{{ note.content }}</p>

              <!-- Nested replies -->
              <div
                v-if="note.replies && note.replies.length > 0"
                class="replies-list"
              >
                <div
                  v-for="reply in note.replies"
                  :key="reply.id"
                  class="reply-item"
                >
                  <CornerDownRight class="reply-icon" />
                  <div class="reply-body">
                    <span class="reply-author"
                      >{{ reply.user?.name || "User" }}:</span
                    >
                    <span class="reply-text">{{ reply.message }}</span>
                  </div>
                </div>
              </div>

              <!-- Reply form -->
              <div class="reply-form">
                <input
                  v-model="replyMessages[note.id]"
                  type="text"
                  placeholder="Write a reply..."
                  @keyup.enter="sendReply(note.id)"
                />
                <button @click="sendReply(note.id)">
                  <Send class="send-icon" />
                </button>
              </div>

              <div v-if="note.status === 'open'" class="note-actions">
                <AppButton
                  variant="secondary"
                  size="sm"
                  @click="resolveNote(note.id)"
                >
                  Resolve Note
                </AppButton>
              </div>
            </div>
          </div>

          <!-- Add new note -->
          <div class="add-note-form">
            <h4>Add Review Note</h4>
            <textarea
              v-model="newNoteContent"
              placeholder="New review note details..."
              rows="3"
            ></textarea>
            <AppButton
              variant="secondary"
              size="sm"
              :icon="Plus"
              @click="addReviewNote"
              >Add Note</AppButton
            >
          </div>
        </SectionPanel>
      </div>
    </section>

    <!-- Unlock Modal -->
    <div v-if="showUnlockModal" class="modal-overlay">
      <div class="modal-content">
        <h3>Unlock Working Paper</h3>
        <p>Document the reason for unlocking this working paper.</p>
        <textarea
          v-model="unlockReason"
          rows="4"
          placeholder="Reason for unlocking..."
        ></textarea>
        <div class="modal-buttons">
          <AppButton variant="secondary" @click="showUnlockModal = false"
            >Cancel</AppButton
          >
          <AppButton variant="primary" @click="handleUnlock"
            >Unlock Paper</AppButton
          >
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.loading-state,
.empty-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 250px;
  color: var(--text-secondary);
}

.wp-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 24px;
}

.main-column,
.sidebar-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.lock-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  background-color: var(--status-danger-bg);
  border: 1px solid var(--status-danger);
  border-radius: 8px;
  padding: 12px 16px;
  margin-bottom: 24px;
  color: var(--text-primary);
}

.lock-banner .icon {
  color: var(--status-danger);
}

.edit-actions {
  display: flex;
  gap: 8px;
}

.wp-content-view h3 {
  margin: 0 0 12px;
  font-size: 16px;
  font-weight: 600;
}

.content-text {
  white-space: pre-wrap;
  color: var(--text-secondary);
  line-height: 1.6;
}

.textarea-wrapper {
  margin-top: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.strategy-textarea {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background-color: var(--surface);
  color: var(--text-primary);
  resize: vertical;
}

.evidence-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.evidence-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px;
  border: 1px solid var(--border);
  border-radius: 6px;
  background-color: var(--surface);
}

.file-name {
  font-weight: 500;
  color: var(--text-primary);
}

.file-meta {
  font-size: 12px;
  color: var(--text-muted);
}

.trail-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid var(--border);
}

.trail-label {
  color: var(--text-muted);
}

.trail-value {
  font-weight: 500;
}

.review-notes-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
  margin-bottom: 20px;
}

.note-card {
  padding: 14px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background-color: var(--surface);
}

.note-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.note-author {
  font-weight: 600;
  font-size: 13px;
}

.note-text {
  margin: 0 0 12px;
  font-size: 14px;
  color: var(--text-secondary);
}

.note-actions {
  display: flex;
  justify-content: flex-end;
}

.replies-list {
  margin-top: 12px;
  padding-left: 12px;
  border-left: 2px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 12px;
}

.reply-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
}

.reply-icon {
  width: 14px;
  height: 14px;
  color: var(--text-muted);
  margin-top: 3px;
}

.reply-body {
  font-size: 13px;
}

.reply-author {
  font-weight: 600;
  margin-right: 6px;
}

.reply-text {
  color: var(--text-secondary);
}

.reply-form {
  display: flex;
  align-items: center;
  gap: 8px;
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 4px 8px;
  background-color: var(--surface);
  margin-top: 8px;
}

.reply-form input {
  flex: 1;
  border: none;
  background: transparent;
  color: var(--text-primary);
  font-size: 13px;
  outline: none;
}

.reply-form button {
  border: none;
  background: transparent;
  color: var(--brand-red);
  cursor: pointer;
  padding: 4px;
}

.send-icon {
  width: 14px;
  height: 14px;
}

.add-note-form {
  margin-top: 24px;
  border-top: 1px solid var(--border);
  padding-top: 16px;
}

.add-note-form h4 {
  margin: 0 0 12px;
  font-size: 14px;
}

.add-note-form textarea {
  width: 100%;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background-color: var(--surface);
  color: var(--text-primary);
  margin-bottom: 12px;
  resize: none;
}

/* Modal Styling */
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
  max-width: 500px;
  box-shadow: var(--shadow-modal);
  border: 1px solid var(--border);
}

.modal-content h3 {
  margin-top: 0;
}

.modal-content textarea {
  width: 100%;
  padding: 12px;
  background-color: var(--surface);
  color: var(--text-primary);
  border-radius: 8px;
  border: 1px solid var(--border);
  margin: 16px 0;
  resize: none;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

@media (max-width: 1024px) {
  .wp-grid {
    grid-template-columns: 1fr;
  }
}
</style>
