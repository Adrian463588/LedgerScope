<script setup lang="ts">
import { onMounted } from 'vue';

import UnsupportedFeaturePanel from '@/components/shared/UnsupportedFeaturePanel.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

onMounted(() => ui.setBreadcrumbs(['Accounting', 'Reconciliation']));
</script>

<template>
  <PageHeader title="Reconciliation" subtitle="Auto-match and approval workflow placeholder." />
  <UnsupportedFeaturePanel :feature="{ title: 'Reconciliation UI ready', body: 'Backend supports base reconciliation routes. Matching detail screens will connect in the next API pass.', endpoint: '/api/v1/companies/{id}/reconciliations' }" />
</template>
