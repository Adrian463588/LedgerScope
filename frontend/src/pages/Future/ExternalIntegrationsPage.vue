<script setup lang="ts">
import { onMounted, ref } from "vue";

import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { futureIntegrationsApi } = useLedgerScopeApi();
import AppTable from "@/components/ui/AppTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import EmptyState from "@/components/ui/EmptyState.vue";
import { useUiStore } from "@/stores/ui.store";
import type { ExternalIntegrationStatus } from "@/types";

const ui = useUiStore();
const integrations = ref<ExternalIntegrationStatus[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);

onMounted(async () => {
  ui.setBreadcrumbs(["Future integrations"]);
  try {
    integrations.value = await futureIntegrationsApi.statuses();
  } catch (caught) {
    error.value =
      caught instanceof Error
        ? caught.message
        : "Unable to load integration status.";
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <PageHeader
    title="External integrations"
    subtitle="Provider adapters are contract-ready and fail closed until credentials and sandbox validation are configured."
  />
  <div v-if="error" class="state state--error">{{ error }}</div>
  <EmptyState
    v-else-if="!isLoading && integrations.length === 0"
    title="No integration adapters registered"
    body="Configure the backend integration registry before enabling a provider."
  />
  <AppTable
    v-else
    :loading="isLoading"
    :columns="[
      { key: 'key', label: 'Integration' },
      { key: 'mode', label: 'Mode' },
      { key: 'configured', label: 'Configured', isStatus: true },
      { key: 'message', label: 'Status' },
    ]"
    :data="integrations"
  />
</template>

<style scoped>
.state {
  padding: 24px;
  color: var(--text-secondary);
}
.state--error {
  color: var(--status-danger);
}
</style>
