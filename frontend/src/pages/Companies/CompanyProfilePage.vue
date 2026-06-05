<script setup lang="ts">
import { Edit } from 'lucide-vue-next';
import { onMounted } from 'vue';

import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useCompanyStore } from '@/stores/company.store';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
const companies = useCompanyStore();

onMounted(() => ui.setBreadcrumbs(['Companies', 'Acme Corp']));
</script>

<template>
  <PageHeader :title="companies.activeCompany?.name ?? 'Company Profile'" subtitle="Entity profile, contacts, and audit history.">
    <template #actions>
      <AppButton :icon="Edit">Edit Profile</AppButton>
    </template>
  </PageHeader>
  <section class="profile-grid">
    <SectionPanel title="Company Details">
      <dl>
        <div><dt>Legal Name</dt><dd>{{ companies.activeCompany?.legal_name }}</dd></div>
        <div><dt>Industry</dt><dd>{{ companies.activeCompany?.industry }}</dd></div>
        <div><dt>Fiscal Year End</dt><dd>{{ companies.activeCompany?.fiscal_year_end }}</dd></div>
        <div><dt>Status</dt><dd><StatusBadge :status="companies.activeCompany?.status ?? 'active'" /></dd></div>
      </dl>
    </SectionPanel>
    <SectionPanel title="Audit Method">
      <p>Risk-based audit with quarterly close review and financial statement sign-off.</p>
      <p class="address">4500 Financial Way, Suite 1200</p>
    </SectionPanel>
  </section>
</template>

<style scoped>
.profile-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

dl {
  display: grid;
  gap: 16px;
  margin: 0;
}

div {
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

dt {
  color: var(--text-muted);
}

dd {
  margin: 0;
  font-weight: 600;
}

p {
  margin: 0;
  color: var(--text-secondary);
}

.address {
  font-family: 'IBM Plex Mono', monospace;
}

@media (max-width: 900px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}
</style>
