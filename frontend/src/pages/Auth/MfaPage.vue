<script setup lang="ts">
import { ShieldCheck } from "lucide-vue-next";
import { ref } from "vue";

import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import { useNotification } from "@/composables/useNotification";
import { navigateTo } from "@/router";
import { mfaSchema } from "@/schemas/auth.schema";

import { useAuthStore } from "@/stores/auth.store";

const auth = useAuthStore();
const code = ref("");
const error = ref("");
const isLoading = ref(false);
const notification = useNotification();

async function verify(): Promise<void> {
  const parsed = mfaSchema.safeParse({ code: code.value });
  if (!parsed.success) {
    error.value =
      parsed.error.issues[0]?.message ?? "Enter the verification code.";
    return;
  }

  isLoading.value = true;
  error.value = "";
  try {
    await auth.verifyMfa(parsed.data.code);
    notification.success("MFA verified.");
    navigateTo("/dashboard");
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Invalid MFA code.";
    notification.error("Verification failed. Please try again.");
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <form class="auth-card" @submit.prevent="verify">
    <ShieldCheck class="icon" aria-hidden="true" />
    <h2>Verify your access</h2>
    <p>Enter the 6-digit code from your authenticator app.</p>
    <AppInput
      v-model="code"
      label="Verification code"
      required
      :error="error"
      placeholder="123456"
    />
    <AppButton variant="primary" type="submit" :loading="isLoading"
      >Verify</AppButton
    >
  </form>
</template>

<style scoped>
.auth-card {
  display: grid;
  width: min(420px, 100%);
  gap: 18px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: white;
  padding: 32px;
}

.icon {
  width: 40px;
  height: 40px;
  color: var(--brand-red);
}

h2 {
  margin: 0;
  font-family: "DM Serif Display", Georgia, serif;
  font-size: 2rem;
}

p {
  margin: 0;
  color: var(--text-secondary);
}
</style>
