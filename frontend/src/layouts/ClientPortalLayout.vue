<script setup lang="ts">
import { navigateTo } from "@/router";
import NotificationBell from "@/components/shared/notification-bell.vue";
</script>

<template>
  <main class="client-layout">
    <header>
      <button @click="navigateTo('/dashboard')">LedgerScope</button>
      <div class="header-right">
        <NotificationBell />
        <span>Client Evidence Portal</span>
      </div>
    </header>
    <section class="page-content">
      <slot />
    </section>
  </main>
</template>

<style scoped>
.client-layout {
  min-height: 100vh;
  background: var(--page-bg);
}

header {
  display: flex;
  height: 56px;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  background: white;
  padding: 0 32px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

button {
  border: 0;
  background: transparent;
  color: var(--brand-red);
  font-family: "DM Serif Display", Georgia, serif;
  font-size: 1.5rem;
}

span {
  color: var(--text-secondary);
  font-weight: 600;
}

section {
  width: min(1120px, 100%);
  margin: 0 auto;
  padding: 32px;
}
</style>
