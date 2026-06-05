import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import { authApi } from '@/api/endpoints';
import type { AuthUser } from '@/types';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const permissions = computed(() => user.value?.permissions ?? []);

  async function login(email: string, password: string, remember: boolean): Promise<void> {
    isLoading.value = true;
    error.value = null;
    try {
      user.value = await authApi.login({ email, password, remember });
    } catch (caught) {
      error.value = caught instanceof Error ? caught.message : 'Login failed.';
      throw caught;
    } finally {
      isLoading.value = false;
    }
  }

  async function logout(): Promise<void> {
    try {
      await authApi.logout();
    } finally {
      reset();
      window.history.pushState({}, '', '/login');
      window.dispatchEvent(new PopStateEvent('popstate'));
    }
  }

  function reset(): void {
    user.value = null;
    error.value = null;
  }

  return { user, permissions, isLoading, error, login, logout, reset };
});
