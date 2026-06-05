<script setup lang="ts">
import { onMounted } from 'vue';

import FileUploadZone from '@/components/evidence/FileUploadZone.vue';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

onMounted(() => ui.setBreadcrumbs(['Client Portal', 'Document Request']));
</script>

<template>
  <PageHeader title="Bank Statement - Q3" subtitle="Document Request #DR-0024 · Due 20 Jan 2026">
    <template #actions><StatusBadge status="High" /></template>
  </PageHeader>
  <SectionPanel title="Auditor Instructions">
    <p>Please provide the official bank statement for account BCA 123-456-7890 for July to September 2024.</p>
  </SectionPanel>
  <SectionPanel title="Uploaded Files">
    <p><StatusBadge status="Accepted" /> Acme_Corp_Statement_Jul_Sep_2024.pdf · v2</p>
    <p><StatusBadge status="Rejected" /> Acme_Corp_Statement_Jul_Sep_2024_v1.pdf · password protected</p>
  </SectionPanel>
  <FileUploadZone />
</template>
