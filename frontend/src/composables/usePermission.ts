import { computed } from 'vue';

import { useAuthStore } from '@/stores/auth.store';

export function usePermission() {
  const auth = useAuthStore();
  const permissions = computed(() => new Set(auth.permissions));

  function can(permission: string): boolean {
    return permissions.value.has(permission) || permissions.value.has('*');
  }

  function canAny(required: string[]): boolean {
    return required.some((permission) => can(permission));
  }

  function canAll(required: string[]): boolean {
    return required.every((permission) => can(permission));
  }

  return { can, canAny, canAll };
}
