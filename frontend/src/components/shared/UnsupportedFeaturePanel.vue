<script setup lang="ts">
import { PlugZap } from 'lucide-vue-next';

import EmptyState from '@/components/ui/EmptyState.vue';
import type { UnsupportedFeature } from '@/types';

defineProps<{
  feature: UnsupportedFeature;
}>();
</script>

<template>
  <EmptyState :icon="PlugZap" :title="feature.title" :body="feature.body">
    <code>{{ feature.endpoint }}</code>
  </EmptyState>
</template>

<style scoped>
code {
  border: 1px solid var(--border);
  border-radius: 4px;
  background: var(--surface-alt);
  color: var(--text-secondary);
  padding: 6px 8px;
}
</style>
