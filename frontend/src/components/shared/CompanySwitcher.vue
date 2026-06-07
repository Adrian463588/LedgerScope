<script setup lang="ts">
import { Building2 } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';
import { useCompanyStore } from '@/stores/company.store';

const companyStore = useCompanyStore();
const { companies, activeCompanyId } = storeToRefs(companyStore);

function switchCompany() {
  if (activeCompanyId.value) {
    companyStore.switchCompany(activeCompanyId.value);
  }
}
</script>

<template>
  <label class="company-switcher">
    <span><Building2 aria-hidden="true" /> Company</span>
    <select v-model.number="activeCompanyId" @change="switchCompany">
      <option v-for="company in companies" :key="company.id" :value="company.id">
        {{ company.name }}
      </option>
    </select>
  </label>
</template>

<style scoped>
.company-switcher {
  display: grid;
  gap: 8px;
  border-top: 1px solid var(--shell-border);
  border-bottom: 1px solid var(--shell-border);
  padding: 14px 12px;
}

span {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-inverse-muted);
  font-size: 0.75rem;
  text-transform: uppercase;
}

svg {
  width: 14px;
  height: 14px;
}

select {
  height: 34px;
  border: 1px solid var(--shell-border);
  border-radius: 4px;
  background: var(--shell-bg);
  color: white;
  padding: 0 10px;
}
</style>
