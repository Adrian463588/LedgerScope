<script setup lang="ts">
import { computed, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { authApi } = useLedgerScopeApi();
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import { navigateTo, router } from "@/router";

const token = computed(() =>
  String(router.currentRoute.value.params["token"] ?? ""),
);
const name = ref("");
const password = ref("");
const confirmation = ref("");
const error = ref<string | null>(null);
const message = ref<string | null>(null);
const isLoading = ref(false);

async function submit(): Promise<void> {
  if (
    !token.value ||
    !name.value.trim() ||
    password.value.length < 8 ||
    password.value !== confirmation.value
  ) {
    error.value =
      "Enter your name and matching passwords of at least 8 characters.";
    return;
  }

  isLoading.value = true;
  error.value = null;
  try {
    await authApi.acceptInvitation(token.value, {
      name: name.value.trim(),
      password: password.value,
      password_confirmation: confirmation.value,
    });
    message.value = "Invitation accepted. You can now sign in.";
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Unable to accept invitation.";
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <form class="auth-card" @submit.prevent="submit">
    <h1>Join LedgerScope</h1>
    <AppInput v-model="name" label="Full name" required :error="error ?? ''" />
    <AppInput v-model="password" label="Password" type="password" required />
    <AppInput
      v-model="confirmation"
      label="Confirm password"
      type="password"
      required
    />
    <p v-if="message" class="message">{{ message }}</p>
    <AppButton variant="primary" type="submit" :loading="isLoading"
      >Accept invitation</AppButton
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
