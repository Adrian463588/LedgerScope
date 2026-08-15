<script setup lang="ts">
defineProps<{
  eyebrow?: string;
  title: string;
  subtitle?: string;
}>();

const slots = defineSlots<{
  actions?: () => unknown;
}>();
</script>

<template>
  <header class="page-header">
    <div>
      <p v-if="eyebrow" class="page-header__eyebrow">{{ eyebrow }}</p>
      <h1>{{ title }}</h1>
      <p v-if="subtitle" class="page-header__subtitle">{{ subtitle }}</p>
    </div>
    <div v-if="slots.actions" class="page-header__actions">
      <slot name="actions" />
    </div>
  </header>
</template>

<style scoped>
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
}

.page-header__eyebrow {
  margin: 0 0 4px;
  color: var(--brand-red);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  color: var(--text-primary);
  font-family: "DM Serif Display", Georgia, serif;
  font-size: clamp(2rem, 4vw, 2.5rem);
  font-weight: 600;
  line-height: 1.05;
}

.page-header__subtitle {
  margin: 8px 0 0;
  color: var(--text-secondary);
}

.page-header__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

@media (max-width: 768px) {
  .page-header {
    display: grid;
  }
}
</style>
