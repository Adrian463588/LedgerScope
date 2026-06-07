<script setup lang="ts">
import { LockKeyhole, Mail } from 'lucide-vue-next';
import { ref } from 'vue';

import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';
import { useNotification } from '@/composables/useNotification';
import { navigateTo } from '@/router';
import { loginSchema } from '@/schemas/auth.schema';
import { useAuthStore } from '@/stores/auth.store';

// ─── Demo accounts (Phase 1.5 seeder credentials) ────────────────────────────
interface DemoAccount {
  email: string;
  password: string;
  role: string;
  name: string;
  initials: string;
}

const DEMO_ACCOUNTS: DemoAccount[] = [
  {
    email: 'superadmin@ledgerscope.test',
    password: 'Admin@LedgerScope2026!',
    role: 'Super Admin',
    name: 'Super Admin',
    initials: 'SA',
  },
  {
    email: 'rina@ledgerscope.test',
    password: 'password',
    role: 'Firm Admin',
    name: 'Rina Sari',
    initials: 'RS',
  },
];

// ─── State ────────────────────────────────────────────────────────────────────
const auth = useAuthStore();
const notification = useNotification();
const email = ref('');
const password = ref('');
const error = ref('');
const selectedDemo = ref<DemoAccount | null>(null);

function fillDemo(account: DemoAccount): void {
  selectedDemo.value = account;
  email.value = account.email;
  password.value = account.password;
  error.value = '';
}

async function submit(): Promise<void> {
  const parsed = loginSchema.safeParse({ email: email.value, password: password.value, remember: true });
  if (!parsed.success) {
    error.value = parsed.error.issues[0]?.message ?? 'Check your login details.';
    return;
  }

  try {
    await auth.login(parsed.data.email, parsed.data.password, parsed.data.remember);
    if (auth.mfaRequired) {
      notification.info('MFA verification required.');
      navigateTo('/mfa');
    } else {
      notification.success('Login successful.');
      navigateTo('/dashboard');
    }
  } catch {
    error.value = 'Invalid credentials. Please check your email and password.';
    notification.error('Login failed. Please try again.');
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

    <AppButton
      variant="primary"
      type="submit"
      size="lg"
      :loading="auth.isLoading"
      :icon="LockKeyhole"
    >Sign In</AppButton>

    <!-- Demo accounts panel -->
    <div class="demo-section">
      <p class="demo-section__label">Demo accounts — click to fill credentials</p>
      <div class="demo-accounts">
        <button
          v-for="account in DEMO_ACCOUNTS"
          :key="account.email"
          type="button"
          class="demo-account"
          :class="{ 'demo-account--active': selectedDemo?.email === account.email }"
          @click="fillDemo(account)"
        >
          <span class="demo-account__avatar" aria-hidden="true">{{ account.initials }}</span>
          <span class="demo-account__body">
            <span class="demo-account__name">{{ account.name }}</span>
            <span class="demo-account__email">{{ account.email }}</span>
          </span>
          <span class="demo-account__badge">{{ account.role }}</span>
        </button>
      </div>
    </div>

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
  font-family: 'DM Serif Display', Georgia, serif;
  font-size: 1.75rem;
  line-height: 1.15;
  color: var(--text-primary);
}

p {
  margin: 4px 0 0;
  font-size: 0.875rem;
  color: var(--text-secondary);
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

/* ── Demo section ───────────────────────────────────────────── */
.demo-section {
  border-top: 1px solid var(--border);
  padding-top: 18px;
}

.demo-section__label {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: var(--text-muted);
  margin: 0 0 10px;
}

.demo-accounts {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

/* ── Individual demo account button ─────────────────────────── */
.demo-account {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 12px;
  background: var(--surface-alt);
  border: 1px solid var(--border);
  border-radius: 7px;
  cursor: pointer;
  transition:
    border-color 130ms ease,
    background 130ms ease,
    transform 80ms ease;
  text-align: left;
}

.demo-account:hover {
  border-color: var(--border-strong);
  background: var(--surface-hover);
}

.demo-account:active {
  transform: scale(0.99);
}

.demo-account--active {
  border-color: var(--brand-red);
  background: rgba(192, 25, 10, 0.05);
}

.demo-account__avatar {
  display: grid;
  flex-shrink: 0;
  width: 32px;
  height: 32px;
  place-items: center;
  border-radius: 50%;
  background: var(--surface);
  border: 1px solid var(--border);
  font: 600 0.7rem 'IBM Plex Mono', monospace;
  color: var(--text-secondary);
}

.demo-account--active .demo-account__avatar {
  border-color: var(--brand-red);
  color: var(--brand-red);
  background: rgba(192, 25, 10, 0.08);
}

.demo-account__body {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 1px;
  min-width: 0;
}

.demo-account__name {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
}

.demo-account__email {
  font-size: 0.75rem;
  color: var(--text-muted);
  font-family: 'IBM Plex Mono', monospace;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.demo-account__badge {
  flex-shrink: 0;
  font-size: 0.7rem;
  color: var(--text-muted);
  background: var(--surface);
  border: 1px solid var(--border);
  padding: 2px 8px;
  border-radius: 4px;
}
</style>
