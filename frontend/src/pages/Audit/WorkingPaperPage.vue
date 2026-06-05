<script setup lang="ts">
import { onMounted } from 'vue';

import SectionPanel from '@/components/shared/SectionPanel.vue';
import UnsupportedFeaturePanel from '@/components/shared/UnsupportedFeaturePanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { unsupportedFeatures } from '@/data/fixtures';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

onMounted(() => ui.setBreadcrumbs(['Audit', 'Working Paper', 'WP-C.1.1']));
</script>

<template>
  <PageHeader title="WP-C.1.1 Cash & Bank" subtitle="Year-end cutoff test with evidence links and review notes.">
    <template #actions>
      <StatusBadge status="Reviewed" />
      <AppButton variant="primary">Approve Working Paper</AppButton>
    </template>
  </PageHeader>
  <section class="wp-grid">
    <SectionPanel title="Working Paper">
      <h3>Audit Objective</h3>
      <p>Verify that cash and bank transactions are recorded in the correct period.</p>
      <h3>Procedures</h3>
      <ul><li>✓ Inspect bank statement</li><li>✓ Trace to GL balance</li><li>✓ Verify cutoff date</li></ul>
      <h3>Conclusion</h3>
      <p>Based on procedures performed, no material exception noted.</p>
    </SectionPanel>
    <SectionPanel title="Sign-off Trail">
      <p>Prepared: Budi Santoso · Jan 12, 09:15</p>
      <p>Reviewed: Ahmad Darwis · Jan 14, 16:30</p>
      <UnsupportedFeaturePanel :feature="unsupportedFeatures.workingPapers" />
    </SectionPanel>
  </section>
</template>

<style scoped>
.wp-grid {
  display: grid;
  grid-template-columns: 1.3fr 0.7fr;
  gap: 20px;
}

p,
li {
  color: var(--text-secondary);
}
</style>
