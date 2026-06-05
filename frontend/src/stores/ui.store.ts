import { defineStore } from 'pinia';
import { ref } from 'vue';

export interface ConfirmDialogState {
  open: boolean;
  title: string;
  message: string;
  tone: 'danger' | 'primary';
  confirmLabel: string;
}

export const useUiStore = defineStore('ui', () => {
  const sidebarCollapsed = ref(false);
  const mobileSidebarOpen = ref(false);
  const breadcrumbs = ref<string[]>(['Dashboard']);
  const confirmDialog = ref<ConfirmDialogState>({ open: false, title: '', message: '', tone: 'primary', confirmLabel: 'Confirm' });
  const resolver = ref<((value: boolean) => void) | null>(null);

  function toggleSidebar(): void {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  }

  function setMobileSidebar(open: boolean): void {
    mobileSidebarOpen.value = open;
  }

  function setBreadcrumbs(next: string[]): void {
    breadcrumbs.value = next;
  }

  function confirm(options: Omit<ConfirmDialogState, 'open'>): Promise<boolean> {
    confirmDialog.value = { ...options, open: true };
    return new Promise((resolve) => {
      resolver.value = resolve;
    });
  }

  function resolveConfirm(): void {
    confirmDialog.value.open = false;
    resolver.value?.(true);
    resolver.value = null;
  }

  function cancelConfirm(): void {
    confirmDialog.value.open = false;
    resolver.value?.(false);
    resolver.value = null;
  }

  return { sidebarCollapsed, mobileSidebarOpen, breadcrumbs, confirmDialog, toggleSidebar, setMobileSidebar, setBreadcrumbs, confirm, resolveConfirm, cancelConfirm };
});
