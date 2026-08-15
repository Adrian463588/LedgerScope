<script setup lang="ts">
import { onMounted, ref } from "vue";
import { AlertTriangle, Play, CheckCircle2 } from "lucide-vue-next";
import { useUiStore } from "@/stores/ui.store";
import { useCompanyStore } from "@/stores/company.store";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { redFlagApi } = useLedgerScopeApi();
import { useNotification } from "@/composables/useNotification";
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import type { RedFlag } from "@/types";

const ui = useUiStore();
const companyStore = useCompanyStore();
const notification = useNotification();

const isScanning = ref(false);
const scanned = ref(false);
const totalScanned = ref(0);
const totalFlags = ref(0);
const flags = ref<RedFlag[]>([]);
const error = ref<string | null>(null);

async function runScan(): Promise<void> {
  if (!companyStore.activeCompanyId) {
    await companyStore.fetchCompanies();
  }

  const companyId = companyStore.activeCompanyId;
  if (!companyId) {
    error.value = "No company is available for this workspace.";
    return;
  }

  isScanning.value = true;
  scanned.value = false;
  error.value = null;
  try {
    const res = await redFlagApi.scanJournals(companyId);
    totalScanned.value = res.total_journals_scanned;
    totalFlags.value = res.total_flags;
    flags.value = res.flags;
    scanned.value = true;
    if (res.total_flags === 0) {
      notification.success("Scan complete. No anomalies detected!");
    } else {
      notification.info(
        `Scan complete. ${res.total_flags} red flag(s) raised.`,
      );
    }
  } catch (caught) {
    error.value =
      caught instanceof Error
        ? caught.message
        : "Failed to execute journal red-flag scan.";
    notification.error("Failed to execute journal red-flag scan.");
  } finally {
    isScanning.value = false;
  }
}

function getRuleBadgeTone(rule: string): "warning" | "danger" | "info" {
  if (rule === "large_entry") return "danger";
  if (
    rule === "near_threshold_amount" ||
    rule === "unusual_account_combination"
  )
    return "warning";
  return "info";
}

onMounted(() => {
  ui.setBreadcrumbs(["Audit", "Journal Testing"]);
});
</script>

<template>
  <PageHeader
    title="Journal Red-Flag Testing"
    subtitle="Run automated rule-based analytics to identify fraud indicators and transaction anomalies."
  >
    <template #actions>
      <AppButton
        variant="primary"
        :icon="Play"
        :disabled="isScanning"
        @click="runScan"
      >
        {{ isScanning ? "Scanning Journals..." : "Run Scan" }}
      </AppButton>
    </template>
  </PageHeader>

  <div v-if="error" class="p-4 text-sm text-[color:var(--status-danger)]">
    {{ error }}
  </div>

  <div class="p-4 space-y-6">
    <div v-if="scanned" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        class="p-4 rounded-lg bg-[color:var(--shell-900)] border border-[color:var(--shell-border)] flex items-center gap-4"
      >
        <CheckCircle2 class="w-8 h-8 text-green-500" />
        <div>
          <span class="block text-2xl font-bold text-white">{{
            totalScanned
          }}</span>
          <span class="text-xs text-gray-400">Total Journals Scanned</span>
        </div>
      </div>
      <div
        class="p-4 rounded-lg bg-[color:var(--shell-900)] border border-[color:var(--shell-border)] flex items-center gap-4"
      >
        <AlertTriangle
          class="w-8 h-8"
          :class="totalFlags > 0 ? 'text-red-500' : 'text-gray-500'"
        />
        <div>
          <span class="block text-2xl font-bold text-white">{{
            totalFlags
          }}</span>
          <span class="text-xs text-gray-400">Red Flags Raised</span>
        </div>
      </div>
    </div>

    <div v-if="isScanning" class="p-8 text-center text-gray-400">
      <Play class="w-8 h-8 animate-spin mx-auto mb-2 text-primary" />
      Running audit rules engine...
    </div>

    <div
      v-else-if="scanned && flags.length === 0"
      class="p-8 text-center bg-[color:var(--shell-900)] rounded-lg border border-[color:var(--shell-border)]"
    >
      <CheckCircle2 class="w-12 h-12 text-green-500 mx-auto mb-3" />
      <h3 class="text-white font-semibold text-base mb-1">
        No Anomalies Found
      </h3>
      <p class="text-gray-400 text-sm max-w-md mx-auto">
        All analyzed transactions cleared the weekend, large entry, round
        number, and unusual account combinations checks.
      </p>
    </div>

    <SectionPanel
      v-else-if="scanned"
      title="Testing Findings"
      subtitle="Highlighted transactions violating standard risk control checks."
    >
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-300">
          <thead>
            <tr
              class="border-b border-[color:var(--shell-border)] text-gray-400 text-xs uppercase"
            >
              <th class="py-3 px-2">Journal No</th>
              <th class="py-3 px-2">Date</th>
              <th class="py-3 px-2">Triggered Rule</th>
              <th class="py-3 px-2">Message</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(flag, idx) in flags"
              :key="idx"
              class="border-b border-[color:var(--shell-border)] hover:bg-[color:var(--shell-900)]"
            >
              <td class="py-3 px-2 font-medium text-white">
                <router-link
                  :to="`/journal-entries`"
                  class="hover:underline text-primary"
                >
                  {{ flag.journal_number }}
                </router-link>
              </td>
              <td class="py-3 px-2 text-gray-400">{{ flag.journal_date }}</td>
              <td class="py-3 px-2 capitalize">
                <span
                  :class="{
                    'px-1.5 py-0.5 text-xs rounded bg-red-500/20 text-red-400':
                      getRuleBadgeTone(flag.rule) === 'danger',
                    'px-1.5 py-0.5 text-xs rounded bg-orange-500/20 text-orange-400':
                      getRuleBadgeTone(flag.rule) === 'warning',
                    'px-1.5 py-0.5 text-xs rounded bg-blue-500/20 text-blue-400':
                      getRuleBadgeTone(flag.rule) === 'info',
                  }"
                >
                  {{ flag.rule.replace(/_/g, " ") }}
                </span>
              </td>
              <td class="py-3 px-2 text-white">{{ flag.message }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </SectionPanel>
  </div>
</template>
