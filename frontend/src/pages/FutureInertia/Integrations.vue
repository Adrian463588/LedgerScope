<script setup lang="ts">
import type { ExternalIntegrationStatus } from "@/types";

defineProps<{
  statuses: ExternalIntegrationStatus[];
}>();
</script>

<template>
  <main class="future-page">
    <section class="future-card" aria-labelledby="future-title">
      <p class="future-eyebrow">LedgerScope / Future modules</p>
      <h1 id="future-title">External integrations</h1>
      <p class="future-lede">
        Provider adapters are registered with fail-closed behavior. An adapter
        is unavailable until its credentials and sandbox contract are valid.
      </p>

      <div v-if="statuses.length === 0" class="future-state">
        No integration adapters are registered.
      </div>

      <div v-else class="future-grid">
        <article
          v-for="status in statuses"
          :key="status.key"
          class="future-integration"
          :data-configured="status.configured"
        >
          <div>
            <h2>{{ status.key }}</h2>
            <p>{{ status.message }}</p>
          </div>
          <span
            class="future-status"
            :class="
              status.configured
                ? 'future-status--ready'
                : 'future-status--unavailable'
            "
          >
            {{ status.configured ? "Configured" : "Unavailable" }}
          </span>
        </article>
      </div>
    </section>
  </main>
</template>

<style scoped>
.future-page {
  min-height: 100vh;
  padding: 48px 24px;
  background: var(--page-bg);
  color: var(--text-primary);
}

.future-card {
  width: min(960px, 100%);
  margin: 0 auto;
  padding: 32px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  box-shadow: var(--shadow-card);
}

.future-eyebrow {
  margin: 0 0 8px;
  color: var(--brand-red);
  font: 600 0.75rem/1.4 var(--font-mono);
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

h1,
h2,
p {
  margin-top: 0;
}

h1 {
  margin-bottom: 12px;
  font: 400 2rem/1.2 var(--font-display);
}

.future-lede {
  max-width: 720px;
  margin-bottom: 28px;
  color: var(--text-secondary);
}

.future-state {
  padding: 20px;
  color: var(--text-secondary);
  background: var(--surface-alt);
  border: 1px solid var(--border);
  border-radius: 6px;
}

.future-grid {
  display: grid;
  gap: 12px;
}

.future-integration {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
  padding: 18px;
  border: 1px solid var(--border);
  border-radius: 6px;
}

.future-integration h2 {
  margin-bottom: 6px;
  font-size: 1rem;
  font-weight: 600;
}

.future-integration p {
  margin-bottom: 0;
  color: var(--text-secondary);
}

.future-status {
  flex: 0 0 auto;
  padding: 4px 8px;
  border-radius: 4px;
  font: 600 0.75rem/1.3 var(--font-mono);
}

.future-status--ready {
  color: var(--status-success);
  background: var(--status-success-bg);
}

.future-status--unavailable {
  color: var(--status-warning);
  background: var(--status-warning-bg);
}

@media (max-width: 640px) {
  .future-page {
    padding: 24px 16px;
  }

  .future-card {
    padding: 20px;
  }

  .future-integration {
    display: grid;
    gap: 12px;
  }
}
</style>
