<script setup lang="ts">
import { onMounted, ref } from "vue";
import { CheckCheck, Loader2 } from "lucide-vue-next";

import AppButton from "@/components/ui/AppButton.vue";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { notificationsApi } = useLedgerScopeApi();
import PageHeader from "@/components/ui/PageHeader.vue";
import { useNotification } from "@/composables/useNotification";
import { useUiStore } from "@/stores/ui.store";
import { useUserNotificationStore } from "@/stores/user-notification.store";
import { navigateTo } from "@/router";
import type { DbNotification } from "@/types";

const ui = useUiStore();
const store = useUserNotificationStore();
const notification = useNotification();
const isMarkingAll = ref(false);

function openNotification(item: DbNotification): void {
  void store.markAsRead(item.id);
  if (item.data.action_url) {
    navigateTo(item.data.action_url);
  }
}

async function markAllRead(): Promise<void> {
  if (store.unreadCount === 0) return;
  isMarkingAll.value = true;
  try {
    await notificationsMarkAllRead();
    store.notifications.forEach((item) => {
      if (!item.read_at) item.read_at = new Date().toISOString();
    });
    store.unreadCount = 0;
    notification.success("All notifications marked as read.");
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to mark notifications as read.",
    );
  } finally {
    isMarkingAll.value = false;
  }
}

async function notificationsMarkAllRead(): Promise<void> {
  await notificationsApi.markAllRead();
}

async function load(): Promise<void> {
  await store.fetchNotifications(true);
}

onMounted(() => {
  ui.setBreadcrumbs(["Notifications"]);
  void load();
});
</script>

<template>
  <PageHeader
    title="Notifications"
    subtitle="Review alerts, approvals, requests, and workflow reminders."
  >
    <template #actions>
      <AppButton
        :icon="CheckCheck"
        :loading="isMarkingAll"
        :disabled="store.unreadCount === 0"
        @click="markAllRead"
      >
        Mark all read
      </AppButton>
    </template>
  </PageHeader>

  <section class="notification-panel" aria-live="polite">
    <div
      v-if="store.isLoading && store.notifications.length === 0"
      class="state"
    >
      <Loader2 class="spin" aria-hidden="true" />
      <p>Loading notifications...</p>
    </div>
    <div v-else-if="store.error" class="state state--error">
      <p>{{ store.error }}</p>
      <AppButton variant="secondary" @click="load">Retry</AppButton>
    </div>
    <div v-else-if="store.notifications.length === 0" class="state">
      <p>No notifications yet.</p>
    </div>
    <div v-else class="notification-list">
      <button
        v-for="item in store.notifications"
        :key="item.id"
        type="button"
        class="notification-item"
        :class="{ 'notification-item--unread': !item.read_at }"
        @click="openNotification(item)"
      >
        <span class="notification-item__heading">
          <strong>{{ item.data.title || "Notification" }}</strong>
          <span v-if="!item.read_at" class="unread-dot" aria-label="Unread" />
        </span>
        <span class="notification-item__message">{{ item.data.message }}</span>
        <time :datetime="item.created_at">
          {{ new Date(item.created_at).toLocaleString() }}
        </time>
      </button>
      <AppButton
        v-if="store.hasMore"
        :loading="store.isLoading"
        class="load-more"
        @click="store.fetchNotifications()"
      >
        Load more
      </AppButton>
    </div>
  </section>
</template>

<style scoped>
.notification-panel {
  margin-top: 24px;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--surface);
}

.state {
  display: grid;
  min-height: 220px;
  place-items: center;
  align-content: center;
  gap: 12px;
  color: var(--text-secondary);
  text-align: center;
}

.state p {
  margin: 0;
}

.state--error {
  color: var(--status-danger);
}

.notification-list {
  display: grid;
}

.notification-item {
  display: grid;
  gap: 8px;
  width: 100%;
  border: 0;
  border-bottom: 1px solid var(--border);
  background: var(--surface);
  padding: 18px 20px;
  color: var(--text-primary);
  text-align: left;
  cursor: pointer;
}

.notification-item:hover,
.notification-item:focus-visible {
  background: var(--surface-hover);
}

.notification-item--unread {
  background: var(--surface-alt);
}

.notification-item__heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.notification-item__message {
  color: var(--text-secondary);
}

time {
  color: var(--text-muted);
  font-size: 0.75rem;
}

.unread-dot {
  width: 8px;
  height: 8px;
  flex: 0 0 auto;
  border-radius: 50%;
  background: var(--brand-red);
}

.load-more {
  justify-self: center;
  margin: 16px;
}

.spin {
  width: 20px;
  height: 20px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
