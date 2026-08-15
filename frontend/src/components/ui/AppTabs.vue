<script setup lang="ts">
export interface TabItem {
  label: string;
  value: string;
}

const model = defineModel<string>({ required: true });

defineProps<{
  tabs: TabItem[];
}>();
</script>

<template>
  <div class="tabs" role="tablist">
    <button
      v-for="tab in tabs"
      :key="tab.value"
      role="tab"
      :aria-selected="model === tab.value"
      :class="{ active: model === tab.value }"
      @click="model = tab.value"
    >
      {{ tab.label }}
    </button>
  </div>
</template>

<style scoped>
.tabs {
  display: flex;
  border-bottom: 1px solid var(--border);
}

button {
  border: 0;
  border-bottom: 2px solid transparent;
  background: transparent;
  color: var(--text-secondary);
  padding: 12px 16px;
}

button:hover,
button.active {
  color: var(--brand-red);
  border-bottom-color: var(--brand-red);
}
</style>
