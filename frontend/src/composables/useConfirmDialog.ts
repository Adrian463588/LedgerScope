import { storeToRefs } from 'pinia';

import { useUiStore } from '@/stores/ui.store';

export function useConfirmDialog() {
  const ui = useUiStore();
  const { confirmDialog } = storeToRefs(ui);

  return {
    confirmDialog,
    confirm: ui.confirm,
    resolveConfirm: ui.resolveConfirm,
    cancelConfirm: ui.cancelConfirm,
  };
}
