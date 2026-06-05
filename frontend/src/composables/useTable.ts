import { computed, reactive, ref } from 'vue';

export interface TableOptions {
  initialPerPage?: number;
}

export function useTable(options: TableOptions = {}) {
  const sortBy = ref<string | null>(null);
  const sortDirection = ref<'asc' | 'desc'>('asc');
  const currentPage = ref(1);
  const perPage = ref(options.initialPerPage ?? 25);
  const filters = reactive<Record<string, unknown>>({});

  function toggleSort(column: string): void {
    if (sortBy.value === column) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
      return;
    }

    sortBy.value = column;
    sortDirection.value = 'asc';
  }

  function goToPage(page: number): void {
    currentPage.value = Math.max(1, page);
  }

  function setFilter(key: string, value: unknown): void {
    filters[key] = value;
    currentPage.value = 1;
  }

  function clearFilters(): void {
    Object.keys(filters).forEach((key) => delete filters[key]);
    currentPage.value = 1;
  }

  const queryParams = computed(() => ({
    page: currentPage.value,
    per_page: perPage.value,
    sort: sortBy.value ?? undefined,
    direction: sortDirection.value,
    ...filters,
  }));

  return { sortBy, sortDirection, currentPage, perPage, filters, queryParams, toggleSort, goToPage, setFilter, clearFilters };
}
