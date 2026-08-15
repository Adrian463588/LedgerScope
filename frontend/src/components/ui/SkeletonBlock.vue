<script setup lang="ts">
withDefaults(
  defineProps<{
    height?: string;
    width?: string;
  }>(),
  {
    height: "14px",
    width: "100%",
  },
);
</script>

<template>
  <span class="skeleton block" :style="{ height, width }" aria-hidden="true" />
</template>
