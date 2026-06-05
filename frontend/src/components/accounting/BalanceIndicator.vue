<script setup lang="ts">
import { computed } from 'vue';
import { AlertCircle, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps<{
  debit: number;
  credit: number;
}>();

const balanced = computed(() => props.debit === props.credit);
</script>

<template>
  <div class="balance-indicator" :class="{ balanced, unbalanced: !balanced }">
    <CheckCircle2 v-if="balanced" aria-hidden="true" />
    <AlertCircle v-else aria-hidden="true" />
    <strong>{{ balanced ? 'Journal Balanced' : 'Journal Unbalanced' }}</strong>
    <span>Debit {{ debit.toLocaleString('id-ID') }} · Credit {{ credit.toLocaleString('id-ID') }}</span>
  </div>
</template>

<style scoped>
.balance-indicator {
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: white;
  padding: 12px 14px;
}

.balanced {
  color: var(--status-success);
}

.unbalanced {
  color: var(--status-danger);
}

svg {
  width: 20px;
  height: 20px;
}

span {
  margin-left: auto;
  color: var(--text-secondary);
  font-family: 'IBM Plex Mono', monospace;
}
</style>
