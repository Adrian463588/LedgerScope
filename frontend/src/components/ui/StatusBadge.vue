<script setup lang="ts">
import { computed } from 'vue';

import type { StatusTone } from '@/types';

const props = defineProps<{
  status: string;
}>();

const tone = computed<StatusTone>(() => {
  const status = props.status.toLowerCase();
  if (['posted', 'approved', 'accepted', 'completed', 'balanced', 'ready'].includes(status)) return 'success';
  if (['draft', 'planning', 'submitted', 'under review', 'review'].includes(status)) return 'neutral';
  if (['fieldwork', 'generating', 'medium', 'in progress'].includes(status)) return 'warning';
  if (['rejected', 'failed', 'critical', 'high', 'overdue', 'unbalanced'].includes(status)) return 'danger';
  if (['locked', 'archived'].includes(status)) return 'locked';
  return 'info';
});
</script>

<template>
  <span class="status-badge" :class="`status-badge--${tone}`">{{ props.status }}</span>
</template>

<style scoped>
.status-badge {
  display: inline-flex;
  align-items: center;
  border: 1px solid;
  border-radius: 999px;
  padding: 2px 8px;
  font: 600 0.6875rem/1rem 'IBM Plex Sans', sans-serif;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  transition: background 200ms, color 200ms;
}

.status-badge--neutral {
  background: var(--status-neutral-bg);
  border-color: var(--status-neutral-border);
  color: var(--status-neutral);
}

.status-badge--success {
  background: var(--status-success-bg);
  border-color: var(--status-success-border);
  color: var(--status-success);
}

.status-badge--warning {
  background: var(--status-warning-bg);
  border-color: var(--status-warning-border);
  color: var(--status-warning);
}

.status-badge--danger {
  background: var(--status-danger-bg);
  border-color: var(--status-danger-border);
  color: var(--status-danger);
}

.status-badge--info {
  background: var(--status-info-bg);
  border-color: var(--status-info-border);
  color: var(--status-info);
}

.status-badge--locked {
  background: var(--status-locked-bg);
  border-color: var(--status-locked-border);
  color: var(--status-locked);
}
</style>
