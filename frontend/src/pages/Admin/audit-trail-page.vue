<script setup lang="ts">
import { onMounted, ref } from "vue";
import { Search, Eye, X } from "lucide-vue-next";
import { useUiStore } from "@/stores/ui.store";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { adminApi } = useLedgerScopeApi();
import { useNotification } from "@/composables/useNotification";
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppTable from "@/components/ui/AppTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import type { AuditLog } from "@/types";
import type { TableRow } from "@/components/ui/AppTable.vue";

const ui = useUiStore();
const notification = useNotification();

const logs = ref<AuditLog[]>([]);
const isLoading = ref(true);
const page = ref(1);
const totalPages = ref(1);
const selectedLog = ref<AuditLog | null>(null);
const showDetailModal = ref(false);

const actionFilter = ref("");
const userFilter = ref("");

async function loadLogs() {
  isLoading.value = true;
  try {
    const params: Record<string, string | number> = {
      page: page.value,
    };
    if (actionFilter.value) params["action"] = actionFilter.value;
    if (userFilter.value) params["user_id"] = userFilter.value;

    const res = await adminApi.listAuditTrail(params);
    logs.value = res.items;
    totalPages.value = res.meta.last_page;
  } catch {
    notification.error("Failed to load audit trail logs.");
  } finally {
    isLoading.value = false;
  }
}

function viewDetail(log: AuditLog): void {
  selectedLog.value = log;
  showDetailModal.value = true;
}

function asAuditLog(row: TableRow): AuditLog {
  return row as unknown as AuditLog;
}

function rowUser(row: TableRow): AuditLog["user"] {
  return row["user"] && typeof row["user"] === "object"
    ? (row["user"] as AuditLog["user"])
    : null;
}

function rowCreatedAt(row: TableRow): string {
  return typeof row["created_at"] === "string" ? row["created_at"] : "";
}

onMounted(() => {
  ui.setBreadcrumbs(["Admin", "Audit Trail"]);
  void loadLogs();
});
</script>

<template>
  <PageHeader
    title="Audit Trail & Compliance Logs"
    subtitle="Track all sensitive operations, administration actions, and financial modifications."
  />

  <div class="p-4 space-y-4">
    <div
      class="flex flex-wrap gap-4 items-end bg-[color:var(--shell-900)] p-4 rounded-lg border border-[color:var(--shell-border)]"
    >
      <div class="flex-1 min-w-[200px]">
        <label class="block text-xs text-gray-400 font-semibold mb-1 uppercase"
          >Filter by Action</label
        >
        <input
          v-model="actionFilter"
          type="text"
          placeholder="e.g. admin.user.suspended"
          class="w-full bg-[color:var(--shell-950)] text-white text-sm rounded border border-[color:var(--shell-border)] p-2"
          @keyup.enter="loadLogs"
        />
      </div>
      <div>
        <AppButton variant="secondary" :icon="Search" @click="loadLogs"
          >Filter Logs</AppButton
        >
      </div>
    </div>

    <SectionPanel
      title="Compliance Events"
      subtitle="Traceability matrix of all system logs"
    >
      <AppTable
        :loading="isLoading"
        :columns="[
          { key: 'action', label: 'Action' },
          { key: 'user', label: 'User' },
          { key: 'ip_address', label: 'IP Address' },
          { key: 'timestamp', label: 'Timestamp' },
          { key: 'actions', label: '' },
        ]"
        :data="logs"
      >
        <template #cell-user="{ row }">
          <span v-if="rowUser(row)" class="text-white">{{
            rowUser(row)?.name
          }}</span>
          <span v-else class="text-gray-500 italic">System</span>
        </template>
        <template #cell-timestamp="{ row }">
          <span class="text-gray-400 text-xs">{{
            new Date(rowCreatedAt(row)).toLocaleString()
          }}</span>
        </template>
        <template #cell-actions="{ row }">
          <button
            class="p-1 text-primary hover:text-white"
            @click="viewDetail(asAuditLog(row))"
          >
            <Eye class="w-4 h-4" />
          </button>
        </template>
      </AppTable>

      <div
        v-if="totalPages > 1"
        class="flex justify-between items-center mt-4 pt-4 border-t border-[color:var(--shell-border)]"
      >
        <AppButton
          variant="secondary"
          :disabled="page <= 1"
          @click="
            page--;
            loadLogs();
          "
          >Previous</AppButton
        >
        <span class="text-xs text-gray-400"
          >Page {{ page }} of {{ totalPages }}</span
        >
        <AppButton
          variant="secondary"
          :disabled="page >= totalPages"
          @click="
            page++;
            loadLogs();
          "
          >Next</AppButton
        >
      </div>
    </SectionPanel>
  </div>

  <div
    v-if="showDetailModal && selectedLog"
    class="modal-overlay"
    @click.self="showDetailModal = false"
  >
    <div class="modal-content !max-w-xl">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-lg text-white font-bold">
            Event Details: {{ selectedLog.action }}
          </h3>
          <span class="text-xs text-gray-400 font-mono"
            >ID: {{ selectedLog.id }} | Time:
            {{ new Date(selectedLog.created_at).toLocaleString() }}</span
          >
        </div>
        <button
          class="p-1 text-gray-400 hover:text-white"
          @click="showDetailModal = false"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="space-y-4 text-xs">
        <div>
          <span class="block text-gray-500 font-semibold mb-1 uppercase"
            >User Agent</span
          >
          <div
            class="bg-[color:var(--shell-950)] text-gray-300 p-2 rounded border border-[color:var(--shell-border)] truncate"
          >
            {{ selectedLog.user_agent }}
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <span class="block text-gray-500 font-semibold mb-1 uppercase"
              >Before State</span
            >
            <pre
              class="bg-[color:var(--shell-950)] text-red-400 p-3 rounded border border-[color:var(--shell-border)] overflow-x-auto max-h-48 text-[11px]"
              >{{
                JSON.stringify(selectedLog.before_value, null, 2) || "{}"
              }}</pre
            >
          </div>
          <div>
            <span class="block text-gray-500 font-semibold mb-1 uppercase"
              >After State</span
            >
            <pre
              class="bg-[color:var(--shell-950)] text-green-400 p-3 rounded border border-[color:var(--shell-border)] overflow-x-auto max-h-48 text-[11px]"
              >{{
                JSON.stringify(selectedLog.after_value, null, 2) || "{}"
              }}</pre
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
