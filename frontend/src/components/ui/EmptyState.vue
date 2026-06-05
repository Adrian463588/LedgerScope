<script setup lang="ts">
import type { Component } from 'vue';

withDefaults(
  defineProps<{
    icon?: Component;
    title: string;
    body: string;
  }>(),
  {
    icon: undefined,
  },
);

const slots = defineSlots<{
  default?: () => unknown;
}>();
</script>

<template>
  <section class="empty-state">
    <component :is="icon" v-if="icon" class="empty-state__icon" aria-hidden="true" />
    <h2>{{ title }}</h2>
    <p>{{ body }}</p>
    <div v-if="slots.default" class="empty-state__actions">
      <slot />
    </div>
  </section>
</template>

<style scoped>
.empty-state {
  display: grid;
  gap: 12px;
  justify-items: center;
  border: 1px dashed var(--border-strong);
  border-radius: 8px;
  background: white;
  padding: 40px 24px;
  text-align: center;
}

.empty-state__icon {
  width: 48px;
  height: 48px;
  color: var(--text-muted);
}

h2 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
}

p {
  max-width: 48ch;
  margin: 0;
  color: var(--text-secondary);
}

.empty-state__actions {
  display: flex;
  gap: 8px;
}
</style>
