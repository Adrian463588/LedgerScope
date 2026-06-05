<script setup lang="ts">
import { LockKeyhole, Mail } from 'lucide-vue-next';
import { ref } from 'vue';

import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';
import { useNotification } from '@/composables/useNotification';
import { navigateTo } from '@/router';
import { loginSchema } from '@/schemas/auth.schema';
import { useAuthStore } from '@/stores/auth.store';

const auth = useAuthStore();
const notification = useNotification();
const email = ref('admin@ledgerscope.test');
const password = ref('password');
const error = ref('');

async function submit(): Promise<void> {
  const parsed = loginSchema.safeParse({ email: email.value, password: password.value, remember: true });
  if (!parsed.success) {
    error.value = parsed.error.issues[0]?.message ?? 'Check your login details.';
    return;
  }

  try {
    await auth.login(parsed.data.email, parsed.data.password, parsed.data.remember);
    notification.success('Login successful.');
    navigateTo('/dashboard');
  } catch {
    error.value = 'Could not reach the auth API. Demo session remains available.';
    notification.info('Using demo session while backend auth is unavailable.');
    navigateTo('/dashboard');
  }
}
</script>

<template>
  <form class="auth-card" @submit.prevent="submit">
    <div>
      <h2>Sign in to LedgerScope</h2>
      <p>Use your audit workspace credentials.</p>
    </div>
    <AppInput v-model="email" label="Email" type="email" required :error="error && !email ? error : ''" />
    <AppInput v-model="password" label="Password" type="password" required :error="error" />
    <AppButton variant="primary" type="submit" size="lg" :loading="auth.isLoading" :icon="LockKeyhole">Sign In</AppButton>
    <button type="button" class="mfa-link" @click="navigateTo('/mfa')"><Mail aria-hidden="true" /> Verify with MFA instead</button>
  </form>
</template>

<style scoped>
.auth-card {
  display: grid;
  width: min(420px, 100%);
  gap: 20px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: white;
  padding: 32px;
  box-shadow: var(--shadow-card);
}

h2 {
  margin: 0;
  font-family: 'DM Serif Display', Georgia, serif;
  font-size: 2rem;
}

p {
  margin: 6px 0 0;
  color: var(--text-secondary);
}

.mfa-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border: 0;
  background: transparent;
  color: var(--brand-red);
}

.mfa-link svg {
  width: 16px;
  height: 16px;
}
</style>
