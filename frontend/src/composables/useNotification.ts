import { storeToRefs } from 'pinia';

import { useNotificationStore } from '@/stores/notification.store';

export function useNotification() {
  const store = useNotificationStore();
  const { notifications } = storeToRefs(store);

  return {
    notifications,
    success: store.success,
    error: store.error,
    info: store.info,
    remove: store.remove,
  };
}
