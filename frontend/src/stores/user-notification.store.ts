import { defineStore } from "pinia";
import { ref } from "vue";
import { notificationsApi } from "@/api/endpoints";
import type { DbNotification } from "@/types";

export const useUserNotificationStore = defineStore("user-notification", () => {
  const notifications = ref<DbNotification[]>([]);
  const unreadCount = ref<number>(0);
  const isLoading = ref<boolean>(false);
  const page = ref<number>(1);
  const hasMore = ref<boolean>(false);
  const error = ref<string | null>(null);

  async function fetchNotifications(reset = false): Promise<void> {
    if (isLoading.value && !reset) return;
    isLoading.value = true;
    error.value = null;
    try {
      if (reset) {
        page.value = 1;
        notifications.value = [];
      }
      const pageResult = await notificationsApi.list(page.value);

      const items = pageResult.items;
      notifications.value = reset ? items : [...notifications.value, ...items];

      unreadCount.value = notifications.value.filter((n) => !n.read_at).length;
      hasMore.value = pageResult.meta.current_page < pageResult.meta.last_page;
      if (hasMore.value) {
        page.value += 1;
      }
    } catch (caught) {
      error.value =
        caught instanceof Error
          ? caught.message
          : "Unable to load notifications.";
    } finally {
      isLoading.value = false;
    }
  }

  async function markAsRead(id: string): Promise<void> {
    try {
      await notificationsApi.markRead(id);
      const index = notifications.value.findIndex((n) => n.id === id);
      if (index !== -1 && notifications.value[index]) {
        notifications.value[index].read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
    } catch (caught) {
      error.value =
        caught instanceof Error
          ? caught.message
          : "Unable to mark notification as read.";
    }
  }

  return {
    notifications,
    unreadCount,
    isLoading,
    hasMore,
    error,
    fetchNotifications,
    markAsRead,
  };
});
