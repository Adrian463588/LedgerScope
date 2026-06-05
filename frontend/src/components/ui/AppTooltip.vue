<script setup lang="ts">
defineProps<{
  text: string;
}>();
</script>

<template>
  <span class="tooltip">
    <slot />
    <span role="tooltip">{{ text }}</span>
  </span>
</template>

<style scoped>
.tooltip {
  position: relative;
  display: inline-flex;
}

[role='tooltip'] {
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  z-index: 30;
  width: max-content;
  max-width: 220px;
  transform: translateX(-50%);
  border-radius: 4px;
  background: var(--shell-elevated);
  color: white;
  opacity: 0;
  padding: 6px 8px;
  pointer-events: none;
  transition: opacity 140ms ease;
}

.tooltip:hover [role='tooltip'] {
  opacity: 1;
}
</style>
