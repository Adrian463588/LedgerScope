<script setup lang="ts">
import { ref } from "vue";

defineProps<{
  label: string;
}>();

const open = ref(false);
</script>

<template>
  <div class="dropdown">
    <button @click="open = !open">{{ label }}</button>
    <div v-if="open" class="dropdown__menu">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.dropdown {
  position: relative;
}

button {
  height: 36px;
  border: 1px solid var(--border-strong);
  border-radius: 4px;
  background: white;
  padding: 0 12px;
}

.dropdown__menu {
  position: absolute;
  right: 0;
  z-index: 40;
  min-width: 180px;
  margin-top: 6px;
  border: 1px solid var(--border);
  border-radius: 6px;
  background: white;
  box-shadow: var(--shadow-dropdown);
  padding: 8px;
}
</style>
