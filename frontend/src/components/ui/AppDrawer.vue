<script setup lang="ts">
defineProps<{
  open: boolean;
  title: string;
}>();

defineEmits<{
  close: [];
}>();
</script>

<template>
  <Teleport to="body">
    <aside
      v-if="open"
      class="drawer"
      role="dialog"
      aria-modal="true"
      :aria-label="title"
    >
      <div class="drawer__backdrop" @click="$emit('close')" />
      <section class="drawer__panel">
        <header>
          <h2>{{ title }}</h2>
          <button @click="$emit('close')">Close</button>
        </header>
        <slot />
      </section>
    </aside>
  </Teleport>
</template>

<style scoped>
.drawer {
  position: fixed;
  inset: 0;
  z-index: 75;
}

.drawer__backdrop {
  position: absolute;
  inset: 0;
  background: var(--overlay-backdrop);
}

.drawer__panel {
  position: absolute;
  top: 0;
  right: 0;
  width: min(420px, 100%);
  height: 100%;
  background: white;
  box-shadow: var(--shadow-modal);
  animation: slide-in 280ms cubic-bezier(0.16, 1, 0.3, 1);
}

header {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  padding: 16px 20px;
}

@keyframes slide-in {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
}
</style>
