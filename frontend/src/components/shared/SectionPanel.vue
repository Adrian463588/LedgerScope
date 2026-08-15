<script setup lang="ts">
withDefaults(
  defineProps<{
    title?: string;
    subtitle?: string;
  }>(),
  {
    title: "",
    subtitle: "",
  },
);

const slots = defineSlots<{
  default?: () => unknown;
  actions?: () => unknown;
}>();
</script>

<template>
  <section class="section-panel">
    <header v-if="title || slots.actions">
      <div>
        <h2 v-if="title">{{ title }}</h2>
        <p v-if="subtitle">{{ subtitle }}</p>
      </div>
      <div v-if="slots.actions" class="actions">
        <slot name="actions" />
      </div>
    </header>
    <slot />
  </section>
</template>

<style scoped>
.section-panel {
  display: grid;
  gap: 18px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: white;
  padding: 24px;
  box-shadow: var(--shadow-card);
}

header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

h2,
p {
  margin: 0;
}

h2 {
  font-size: 1rem;
  font-weight: 700;
}

p {
  margin-top: 4px;
  color: var(--text-muted);
  font-size: 0.875rem;
}

.actions {
  display: flex;
  gap: 8px;
}
</style>
