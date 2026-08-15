import { defineStore } from "pinia";
import { ref } from "vue";

export interface NotificationItem {
  id: number;
  type: "success" | "error" | "info";
  message: string;
}

export const useNotificationStore = defineStore("notification", () => {
  const notifications = ref<NotificationItem[]>([]);
  let nextId = 1;

  function push(type: NotificationItem["type"], message: string): void {
    const item = { id: nextId, type, message };
    nextId += 1;
    notifications.value.push(item);
    window.setTimeout(() => remove(item.id), 3600);
  }

  function success(message: string): void {
    push("success", message);
  }

  function error(message: string): void {
    push("error", message);
  }

  function info(message: string): void {
    push("info", message);
  }

  function remove(id: number): void {
    notifications.value = notifications.value.filter((item) => item.id !== id);
  }

  function reset(): void {
    notifications.value = [];
  }

  return { notifications, success, error, info, remove, reset };
});
