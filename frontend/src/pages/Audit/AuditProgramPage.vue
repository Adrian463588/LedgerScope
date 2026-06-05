<script setup lang="ts">
import { onMounted } from 'vue';

import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

onMounted(() => ui.setBreadcrumbs(['Audit', 'Audit Program']));
</script>

<template>
  <PageHeader eyebrow="Audit Program" title="Substantive Testing Matrix" subtitle="Procedure coverage, objective, and detection risk monitor." />
  <SectionPanel title="Audit Risk Monitor"><StatusBadge status="High Detection Risk" /></SectionPanel>
  <AppTable
    :columns="[
      { key: 'procedure', label: 'Audit Procedure & Objective' },
      { key: 'area', label: 'Area' },
      { key: 'status', label: 'Status', isStatus: true },
    ]"
    :data="[
      { procedure: 'Inspect bank statements and trace to GL balance.', area: 'Cash & Bank', status: 'review' },
      { procedure: 'Test journal entry cutoff around period close.', area: 'Journals', status: 'submitted' },
    ]"
  />
</template>
