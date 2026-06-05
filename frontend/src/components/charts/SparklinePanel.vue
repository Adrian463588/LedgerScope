<script setup lang="ts">
defineProps<{
  title: string;
  values: number[];
}>();
</script>

<template>
  <section class="sparkline-panel">
    <h3>{{ title }}</h3>
    <div>
      <span v-for="(value, index) in values" :key="index" :style="{ height: `${value}%` }" />
    </div>
  </section>
</template>

<style scoped>
.sparkline-panel {
  display: grid;
  gap: 16px;
}

h3 {
  margin: 0;
  font-size: 1rem;
}

div {
  display: flex;
  height: 180px;
  align-items: end;
  gap: 10px;
  border-bottom: 1px solid var(--border);
  padding: 16px 8px 0;
}

span {
  flex: 1;
  min-height: 16px;
  border-radius: 4px 4px 0 0;
  background: var(--brand-red);
}

span:nth-child(odd) {
  background: var(--debit-color);
}
</style>
