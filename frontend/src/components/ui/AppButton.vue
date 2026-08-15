<script setup lang="ts">
import type { Component } from "vue";
import { Loader2 } from "lucide-vue-next";

const props = withDefaults(
  defineProps<{
    variant?: "primary" | "secondary" | "ghost" | "danger" | "locked";
    size?: "sm" | "md" | "lg";
    loading?: boolean;
    icon?: Component;
    iconRight?: boolean;
    type?: "button" | "submit" | "reset";
    disabled?: boolean;
  }>(),
  {
    variant: "secondary",
    size: "md",
    loading: false,
    icon: undefined,
    iconRight: false,
    type: "button",
    disabled: false,
  },
);
</script>

<template>
  <button
    :type="props.type"
    class="app-button"
    :class="[
      `app-button--${props.variant}`,
      `app-button--${props.size}`,
      { 'app-button--loading': props.loading },
    ]"
    :disabled="props.disabled || props.loading || props.variant === 'locked'"
    :data-loading="props.loading || undefined"
  >
    <Loader2
      v-if="props.loading"
      class="app-button__icon app-button__spinner"
      aria-hidden="true"
    />
    <component
      :is="props.icon"
      v-else-if="props.icon && !props.iconRight"
      class="app-button__icon"
      aria-hidden="true"
    />
    <span class="app-button__label"><slot /></span>
    <component
      :is="props.icon"
      v-if="props.icon && props.iconRight && !props.loading"
      class="app-button__icon"
      aria-hidden="true"
    />
  </button>
</template>

<style scoped>
.app-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-width: max-content;
  border-radius: 4px;
  border: 1px solid transparent;
  font:
    500 0.8125rem/0.875rem "IBM Plex Sans",
    sans-serif;
  transition:
    background 140ms ease,
    border-color 140ms ease,
    color 140ms ease,
    transform 120ms ease;
}

.app-button:hover:not(:disabled) {
  transform: translateY(-1px);
}

.app-button:active:not(:disabled) {
  transform: translateY(0);
}

.app-button:disabled {
  opacity: 0.72;
  cursor: not-allowed;
}

.app-button--sm {
  height: 28px;
  padding: 6px 12px;
}

.app-button--md {
  height: 36px;
  padding: 8px 16px;
}

.app-button--lg {
  height: 40px;
  padding: 10px 20px;
}

.app-button--primary {
  background: var(--brand-red);
  border-color: var(--brand-red);
  color: white;
}

.app-button--primary:hover:not(:disabled) {
  background: var(--brand-red-hover);
}

.app-button--secondary {
  background: white;
  border-color: var(--border-strong);
  color: var(--text-primary);
}

.app-button--secondary:hover:not(:disabled) {
  background: var(--surface-hover);
}

.app-button--ghost {
  background: transparent;
  color: var(--brand-red);
}

.app-button--ghost:hover:not(:disabled) {
  background: var(--brand-red-muted);
}

.app-button--danger {
  background: var(--status-danger);
  border-color: var(--status-danger);
  color: white;
}

.app-button--danger:hover:not(:disabled) {
  filter: brightness(0.94);
}

.app-button--locked {
  background: var(--surface-alt);
  border-color: var(--border);
  color: var(--text-muted);
}

.app-button__icon {
  width: 16px;
  height: 16px;
}

.app-button__spinner {
  animation: spin 800ms linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
