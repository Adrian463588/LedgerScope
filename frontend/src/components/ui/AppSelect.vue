<script setup lang="ts">
export interface SelectOption {
  label: string;
  value: string | number;
}

const model = defineModel<string | number>({ required: true });

withDefaults(
  defineProps<{
    label: string;
    options: SelectOption[];
  }>(),
  {},
);
</script>

<template>
  <label class="select-field">
    <span>{{ label }}</span>
    <select v-model="model">
      <option v-for="option in options" :key="option.value" :value="option.value">{{ option.label }}</option>
    </select>
  </label>
</template>

<style scoped>
.select-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  color: var(--text-secondary);
  font-size: 0.8125rem;
  font-weight: 500;
}

select {
  height: 36px;
  border: 1px solid var(--border);
  border-radius: 4px;
  background: white;
  color: var(--text-primary);
  padding: 0 32px 0 12px;
}
</style>
