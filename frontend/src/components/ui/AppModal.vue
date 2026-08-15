<script setup lang="ts">
import { X } from "lucide-vue-next";

defineProps<{
  open: boolean;
  title: string;
}>();

defineEmits<{
  close: [];
}>();

const slots = defineSlots<{
  default?: () => unknown;
  footer?: () => unknown;
}>();
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="modal-backdrop"
      role="presentation"
      @click="$emit('close')"
    >
      <section
        class="modal-panel"
        role="dialog"
        aria-modal="true"
        :aria-label="title"
        @click.stop
      >
        <header>
          <h2>{{ title }}</h2>
          <button aria-label="Close dialog" @click="$emit('close')">
            <X aria-hidden="true" />
          </button>
        </header>
        <div class="modal-body">
          <slot />
        </div>
        <footer v-if="slots.footer">
          <slot name="footer" />
        </footer>
      </section>
    </div>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 80;
  display: grid;
  place-items: center;
  background: var(--overlay-backdrop);
  padding: 24px;
}

.modal-panel {
  width: min(560px, 100%);
  border-radius: 8px;
  background: white;
  box-shadow: var(--shadow-modal);
}

header,
footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  padding: 16px 20px;
}

footer {
  justify-content: flex-end;
  gap: 8px;
  border-top: 1px solid var(--border);
  border-bottom: 0;
}

h2 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

button {
  display: grid;
  width: 32px;
  height: 32px;
  place-items: center;
  border: 0;
  border-radius: 4px;
  background: transparent;
  color: var(--text-secondary);
}

button:hover {
  background: var(--surface-hover);
}

svg {
  width: 18px;
  height: 18px;
}

.modal-body {
  padding: 20px;
}
</style>
