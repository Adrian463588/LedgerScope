<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { Plus, Trash } from 'lucide-vue-next';
import { auditControlsApi } from '@/api/endpoints';
import { useNotification } from '@/composables/useNotification';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';

const props = defineProps<{
  engagementId: number;
  controlId: number;
}>();

const notification = useNotification();
const risks = ref<any[]>([]);
const isLoading = ref(false);

const riskName = ref('');
const likelihood = ref('medium');
const impact = ref('medium');
const residualRisk = ref('medium');

async function loadRisks() {
  if (!props.controlId) return;
  isLoading.value = true;
  try {
    risks.value = await auditControlsApi.listRisks(props.engagementId, props.controlId);
  } catch (error) {
    notification.error('Failed to load control risks.');
  } finally {
    isLoading.value = false;
  }
}

async function handleAddRisk() {
  if (!riskName.value.trim()) {
    notification.error('Risk name is required.');
    return;
  }
  try {
    const newRisk = await auditControlsApi.addRisk(props.engagementId, props.controlId, {
      risk_name: riskName.value,
      likelihood: likelihood.value,
      impact: impact.value,
      residual_risk: residualRisk.value,
    });
    risks.value.unshift(newRisk);
    riskName.value = '';
    notification.success('Risk linked successfully.');
  } catch (error: any) {
    notification.error(error.message || 'Failed to link risk.');
  }
}

async function handleDeleteRisk(riskId: number) {
  try {
    await auditControlsApi.deleteRisk(props.engagementId, props.controlId, riskId);
    risks.value = risks.value.filter((r) => r.id !== riskId);
    notification.success('Risk removed.');
  } catch (error: any) {
    notification.error(error.message || 'Failed to remove risk.');
  }
}

watch(() => props.controlId, () => {
  loadRisks();
}, { immediate: true });
</script>

<template>
  <div class="control-risks-section">
    <h3 class="section-title text-white mb-4">Linked Control Risks</h3>

    <div class="quick-add-risk bg-[color:var(--shell-900)] p-4 rounded-lg border border-[color:var(--shell-border)] mb-4">
      <h4 class="text-sm text-gray-300 font-medium mb-3">Add Control Risk</h4>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div class="md:col-span-2">
          <AppInput v-model="riskName" label="Risk Name" placeholder="e.g. Unauthorized journals bypass approval" required />
        </div>
        <div>
          <label class="block text-xs text-gray-400 font-medium mb-1">Likelihood / Impact / Residual</label>
          <div class="flex gap-1">
            <select v-model="likelihood" class="bg-[color:var(--shell-950)] text-white text-xs rounded border border-[color:var(--shell-border)] p-1.5 flex-1">
              <option value="low">Low L</option>
              <option value="medium">Med L</option>
              <option value="high">High L</option>
            </select>
            <select v-model="impact" class="bg-[color:var(--shell-950)] text-white text-xs rounded border border-[color:var(--shell-border)] p-1.5 flex-1">
              <option value="low">Low I</option>
              <option value="medium">Med I</option>
              <option value="high">High I</option>
            </select>
            <select v-model="residualRisk" class="bg-[color:var(--shell-950)] text-white text-xs rounded border border-[color:var(--shell-border)] p-1.5 flex-1">
              <option value="low">Low R</option>
              <option value="medium">Med R</option>
              <option value="high">High R</option>
            </select>
          </div>
        </div>
        <AppButton variant="primary" :icon="Plus" class="w-full" @click="handleAddRisk">Add Risk</AppButton>
      </div>
    </div>

    <div v-if="isLoading" class="text-gray-400 text-sm">Loading risks...</div>
    <div v-else-if="risks.length === 0" class="text-gray-500 text-sm italic">No risks linked to this control yet.</div>
    <div v-else class="risks-list space-y-2">
      <div v-for="risk in risks" :key="risk.id" class="flex justify-between items-center p-3 rounded bg-[color:var(--shell-950)] border border-[color:var(--shell-border)]">
        <div>
          <div class="text-white text-sm font-semibold">{{ risk.risk_name }}</div>
          <div class="flex gap-2 mt-1 text-xs">
            <span class="px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-400">Likelihood: {{ risk.likelihood }}</span>
            <span class="px-1.5 py-0.5 rounded bg-orange-500/20 text-orange-400">Impact: {{ risk.impact }}</span>
            <span class="px-1.5 py-0.5 rounded bg-purple-500/20 text-purple-400">Residual: {{ risk.residual_risk || 'medium' }}</span>
          </div>
        </div>
        <button class="p-1.5 text-red-400 hover:bg-red-500/10 rounded" @click="handleDeleteRisk(risk.id)">
          <Trash class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>
