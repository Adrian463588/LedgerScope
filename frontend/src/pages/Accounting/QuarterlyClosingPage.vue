<script setup lang="ts">
import { Lock } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

import ProgressTracker from '@/components/shared/ProgressTracker.vue';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';
import AppModal from '@/components/ui/AppModal.vue';
import AmountDisplay from '@/components/ui/AmountDisplay.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useNotification } from '@/composables/useNotification';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const notification = useNotification();
const modalOpen = ref(false);
const confirmText = ref('');

function lockQuarter(): void {
  modalOpen.value = false;
  notification.success('Quarter locked.');
}

onMounted(() => ui.setBreadcrumbs(['Accounting', 'Quarterly Closing']));
</script>

<template>
  <PageHeader title="Quarterly Closing - Q1 2026" subtitle="Guided checklist before financial statement lock.">
    <template #actions>
      <StatusBadge status="In Progress" />
      <AppButton variant="primary" :icon="Lock" @click="modalOpen = true">Approve & Lock</AppButton>
    </template>
  </PageHeader>
  <section class="closing-grid">
    <SectionPanel title="Closing Checklist">
      <ul>
        <li>✓ Journals posted</li>
        <li>✓ Trial balanced</li>
        <li>✓ Bank reconciled</li>
        <li>○ AR reconciled</li>
        <li>○ Manager review</li>
      </ul>
      <ProgressTracker label="Progress" :value="38" />
    </SectionPanel>
    <SectionPanel title="Quarterly Summary">
      <dl>
        <div><dt>Revenue</dt><dd><AmountDisplay value="2450000.00" currency /></dd></div>
        <div><dt>Expenses</dt><dd><AmountDisplay value="1890000.00" currency kind="credit" /></dd></div>
        <div><dt>Net Profit</dt><dd><AmountDisplay value="560000.00" currency /></dd></div>
      </dl>
    </SectionPanel>
  </section>
  <AppModal :open="modalOpen" title="Lock Quarter Q1 2026" @close="modalOpen = false">
    <p>This action is permanent without explicit unlock approval. Type "Q1 2026" to confirm.</p>
    <AppInput v-model="confirmText" label="Confirmation" placeholder="Q1 2026" />
    <template #footer>
      <AppButton @click="modalOpen = false">Cancel</AppButton>
      <AppButton variant="danger" :disabled="confirmText !== 'Q1 2026'" @click="lockQuarter">Lock Now</AppButton>
    </template>
  </AppModal>
</template>

<style scoped>
.closing-grid {
  display: grid;
  grid-template-columns: 0.8fr 1.2fr;
  gap: 20px;
}

ul,
dl {
  display: grid;
  gap: 12px;
  margin: 0;
  padding: 0;
  list-style: none;
}

dl div {
  display: flex;
  justify-content: space-between;
}

dt {
  color: var(--text-secondary);
}

dd {
  margin: 0;
}
</style>
