import { defineStore } from 'pinia';
import { ref } from 'vue';
import { notificationsApi } from '@/api/endpoints';

export interface DbNotification {
  id: string;
  type: string;
  data: {
    title: string;
    message: string;
    type: string;
    action_url?: string;
  };
  read_at: string | null;
  created_at: string;
}

export const useUserNotificationStore = defineStore('user-notification', () => {
  const notifications = ref<DbNotification[]>([]);
  const unreadCount = ref<number>(0);
  const isLoading = ref<boolean>(false);
  const page = ref<number>(1);
  const hasMore = ref<boolean>(false);

  async function fetchNotifications(reset = false): Promise<void> {
    if (isLoading.value && !reset) return;
    isLoading.value = true;
    try {
      if (reset) {
        page.value = 1;
        notifications.value = [];
      }
      const data = await notificationsApi.list(page.value);
      
      const items = data.data ?? [];
      notifications.value = reset ? items : [...notifications.value, ...items];
      
      unreadCount.value = notifications.value.filter(n => !n.read_at).length;
      hasMore.value = !!data.next_page_url;
      if (hasMore.value) {
        page.value += 1;
      }
    } catch (err) {
      console.error('Failed to fetch notifications:', err);
    } finally {
      isLoading.value = false;
    }
  }

  async function markAsRead(id: string): Promise<void> {
    try {
      await notificationsApi.markRead(id);
      const index = notifications.value.findIndex(n => n.id === id);
      if (index !== -1 && notifications.value[index]) {
        notifications.value[index].read_at = new Date().toISOString();
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
    } catch (err) {
      console.error('Failed to mark notification as read:', err);
    }
  }

  return {
    notifications,
    unreadCount,
    isLoading,
    hasMore,
    fetchNotifications,
    markAsRead,
  };
});
