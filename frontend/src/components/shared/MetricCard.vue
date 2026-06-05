<script setup lang="ts">
import type { StatusTone } from '@/types';

defineProps<{
  label: string;
  value: string;
  trend: string;
  tone: StatusTone;
}>();
</script>

<template>
  <article class="metric-card" :class="`metric-card--${tone}`">
    <p>{{ label }}</p>
    <strong>{{ value }}</strong>
    <span>{{ trend }}</span>
  </article>
</template>

<style scoped>
.metric-card {
  display: grid;
  gap: 8px;
  border: 1px solid var(--border);
  border-left: 3px solid var(--brand-red);
  border-radius: 8px;
  background: white;
  padding: 20px;
  box-shadow: var(--shadow-card);
}

p {
  margin: 0;
  color: var(--text-muted);
  font-size: 0.6875rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

strong {
  font-family: 'DM Serif Display', Georgia, serif;
  font-size: 1.875rem;
  font-weight: 600;
}

span {
  color: var(--status-success);
  font-family: 'IBM Plex Mono', monospace;
  font-size: 0.8125rem;
}

.metric-card--warning span {
  color: var(--status-warning);
}

.metric-card--danger span {
  color: var(--status-danger);
}

.metric-card--info span,
.metric-card--neutral span {
  color: var(--status-info);
}
</style>
