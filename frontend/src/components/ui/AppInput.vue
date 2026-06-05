<script setup lang="ts">
const model = defineModel<string | number>({ required: true });

const props = withDefaults(
  defineProps<{
    label: string;
    hint?: string;
    error?: string;
    required?: boolean;
    disabled?: boolean;
    type?: string;
    amount?: boolean;
    placeholder?: string;
  }>(),
  {
    hint: '',
    error: '',
    required: false,
    disabled: false,
    type: 'text',
    amount: false,
    placeholder: '',
  },
);
</script>

<template>
  <label class="form-field">
    <span class="form-field__label" :class="{ required: props.required }">{{ props.label }}</span>
    <input
      v-model="model"
      class="form-field__control"
      :class="{ 'form-field__control--amount': props.amount, 'form-field__control--error': props.error }"
      :type="props.type"
      :disabled="props.disabled"
      :placeholder="props.placeholder"
      :aria-invalid="Boolean(props.error)"
    />
    <span v-if="props.error" class="form-field__error">{{ props.error }}</span>
    <span v-else-if="props.hint" class="form-field__hint">{{ props.hint }}</span>
  </label>
</template>

<style scoped>
.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-field__label {
  color: var(--text-secondary);
  font: 500 0.8125rem/1.25rem 'IBM Plex Sans', sans-serif;
}

.form-field__label.required::after {
  color: var(--brand-red);
  content: ' *';
}

.form-field__control {
  height: 36px;
  border: 1px solid var(--border);
  border-radius: 4px;
  background: white;
  color: var(--text-primary);
  padding: 0 12px;
  transition: border-color 120ms, box-shadow 120ms;
}

.form-field__control:focus {
  border-color: var(--brand-red);
  box-shadow: var(--shadow-focus);
  outline: none;
}

.form-field__control:disabled {
  background: var(--surface-alt);
  color: var(--text-muted);
  cursor: not-allowed;
}

.form-field__control--amount {
  font-family: 'IBM Plex Mono', monospace;
  font-variant-numeric: tabular-nums;
  text-align: right;
}

.form-field__control--error {
  border-color: var(--status-danger);
}

.form-field__error,
.form-field__hint {
  font-size: 0.75rem;
}

.form-field__error {
  color: var(--status-danger);
}

.form-field__hint {
  color: var(--text-muted);
}
</style>
