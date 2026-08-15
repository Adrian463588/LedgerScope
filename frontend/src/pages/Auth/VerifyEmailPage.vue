<script setup lang="ts">
import { onMounted, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { authApi } = useLedgerScopeApi();
import AppButton from "@/components/ui/AppButton.vue";
import { navigateTo, router } from "@/router";

const status = ref("Verifying your email...");
const isLoading = ref(true);

onMounted(async () => {
  const token = String(router.currentRoute.value.params["token"] ?? "");
  try {
    await authApi.verifyEmail(token);
    status.value = "Email verified successfully. You can now sign in.";
  } catch (caught) {
    status.value =
      caught instanceof Error ? caught.message : "Unable to verify email.";
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <section class="auth-card">
    <h1>Email verification</h1>
    <p>{{ status }}</p>
    <AppButton
      variant="primary"
      :loading="isLoading"
      @click="navigateTo('/login')"
      >Continue to sign in</AppButton
    >
  </section>
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
</style>
