<script setup lang="ts">
import { onMounted } from 'vue';

import UnsupportedFeaturePanel from '@/components/shared/UnsupportedFeaturePanel.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { unsupportedFeatures } from '@/data/fixtures';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

onMounted(() => ui.setBreadcrumbs(['Audit', 'Findings']));
</script>

<template>
  <PageHeader title="Audit Findings" subtitle="Issue severity, management response, and remediation workflow." />
  <UnsupportedFeaturePanel :feature="unsupportedFeatures.findings" />
  <AppTable
    :columns="[
      { key: 'finding', label: 'Finding' },
      { key: 'severity', label: 'Severity', isStatus: true },
      { key: 'status', label: 'Status', isStatus: true },
    ]"
    :data="[
      { finding: 'Revenue cutoff documentation incomplete', severity: 'high', status: 'open' },
      { finding: 'User access review delayed', severity: 'medium', status: 'review' },
    ]"
  />
</template>
