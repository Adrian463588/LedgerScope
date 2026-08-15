<script setup lang="ts">
import { LockKeyhole, Mail } from "lucide-vue-next";
import { onMounted, ref } from "vue";

import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import { useNotification } from "@/composables/useNotification";
import { navigateTo } from "@/router";
import { loginSchema } from "@/schemas/auth.schema";
import { useAuthStore } from "@/stores/auth.store";

// ─── State ────────────────────────────────────────────────────────────────────
const auth = useAuthStore();
const notification = useNotification();
const email = ref("");
const password = ref("");
const error = ref("");

onMounted(() => {
  if (auth.bootstrapError) {
    error.value = `LedgerScope is unavailable: ${auth.bootstrapError}`;
  }
});

async function submit(): Promise<void> {
  const parsed = loginSchema.safeParse({
    email: email.value,
    password: password.value,
    remember: true,
  });
  if (!parsed.success) {
    error.value =
      parsed.error.issues[0]?.message ?? "Check your login details.";
    return;
  }

  try {
    await auth.login(
      parsed.data.email,
      parsed.data.password,
      parsed.data.remember,
    );
    if (auth.mfaRequired) {
      notification.info("MFA verification required.");
      navigateTo("/mfa");
    } else {
      notification.success("Login successful.");
      navigateTo("/dashboard");
    }
  } catch {
    error.value = "Invalid credentials. Please check your email and password.";
    notification.error("Login failed. Please try again.");
  }
}
</script>

<template>
  <form class="auth-card" @submit.prevent="submit">
    <!-- Header -->
    <div class="auth-card__header">
      <div class="auth-card__logo-mark" aria-hidden="true">
        <span>L</span>
      </div>
      <div>
        <h1>Sign in to LedgerScope</h1>
        <p>Use your audit workspace credentials.</p>
      </div>
    </div>

    <!-- Fields -->
    <AppInput
      v-model="email"
      label="Email address"
      type="email"
      required
      autocomplete="email"
      :error="error && !email ? error : ''"
    />
    <AppInput
      v-model="password"
      label="Password"
      type="password"
      required
      autocomplete="current-password"
      :error="error"
    />
    <p v-if="auth.bootstrapError" class="auth-error" role="alert">
      The backend session could not be checked. Verify the API is running and
      try again.
    </p>

    <AppButton
      variant="primary"
      type="submit"
      size="lg"
      :loading="auth.isLoading"
      :icon="LockKeyhole"
      >Sign In</AppButton
    >

    <button
      type="button"
      class="mfa-link"
      @click="navigateTo('/forgot-password')"
    >
      Forgot your password?
    </button>

    <button type="button" class="mfa-link" @click="navigateTo('/mfa')">
      <Mail aria-hidden="true" />
      Verify with MFA instead
    </button>
  </form>
</template>

<style scoped>
.auth-card {
  display: grid;
  width: min(440px, 100%);
  gap: 20px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: white;
  padding: 36px 32px;
  box-shadow: var(--shadow-card);
}

/* ── Header ─────────────────────────────────────────────────── */
.auth-card__header {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  margin-bottom: 4px;
}

.auth-card__logo-mark {
  display: grid;
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  place-items: center;
  border-radius: 8px;
  background: var(--brand-red);
  color: white;
  font-weight: 700;
  font-size: 1.125rem;
  letter-spacing: -0.03em;
}

h1 {
  margin: 0;
  font-family: "DM Serif Display", Georgia, serif;
  font-size: 1.75rem;
  line-height: 1.15;
  color: var(--text-primary);
}

p {
  margin: 4px 0 0;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.auth-error {
  margin: -8px 0 0;
  color: var(--status-danger);
  font-size: 0.8125rem;
}

/* ── MFA link ───────────────────────────────────────────────── */
.mfa-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 0;
  background: transparent;
  color: var(--brand-red);
  font-size: 0.875rem;
  cursor: pointer;
}

.mfa-link svg {
  width: 16px;
  height: 16px;
}
</style>
