<script setup lang="ts">
import { computed } from "vue";

import AmountDisplay from "./AmountDisplay.vue";
import SkeletonBlock from "./SkeletonBlock.vue";
import StatusBadge from "./StatusBadge.vue";

export interface TableColumn {
  key: string;
  label: string;
  align?: "left" | "right" | "center";
  sortable?: boolean;
  isAmount?: boolean;
  isStatus?: boolean;
  width?: string;
}

export type TableCell = string | number | boolean | object | null | undefined;
export type TableRow = Record<string, TableCell>;

const props = withDefaults(
  defineProps<{
    columns: TableColumn[];
    data: TableRow[];
    loading?: boolean;
    emptyText?: string;
  }>(),
  {
    loading: false,
    emptyText: "No records found.",
  },
);

const emit = defineEmits<{
  "row-click": [row: TableRow, index: number];
}>();

const skeletonRows = computed(() =>
  Array.from({ length: 6 }, (_, index) => index),
);
</script>

<template>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th
            v-for="column in props.columns"
            :key="column.key"
            :class="`align-${column.align ?? 'left'}`"
            :style="{ width: column.width }"
          >
            {{ column.label }}
          </th>
        </tr>
      </thead>
      <tbody v-if="props.loading">
        <tr v-for="row in skeletonRows" :key="row">
          <td v-for="column in props.columns" :key="column.key">
            <SkeletonBlock :width="column.align === 'right' ? '80px' : '70%'" />
          </td>
        </tr>
      </tbody>
      <tbody v-else-if="props.data.length > 0">
        <tr
          v-for="(row, index) in props.data"
          :key="index"
          tabindex="0"
          @click="emit('row-click', row, index)"
          @keydown.enter="emit('row-click', row, index)"
        >
          <td
            v-for="column in props.columns"
            :key="column.key"
            :class="`align-${column.align ?? 'left'}`"
          >
            <slot
              v-if="$slots[`cell-${column.key}`]"
              :name="`cell-${column.key}`"
              :row="row"
              :value="row[column.key]"
              :index="index"
            />
            <AmountDisplay
              v-else-if="column.isAmount"
              :value="String(row[column.key] ?? '0.00')"
              :kind="
                column.key.toLowerCase().includes('credit') ? 'credit' : 'debit'
              "
            />
            <StatusBadge
              v-else-if="column.isStatus"
              :status="String(row[column.key] ?? '')"
            />
            <span v-else>{{ row[column.key] }}</span>
          </td>
        </tr>
      </tbody>
      <tbody v-else>
        <tr>
          <td :colspan="props.columns.length" class="empty-cell">
            {{ props.emptyText }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table-wrap {
  overflow: auto;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: white;
  box-shadow: var(--shadow-card);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 720px;
}

th {
  height: 40px;
  background: var(--surface-alt);
  color: var(--text-muted);
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.04em;
  padding: 0 16px;
  text-transform: uppercase;
}

td {
  height: 48px;
  border-top: 1px solid var(--border);
  color: var(--text-primary);
  padding: 12px 16px;
}

tr:hover td {
  background: var(--surface-hover);
}

.align-right {
  text-align: right;
}

.align-center {
  text-align: center;
}

.empty-cell {
  color: var(--text-muted);
  text-align: center;
}
</style>
