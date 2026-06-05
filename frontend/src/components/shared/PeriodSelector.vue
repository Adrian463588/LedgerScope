<script setup lang="ts">
const model = defineModel<string>({ default: 'Q1 2026' });
</script>

<template>
  <label class="period-selector">
    <span>Period</span>
    <select v-model="model">
      <option>Q1 2026</option>
      <option>Q4 2025</option>
      <option>FY 2024</option>
    </select>
  </label>
</template>

<style scoped>
.period-selector {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--text-secondary);
}

select {
  height: 36px;
  border: 1px solid var(--border-strong);
  border-radius: 4px;
  background: white;
  color: var(--text-primary);
  padding: 0 10px;
}
</style>
