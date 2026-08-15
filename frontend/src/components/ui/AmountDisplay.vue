<script setup lang="ts">
import { computed } from "vue";

import { useCurrency } from "@/composables/useCurrency";

const props = withDefaults(
  defineProps<{
    value: string | number;
    kind?: "debit" | "credit" | "neutral";
    currency?: boolean;
  }>(),
  {
    kind: "neutral",
    currency: false,
  },
);

const { formatAmount, formatCurrency, amountColorClass } = useCurrency();

const display = computed(() =>
  props.currency ? formatCurrency(props.value) : formatAmount(props.value),
);
const tone = computed(() =>
  props.kind === "neutral"
    ? "amount-neutral"
    : amountColorClass(String(props.value), props.kind),
);
</script>

<template>
  <span class="amount-display font-mono-finance" :class="tone">{{
    display
  }}</span>
</template>

<style scoped>
.amount-display {
  display: inline-block;
  min-width: 6ch;
  text-align: right;
}

.amount-debit {
  color: var(--debit-color);
}

.amount-credit {
  color: var(--credit-color);
}

.amount-zero,
.amount-neutral {
  color: var(--zero-color);
}
</style>
