<script setup lang="ts">
const cells = Array.from({ length: 25 }, (_, index) => {
  const row = Math.floor(index / 5) + 1;
  const col = (index % 5) + 1;
  const score = row * col;
  return { id: index, label: `${row}x${col}`, score };
});
</script>

<template>
  <div class="risk-heatmap" aria-label="Risk heatmap">
    <span v-for="cell in cells" :key="cell.id" :class="cell.score > 15 ? 'critical' : cell.score > 8 ? 'high' : 'low'">
      {{ cell.score }}
    </span>
  </div>
</template>

<style scoped>
.risk-heatmap {
  display: grid;
  grid-template-columns: repeat(5, minmax(42px, 1fr));
  gap: 6px;
}

span {
  display: grid;
  aspect-ratio: 1;
  place-items: center;
  border-radius: 4px;
  font-family: 'IBM Plex Mono', monospace;
  font-weight: 700;
}

.low {
  background: var(--status-success-bg);
  color: var(--status-success);
}

.high {
  background: var(--status-warning-bg);
  color: var(--status-warning);
}

.critical {
  background: var(--status-danger-bg);
  color: var(--status-danger);
}
</style>
