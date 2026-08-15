<script setup lang="ts">
import { ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { authApi } = useLedgerScopeApi();
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import { navigateTo } from "@/router";
import { forgotPasswordSchema } from "@/schemas/auth.schema";

const email = ref("");
const error = ref<string | null>(null);
const message = ref<string | null>(null);
const isLoading = ref(false);

async function submit(): Promise<void> {
  const parsed = forgotPasswordSchema.safeParse({ email: email.value });
  if (!parsed.success) {
    error.value = parsed.error.issues[0]?.message ?? "Enter a valid email.";
    return;
  }

  isLoading.value = true;
  error.value = null;
  message.value = null;
  try {
    await authApi.forgotPassword(parsed.data.email);
    message.value = "If the email is registered, a reset link has been sent.";
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Unable to send reset link.";
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <form class="auth-card" @submit.prevent="submit">
    <h1>Reset your password</h1>
    <p>Enter your account email and we will send a reset link.</p>
    <AppInput
      v-model="email"
      label="Email address"
      type="email"
      required
      :error="error ?? ''"
    />
    <p v-if="message" class="message">{{ message }}</p>
    <AppButton variant="primary" type="submit" :loading="isLoading"
      >Send reset link</AppButton
    >
    <button type="button" class="link" @click="navigateTo('/login')">
      Back to sign in
    </button>
  </form>
</template>

<style scoped>
.auth-card {
  display: grid;
  width: min(440px, 100%);
  gap: 18px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: white;
  padding: 36px 32px;
  box-shadow: var(--shadow-card);
}
h1 {
  margin: 0;
  font-family: "DM Serif Display", Georgia, serif;
  font-size: 1.75rem;
  color: var(--text-primary);
}
p {
  margin: 0;
  color: var(--text-secondary);
}
.message {
  color: var(--status-success);
}
.link {
  border: 0;
  background: transparent;
  color: var(--brand-red);
  cursor: pointer;
}
</style>
