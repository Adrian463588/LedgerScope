<script setup lang="ts">
import { useNotification } from "@/composables/useNotification";

const { notifications, remove } = useNotification();
</script>

<template>
  <div class="toast-host" aria-live="polite">
    <button
      v-for="item in notifications"
      :key="item.id"
      class="toast"
      :class="`toast--${item.type}`"
      @click="remove(item.id)"
    >
      {{ item.message }}
    </button>
  </div>
</template>

<style scoped>
.toast-host {
  position: fixed;
  right: 20px;
  bottom: 20px;
  z-index: 100;
  display: grid;
  gap: 8px;
  width: min(360px, calc(100vw - 40px));
}

.toast {
  border: 1px solid;
  border-radius: 6px;
  padding: 12px 14px;
  text-align: left;
  box-shadow: var(--shadow-dropdown);
}

.toast--success {
  background: var(--status-success-bg);
  border-color: var(--status-success-border);
  color: var(--status-success);
}

.toast--error {
  background: var(--status-danger-bg);
  border-color: var(--status-danger-border);
  color: var(--status-danger);
}

.toast--info {
  background: var(--status-info-bg);
  border-color: var(--status-info-border);
  color: var(--status-info);
}
</style>
