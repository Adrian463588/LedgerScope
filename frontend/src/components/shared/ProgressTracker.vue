<script setup lang="ts">
defineProps<{
  label: string;
  value: number;
}>();
</script>

<template>
  <div class="progress-tracker">
    <span>{{ label }}</span>
    <strong>{{ value }}%</strong>
    <div><i :style="{ width: `${value}%` }" /></div>
  </div>
</template>

<style scoped>
.progress-tracker {
  display: grid;
  gap: 8px;
}

span {
  color: var(--text-secondary);
}

strong {
  font-family: 'IBM Plex Mono', monospace;
}

div {
  height: 8px;
  overflow: hidden;
  border-radius: 999px;
  background: var(--surface-active);
}

i {
  display: block;
  height: 100%;
  background: var(--brand-red);
}
</style>
