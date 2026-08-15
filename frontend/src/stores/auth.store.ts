import { defineStore } from "pinia";
import { computed, ref } from "vue";

import { authApi, initCsrf } from "@/api/endpoints";
import { getApiError } from "@/api/client";
import { navigateTo } from "@/router";
import type { AuthUser } from "@/types";
import type { LoginResponse } from "@/api/endpoints";

function isMfaLoginResponse(
  response: AuthUser | LoginResponse,
): response is LoginResponse {
  return "mfa_required" in response && response.mfa_required === true;
}

export const useAuthStore = defineStore("auth", () => {
  const user = ref<AuthUser | null>(null);
  const mfaRequiredEmail = ref<string | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const bootstrapError = ref<string | null>(null);
  const isHydrated = ref(false);
  let fetchMePromise: Promise<void> | null = null;

  const isAuthenticated = computed(() => user.value !== null);
  const mfaRequired = computed(() => mfaRequiredEmail.value !== null);
  const permissions = computed(() => user.value?.permissions ?? []);
  const userInitials = computed(() => {
    if (!user.value?.name) return "??";
    return user.value.name
      .split(" ")
      .slice(0, 2)
      .map((n) => n[0])
      .join("")
      .toUpperCase();
  });

  /**
   * Login with Sanctum SPA flow:
   * 1. Fetch CSRF cookie so Laravel can accept the subsequent POST.
   * 2. POST credentials → backend returns UserResource or MFA required status.
   */
  async function login(
    email: string,
    password: string,
    remember: boolean,
  ): Promise<void> {
    isLoading.value = true;
    error.value = null;
    bootstrapError.value = null;
    mfaRequiredEmail.value = null;
    try {
      await initCsrf();
      const res = await authApi.login({ email, password, remember });
      if (isMfaLoginResponse(res)) {
        mfaRequiredEmail.value = res.email;
      } else {
        user.value = res;
      }
      isHydrated.value = true;
    } catch (caught) {
      error.value = caught instanceof Error ? caught.message : "Login failed.";
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
      error.value =
        caught instanceof Error ? caught.message : "MFA verification failed.";
      throw caught;
    } finally {
      isLoading.value = false;
    }
  }

  /** Re-hydrate user state from the active session (on hard refresh). */
  async function fetchMe(): Promise<void> {
    if (isHydrated.value) return;
    if (fetchMePromise) return fetchMePromise;

    fetchMePromise = (async () => {
      isLoading.value = true;
      bootstrapError.value = null;
      try {
        user.value = await authApi.me();
      } catch (caught) {
        const apiError = getApiError(caught);
        // A 401 during bootstrap is an unauthenticated session, not a fatal app error.
        user.value = null;
        if (
          apiError.code !== "unauthorized" &&
          apiError.code !== "session_expired"
        ) {
          error.value = apiError.message;
          bootstrapError.value = apiError.message;
        }
      } finally {
        isHydrated.value = true;
        isLoading.value = false;
      }
    })();

    try {
      await fetchMePromise;
    } finally {
      fetchMePromise = null;
    }
  }

  async function logout(): Promise<void> {
    try {
      await authApi.logout();
    } finally {
      reset();
      navigateTo("/login");
    }
  }

  function reset(): void {
    user.value = null;
    error.value = null;
    bootstrapError.value = null;
    mfaRequiredEmail.value = null;
    isHydrated.value = false;
  }

  return {
    user,
    mfaRequiredEmail,
    mfaRequired,
    permissions,
    isAuthenticated,
    userInitials,
    isLoading,
    error,
    bootstrapError,
    isHydrated,
    login,
    logout,
    verifyMfa,
    fetchMe,
    reset,
  };
});
