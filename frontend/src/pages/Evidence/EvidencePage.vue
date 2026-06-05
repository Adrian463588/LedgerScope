<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import { onMounted } from 'vue';

import FileUploadZone from '@/components/evidence/FileUploadZone.vue';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import UnsupportedFeaturePanel from '@/components/shared/UnsupportedFeaturePanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { unsupportedFeatures } from '@/data/fixtures';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

onMounted(() => ui.setBreadcrumbs(['Evidence']));
</script>

<template>
  <PageHeader title="Document Request List" subtitle="PBC request tracking, due dates, and evidence status.">
    <template #actions>
      <AppButton variant="primary" :icon="Plus">New Request</AppButton>
    </template>
  </PageHeader>
  <UnsupportedFeaturePanel :feature="unsupportedFeatures.evidence" />
  <section class="evidence-grid">
    <SectionPanel title="Upload New Version"><FileUploadZone /></SectionPanel>
    <AppTable
      :columns="[
        { key: 'request', label: 'Request Name' },
        { key: 'due', label: 'Due Date' },
        { key: 'status', label: 'Status', isStatus: true },
      ]"
      :data="[
        { request: 'Bank Statement - Q3', due: '20 Jan 2026', status: 'requested' },
        { request: 'AR Aging Detail', due: '18 Jan 2026', status: 'accepted' },
      ]"
    />
  </section>
</template>

<style scoped>
.evidence-grid {
  display: grid;
  grid-template-columns: 0.8fr 1.2fr;
  gap: 20px;
}
</style>
