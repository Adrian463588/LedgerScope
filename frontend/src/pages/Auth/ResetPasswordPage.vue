<script setup lang="ts">
import { onMounted, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { authApi } = useLedgerScopeApi();
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import { navigateTo } from "@/router";
import { resetPasswordSchema } from "@/schemas/auth.schema";

const email = ref("");
const token = ref("");
const password = ref("");
const confirmation = ref("");
const error = ref<string | null>(null);
const message = ref<string | null>(null);
const isLoading = ref(false);

onMounted(() => {
  const params = new URLSearchParams(window.location.search);
  token.value = params.get("token") ?? "";
  email.value = params.get("email") ?? "";
});

async function submit(): Promise<void> {
  const parsed = resetPasswordSchema.safeParse({
    token: token.value,
    email: email.value,
    password: password.value,
    password_confirmation: confirmation.value,
  });
  if (!parsed.success) {
    error.value = parsed.error.issues[0]?.message ?? "Invalid reset form.";
    return;
  }

  isLoading.value = true;
  error.value = null;
  try {
    await authApi.resetPassword(parsed.data);
    message.value = "Password reset successful. You can now sign in.";
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Unable to reset password.";
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <form class="auth-card" @submit.prevent="submit">
    <h1>Choose a new password</h1>
    <AppInput
      v-model="email"
      label="Email address"
      type="email"
      required
      :error="error ?? ''"
    />
    <AppInput
      v-model="password"
      label="New password"
      type="password"
      required
    />
    <AppInput
      v-model="confirmation"
      label="Confirm password"
      type="password"
      required
    />
    <p v-if="message" class="message">{{ message }}</p>
    <AppButton variant="primary" type="submit" :loading="isLoading"
      >Reset password</AppButton
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
