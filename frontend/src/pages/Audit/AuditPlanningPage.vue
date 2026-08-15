<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { useRoute } from "vue-router";
import {
  Save,
  CheckSquare,
  FileText,
  Settings,
  ShieldAlert,
} from "lucide-vue-next";

import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import { useNotification } from "@/composables/useNotification";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { engagementApi } = useLedgerScopeApi();
import { useUiStore } from "@/stores/ui.store";
import { multiplyDecimal } from "@/utils/decimal";

const route = useRoute();
const ui = useUiStore();
const notification = useNotification();

const engagementId = computed<number | null>(() => {
  const value = route.params["id"];
  return typeof value === "string" && /^\d+$/.test(value)
    ? Number(value)
    : null;
});
const isLoading = ref(true);
const isSaving = ref(false);
const loadError = ref<string | null>(null);

const overallMateriality = ref("0.00");
const performanceMateriality = ref("0.00");
const trivialThreshold = ref("0.00");
const auditStrategy = ref("");
const checklist = ref<
  Array<{ key: string; name: string; is_completed: boolean }>
>([]);

async function fetchAuditPlan(): Promise<void> {
  try {
    isLoading.value = true;
    loadError.value = null;
    if (!engagementId.value) {
      loadError.value =
        "Select a valid engagement before opening audit planning.";
      return;
    }

    const plan = await engagementApi.getAuditPlan(engagementId.value);
    overallMateriality.value = plan.overall_materiality
      ? String(plan.overall_materiality)
      : "0.00";
    performanceMateriality.value = plan.performance_materiality
      ? String(plan.performance_materiality)
      : "0.00";
    trivialThreshold.value = plan.trivial_threshold
      ? String(plan.trivial_threshold)
      : "0.00";
    auditStrategy.value = plan.audit_strategy || "";
    checklist.value = plan.planning_checklist || [];
  } catch (caught) {
    loadError.value =
      caught instanceof Error ? caught.message : "Failed to load audit plan.";
    notification.error("Failed to load audit plan.");
  } finally {
    isLoading.value = false;
  }
}

async function saveAuditPlan(): Promise<void> {
  if (!engagementId.value) return;

  try {
    isSaving.value = true;
    await engagementApi.updateAuditPlan(engagementId.value, {
      overall_materiality: overallMateriality.value,
      performance_materiality: performanceMateriality.value,
      trivial_threshold: trivialThreshold.value,
      audit_strategy: auditStrategy.value,
      planning_checklist: checklist.value,
    });
    notification.success("Audit plan saved successfully.");
  } catch {
    notification.error("Failed to save audit plan.");
  } finally {
    isSaving.value = false;
  }
}

function calculateDefaults(): void {
  performanceMateriality.value = multiplyDecimal(
    overallMateriality.value,
    "0.75",
  );
  trivialThreshold.value = multiplyDecimal(overallMateriality.value, "0.05");
  notification.success(
    "Suggested thresholds calculated: 75% for performance, 5% for trivial.",
  );
}

onMounted(() => {
  ui.setBreadcrumbs(["Audit", "Engagements", "Planning"]);
  void fetchAuditPlan();
});
</script>

<template>
  <PageHeader
    title="Audit Planning & Materiality"
    subtitle="Establish audit strategy, calculate materiality thresholds, and complete planning checklist."
  >
    <template #actions>
      <AppButton
        variant="primary"
        :icon="Save"
        :disabled="isSaving || isLoading"
        @click="saveAuditPlan"
      >
        {{ isSaving ? "Saving..." : "Save Audit Plan" }}
      </AppButton>
    </template>
  </PageHeader>

  <div v-if="isLoading" class="loading-state">
    <p>Loading audit plan...</p>
  </div>

  <div v-else-if="loadError" class="empty-state">
    <p>{{ loadError }}</p>
  </div>

  <div v-else class="plan-grid">
    <div class="main-column">
      <SectionPanel title="Materiality Calculations" :icon="Settings">
        <p class="description">
          Define overall planning materiality (typically 1-5% of benchmark like
          revenues or assets) to derive performance and trivial thresholds.
        </p>

        <div class="materiality-inputs">
          <AppInput
            v-model="overallMateriality"
            label="Overall Materiality"
            amount
            required
          />
          <AppInput
            v-model="performanceMateriality"
            label="Performance Materiality"
            amount
            required
          />
          <AppInput
            v-model="trivialThreshold"
            label="Trivial Threshold (SUD)"
            amount
            required
          />
        </div>

        <div class="calculator-action">
          <AppButton variant="secondary" @click="calculateDefaults"
            >Calculate Suggestions (75% / 5%)</AppButton
          >
        </div>
      </SectionPanel>

      <SectionPanel title="General Audit Strategy" :icon="FileText">
        <p class="description">
          Outline the overall strategy regarding scope, timing, direction of the
          audit, and key audit matters.
        </p>
        <textarea
          v-model="auditStrategy"
          class="strategy-textarea"
          placeholder="Describe the overall audit response to identified risks..."
          rows="8"
        ></textarea>
      </SectionPanel>
    </div>

    <div class="sidebar-column">
      <SectionPanel title="Planning Checklist" :icon="CheckSquare">
        <p class="description">
          All planning stage procedures must be completed before starting
          fieldwork.
        </p>
        <div class="checklist-items">
          <label
            v-for="item in checklist"
            :key="item.key"
            class="checklist-item"
          >
            <input type="checkbox" v-model="item.is_completed" />
            <span :class="{ completed: item.is_completed }">{{
              item.name
            }}</span>
          </label>
        </div>
      </SectionPanel>

      <SectionPanel title="Materiality Guidelines" :icon="ShieldAlert">
        <div class="guideline-card">
          <h4>Benchmarking Benchmarks</h4>
          <ul>
            <li><strong>Profit Before Tax:</strong> 3.0% – 7.0%</li>
            <li><strong>Total Assets:</strong> 0.5% – 2.0%</li>
            <li><strong>Total Revenue:</strong> 0.5% – 2.0%</li>
            <li><strong>Total Equity:</strong> 1.0% – 5.0%</li>
          </ul>
        </div>
      </SectionPanel>
    </div>
  </div>
</template>

<style scoped>
.loading-state {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
  color: var(--text-secondary);
}

.plan-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 24px;
}

.main-column,
.sidebar-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.description {
  color: var(--text-secondary);
  font-size: 14px;
  margin-bottom: 20px;
}

.materiality-inputs {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 16px;
}

.calculator-action {
  display: flex;
  justify-content: flex-end;
}

.strategy-textarea {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid var(--border);
  background-color: var(--surface-alt);
  color: var(--text-primary);
  font-size: 14px;
  resize: vertical;
}

.checklist-items {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.checklist-item {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  transition: background-color 0.2s;
}

.checklist-item:hover {
  background-color: var(--surface-hover);
}

.checklist-item input {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.checklist-item span {
  font-size: 14px;
  color: var(--text-primary);
}

.checklist-item .completed {
  text-decoration: line-through;
  color: var(--text-muted);
}

.guideline-card h4 {
  margin: 0 0 10px;
  font-size: 14px;
  color: var(--text-primary);
}

.guideline-card ul {
  padding-left: 20px;
  margin: 0;
  font-size: 13px;
  color: var(--text-secondary);
  line-height: 1.6;
}

@media (max-width: 1024px) {
  .plan-grid {
    grid-template-columns: 1fr;
  }
}
</style>
