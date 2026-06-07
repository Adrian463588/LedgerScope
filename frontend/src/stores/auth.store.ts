import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

import { authApi, initCsrf } from '@/api/endpoints';
import { navigateTo } from '@/router';
import type { AuthUser } from '@/types';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null);
  const mfaRequiredEmail = ref<string | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const isAuthenticated = computed(() => user.value !== null);
  const mfaRequired = computed(() => mfaRequiredEmail.value !== null);
  const permissions = computed(() => user.value?.permissions ?? []);
  const userInitials = computed(() => {
    if (!user.value?.name) return '??';
    return user.value.name
      .split(' ')
      .slice(0, 2)
      .map((n) => n[0])
      .join('')
      .toUpperCase();
  });

  /**
   * Login with Sanctum SPA flow:
   * 1. Fetch CSRF cookie so Laravel can accept the subsequent POST.
   * 2. POST credentials → backend returns UserResource or MFA required status.
   */
  async function login(email: string, password: string, remember: boolean): Promise<void> {
    isLoading.value = true;
    error.value = null;
    mfaRequiredEmail.value = null;
    try {
      await initCsrf();
      const res = await authApi.login({ email, password, remember });
      if ('mfa_required' in res && res.mfa_required) {
        mfaRequiredEmail.value = res.email;
      } else {
        user.value = res as AuthUser;
      }
    } catch (caught) {
      error.value = caught instanceof Error ? caught.message : 'Login failed.';
      throw caught;
    } finally {
      isLoading.value = false;
    }
  }

  /** Verify MFA code and authenticate */
  async function verifyMfa(code: string): Promise<void> {
    isLoading.value = true;
    error.value = null;
    try {
      user.value = await authApi.verifyMfa({ code });
      mfaRequiredEmail.value = null;
    } catch (caught) {
      error.value = caught instanceof Error ? caught.message : 'MFA verification failed.';
      throw caught;
    } finally {
      isLoading.value = false;
    }
  }

  /** Re-hydrate user state from the active session (on hard refresh). */
  async function fetchMe(): Promise<void> {
    if (isLoading.value) return;
    try {
      user.value = await authApi.me();
    } catch {
      // 401 means not authenticated — that is fine, leave user null.
      user.value = null;
    }
  }

  async function logout(): Promise<void> {
    try {
      await authApi.logout();
    } finally {
      reset();
      navigateTo('/login');
    }
  }

  function reset(): void {
    user.value = null;
    error.value = null;
    mfaRequiredEmail.value = null;
  }

  return { user, mfaRequiredEmail, mfaRequired, permissions, isAuthenticated, userInitials, isLoading, error, login, logout, verifyMfa, fetchMe, reset };
});
