<script setup lang="ts">
import { onMounted, onUnmounted, ref } from "vue";
import { Bell, Loader2 } from "lucide-vue-next";
import { navigateTo } from "@/router";
import { useUserNotificationStore } from "@/stores/user-notification.store";
import type { DbNotification } from "@/types";

const store = useUserNotificationStore();
const isOpen = ref(false);
const bellRef = ref<HTMLElement | null>(null);

function toggleDropdown(): void {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    void store.fetchNotifications(true);
  }
}

function handleNotificationClick(item: DbNotification): void {
  void store.markAsRead(item.id);
  isOpen.value = false;
  if (item.data?.action_url) {
    navigateTo(item.data.action_url);
  }
}

function closeDropdown(e: MouseEvent): void {
  if (bellRef.value && !bellRef.value.contains(e.target as Node)) {
    isOpen.value = false;
  }
}

onMounted(() => {
  void store.fetchNotifications(true);
  const interval = window.setInterval(() => {
    void store.fetchNotifications(true);
  }, 30000);

  document.addEventListener("click", closeDropdown);

  onUnmounted(() => {
    window.clearInterval(interval);
    document.removeEventListener("click", closeDropdown);
  });
});
</script>

<template>
  <div ref="bellRef" class="notification-bell">
    <button
      class="icon-button"
      aria-label="Notifications"
      :class="{ active: isOpen }"
      @click="toggleDropdown"
    >
      <Bell aria-hidden="true" />
      <span v-if="store.unreadCount > 0" class="badge">{{
        store.unreadCount
      }}</span>
    </button>

    <Transition name="slide-up">
      <div v-if="isOpen" class="dropdown">
        <div class="dropdown-header">
          <h3>Notifications</h3>
          <span v-if="store.unreadCount > 0" class="unread-tag"
            >{{ store.unreadCount }} new</span
          >
        </div>
        <button
          class="view-all"
          type="button"
          @click="
            navigateTo('/notifications');
            isOpen = false;
          "
        >
          View all notifications
        </button>

        <div class="dropdown-content">
          <div
            v-if="store.isLoading && store.notifications.length === 0"
            class="loading-state"
          >
            <Loader2 class="animate-spin text-red" aria-hidden="true" />
            <p>Loading notifications...</p>
          </div>
          <div v-else-if="store.notifications.length === 0" class="empty-state">
            <p>All caught up! No notifications.</p>
          </div>
          <div v-else class="notification-list">
            <div
              v-for="item in store.notifications"
              :key="item.id"
              class="notification-item"
              :class="{ unread: !item.read_at }"
              @click="handleNotificationClick(item)"
            >
              <div class="item-header">
                <span class="item-title">{{
                  item.data?.title || "Notification"
                }}</span>
                <span v-if="!item.read_at" class="unread-dot"></span>
              </div>
              <p class="item-message">{{ item.data?.message }}</p>
              <span class="item-time">
                {{ new Date(item.created_at).toLocaleDateString() }}
              </span>
            </div>
            <button
              v-if="store.hasMore"
              class="load-more-btn"
              :disabled="store.isLoading"
              @click.stop="store.fetchNotifications()"
            >
              <Loader2
                v-if="store.isLoading"
                class="animate-spin"
                aria-hidden="true"
              />
              <span v-else>Load More</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.notification-bell {
  position: relative;
  display: inline-block;
}

.icon-button {
  position: relative;
  display: grid;
  width: 36px;
  height: 36px;
  place-items: center;
  border: 0;
  border-radius: 4px;
  background: transparent;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.icon-button:hover,
.icon-button.active {
  background: var(--surface-hover);
  color: var(--text-primary);
}

.badge {
  position: absolute;
  top: 3px;
  right: 3px;
  display: grid;
  width: 16px;
  height: 16px;
  place-items: center;
  border-radius: 50%;
  background: var(--brand-red);
  color: white;
  font:
    500 0.625rem "IBM Plex Mono",
    monospace;
}

.dropdown {
  position: absolute;
  top: 44px;
  right: 0;
  z-index: 100;
  width: 320px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: white;
  box-shadow: var(--shadow-dropdown);
}

.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  padding: 12px 16px;
}

.dropdown-header h3 {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.unread-tag {
  font-size: 0.75rem;
  font-weight: 500;
  color: white;
  background: var(--brand-red);
  padding: 2px 6px;
  border-radius: 9999px;
}

.dropdown-content {
  max-height: 360px;
  overflow-y: auto;
}

.loading-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
  text-align: center;
  color: var(--text-secondary);
}

.loading-state p,
.empty-state p {
  margin: 8px 0 0 0;
  font-size: 0.875rem;
}

.notification-list {
  display: flex;
  flex-direction: column;
}

.notification-item {
  border-bottom: 1px solid var(--border);
  padding: 12px 16px;
  cursor: pointer;
  transition: background 0.15s ease;
}

.notification-item:last-child {
  border-bottom: 0;
}

.notification-item:hover {
  background: var(--surface-hover);
}

.notification-item.unread {
  background: var(--surface-alt);
}

.item-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
}

.item-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-primary);
}

.unread-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--brand-red);
}

.item-message {
  margin: 0 0 6px 0;
  font-size: 0.75rem;
  color: var(--text-secondary);
  line-height: 1.4;
  word-break: break-word;
}

.item-time {
  font-size: 0.6875rem;
  color: var(--text-muted);
}

.load-more-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  border: 0;
  border-top: 1px solid var(--border);
  background: white;
  padding: 10px;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
}

.load-more-btn:hover {
  background: var(--surface-hover);
  color: var(--text-primary);
}

.animate-spin {
  animation: spin 1s linear infinite;
  width: 14px;
  height: 14px;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.2s ease-out;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(10px);
  opacity: 0;
}
</style>
