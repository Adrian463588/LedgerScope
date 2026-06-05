<script setup lang="ts">
import { Plus } from 'lucide-vue-next';
import { computed, onMounted } from 'vue';

import ProgressTracker from '@/components/shared/ProgressTracker.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { useCompanyStore } from '@/stores/company.store';
import { useEngagementStore } from '@/stores/engagement.store';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const companies = useCompanyStore();
const engagements = useEngagementStore();
const rows = computed(() => engagements.engagements.map((item) => ({ name: item.name, type: item.type, period: item.period, progress: `${item.progress}%`, status: item.status, risk: item.risk })));

onMounted(() => {
  ui.setBreadcrumbs(['Audit', 'Engagements']);
  void engagements.fetchEngagements(companies.activeCompany?.id ?? 1);
});
</script>

<template>
  <PageHeader title="Audit Engagements" subtitle="Engagement planning, assignment, progress, and risk exposure.">
    <template #actions>
      <AppButton variant="primary" :icon="Plus">Create Engagement</AppButton>
    </template>
  </PageHeader>
  <ProgressTracker label="Fieldwork completion" :value="68" />
  <AppTable
    :loading="engagements.isLoading"
    :columns="[
      { key: 'name', label: 'Engagement' },
      { key: 'type', label: 'Type' },
      { key: 'period', label: 'Reporting Period' },
      { key: 'progress', label: 'Progress' },
      { key: 'status', label: 'Status', isStatus: true },
      { key: 'risk', label: 'Risk', isStatus: true },
    ]"
    :data="rows"
  />
</template>
