<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import { onMounted } from 'vue';

import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

onMounted(() => ui.setBreadcrumbs(['Audit', 'Risk Control Matrix']));
</script>

<template>
  <PageHeader title="Risk Control Matrix (RCM)" subtitle="Map risks, controls, owners, and audit results.">
    <template #actions>
      <AppButton variant="primary" :icon="Plus">New Risk Control</AppButton>
    </template>
  </PageHeader>
  <SectionPanel title="Control Coverage" subtitle="4 unmapped risks require attention." />
  <AppTable
    :columns="[
      { key: 'control', label: 'Control Name' },
      { key: 'owner', label: 'Owner' },
      { key: 'nature', label: 'Nature' },
      { key: 'result', label: 'Audit Result', isStatus: true },
    ]"
    :data="[
      { control: 'Privileged Access Review for ERP Journal Entries', owner: 'IT Controller', nature: 'Detective', result: 'Failed' },
      { control: 'Bank Reconciliation Review', owner: 'Finance Manager', nature: 'Preventive', result: 'Passed' },
    ]"
  />
</template>
