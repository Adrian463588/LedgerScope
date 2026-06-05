<script setup lang="ts">
import AppButton from './AppButton.vue';

const page = defineModel<number>({ required: true });

const props = defineProps<{
  totalPages: number;
}>();
</script>

<template>
  <nav class="pagination" aria-label="Pagination">
    <span>Page {{ page }} of {{ props.totalPages }}</span>
    <AppButton size="sm" :disabled="page <= 1" @click="page -= 1">Previous</AppButton>
    <AppButton size="sm" :disabled="page >= props.totalPages" @click="page += 1">Next</AppButton>
  </nav>
</template>

<style scoped>
.pagination {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
  color: var(--text-muted);
  font-size: 0.8125rem;
}
</style>
