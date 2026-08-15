<script setup lang="ts">
import { Lock } from "lucide-vue-next";
</script>

<template>
  <aside class="lock-banner">
    <Lock aria-hidden="true" />
    <div>
      <strong>This period is locked</strong>
      <p>
        Q1 2026 was locked after review. Request unlock before editing posted
        records.
      </p>
    </div>
  </aside>
</template>

<style scoped>
.lock-banner {
  display: flex;
  gap: 12px;
  border: 1px solid var(--brand-red-border);
  border-radius: 8px;
  background: var(--brand-red-muted);
  color: var(--text-primary);
  padding: 14px 16px;
}

svg {
  width: 24px;
  height: 24px;
  color: var(--brand-red);
}

p {
  margin: 4px 0 0;
  color: var(--text-secondary);
}
</style>
