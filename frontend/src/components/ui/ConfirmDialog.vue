<script setup lang="ts">
import AppButton from "./AppButton.vue";
import AppModal from "./AppModal.vue";
import { useConfirmDialog } from "@/composables/useConfirmDialog";

const { confirmDialog, resolveConfirm, cancelConfirm } = useConfirmDialog();
</script>

<template>
  <AppModal
    :open="confirmDialog.open"
    :title="confirmDialog.title"
    @close="cancelConfirm"
  >
    <p class="confirm-message">{{ confirmDialog.message }}</p>
    <template #footer>
      <AppButton variant="secondary" @click="cancelConfirm">Cancel</AppButton>
      <AppButton
        :variant="confirmDialog.tone === 'danger' ? 'danger' : 'primary'"
        @click="resolveConfirm"
        >{{ confirmDialog.confirmLabel }}</AppButton
      >
    </template>
  </AppModal>
</template>

<style scoped>
.confirm-message {
  margin: 0;
  color: var(--text-secondary);
  line-height: 1.6;
}
</style>
