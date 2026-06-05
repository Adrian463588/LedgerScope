<script setup lang="ts">
import { Building2, Plus } from 'lucide-vue-next';
import { onMounted } from 'vue';

import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import { navigateTo } from '@/router';
import { useCompanyStore } from '@/stores/company.store';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const companies = useCompanyStore();

onMounted(() => {
  ui.setBreadcrumbs(['Companies']);
  void companies.fetchCompanies();
});
</script>

<template>
  <PageHeader title="Companies Master Data" subtitle="Manage assigned entities, fiscal reporting profile, and access.">
    <template #actions>
      <AppButton variant="primary" :icon="Plus">New Company</AppButton>
    </template>
  </PageHeader>
  <EmptyState v-if="companies.companies.length === 0" :icon="Building2" title="No companies yet" body="Create a company to start bookkeeping and audit workflows." />
  <AppTable
    v-else
    :loading="companies.isLoading"
    :columns="[
      { key: 'name', label: 'Company' },
      { key: 'industry', label: 'Industry' },
      { key: 'reporting_period', label: 'Reporting Period' },
      { key: 'status', label: 'Status', isStatus: true },
    ]"
    :data="companies.companies"
    @click="navigateTo('/companies/acme')"
  />
</template>
