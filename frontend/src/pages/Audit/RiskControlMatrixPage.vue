<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { Plus, Trash, Check, ShieldAlert } from 'lucide-vue-next';
import { useUiStore } from '@/stores/ui.store';
import { useCompanyStore } from '@/stores/company.store';
import { engagementApi, auditControlsApi } from '@/api/endpoints';
import { useNotification } from '@/composables/useNotification';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import ControlRiskList from '@/components/audit/control-risk-list.vue';

const route = useRoute();
const ui = useUiStore();
const companyStore = useCompanyStore();
const notification = useNotification();

const controls = ref<any[]>([]);
const selectedControl = ref<any>(null);
const isLoading = ref(true);
const engagementId = ref<number | null>(null);

const showCreateModal = ref(false);
const newName = ref('');
const newType = ref('preventive');
const newOwner = ref('');
const newDesc = ref('');

async function loadEngagement() {
  try {
    isLoading.value = true;
    const paramId = route.params['id'];
    if (paramId) {
      engagementId.value = Number(paramId);
      await fetchControls();
    } else {
      const companyId = companyStore.activeCompany?.id ?? 1;
      const engagements = await engagementApi.list(companyId);
      const firstEng = engagements[0];
      if (firstEng && firstEng.id !== undefined) {
        engagementId.value = firstEng.id;
        await fetchControls();
      }
    }
  } catch (error) {
    notification.error('Failed to load active engagement.');
  } finally {
    isLoading.value = false;
  }
}

async function fetchControls() {
  if (!engagementId.value) return;
  try {
    controls.value = await auditControlsApi.listControls(engagementId.value);
    if (controls.value.length > 0 && !selectedControl.value) {
      selectedControl.value = controls.value[0];
    }
  } catch (error) {
    notification.error('Failed to load internal controls.');
  }
}

async function handleCreateControl() {
  if (!engagementId.value || !newName.value) return;
  try {
    const newControl = await auditControlsApi.createControl(engagementId.value, {
      name: newName.value,
      control_type: newType.value,
      owner: newOwner.value,
      description: newDesc.value,
      effectiveness: 'not_tested',
    });
    controls.value.unshift(newControl);
    selectedControl.value = newControl;
    newName.value = '';
    newOwner.value = '';
    newDesc.value = '';
    showCreateModal.value = false;
    notification.success('Internal control recorded.');
  } catch (error: any) {
    notification.error(error.message || 'Failed to record control.');
  }
}

async function updateEffectiveness(control: any, val: string) {
  if (!engagementId.value) return;
  try {
    await auditControlsApi.updateControl(engagementId.value, control.id, { effectiveness: val });
    control.effectiveness = val;
    notification.success(`Effectiveness marked as ${val.replace('_', ' ')}`);
  } catch (error) {
    notification.error('Failed to update control effectiveness.');
  }
}

async function handleDeleteControl(id: number) {
  if (!engagementId.value) return;
  try {
    await auditControlsApi.deleteControl(engagementId.value, id);
    controls.value = controls.value.filter((c) => c.id !== id);
    if (selectedControl.value?.id === id) {
      selectedControl.value = controls.value[0] || null;
    }
    notification.success('Control deleted.');
  } catch (error) {
    notification.error('Failed to delete control.');
  }
}

onMounted(() => {
  ui.setBreadcrumbs(['Audit', 'Risk Control Matrix']);
  void loadEngagement();
});
</script>

<template>
  <PageHeader title="Risk Control Matrix (RCM)" subtitle="Map risks, internal controls, owners, and effectiveness testing.">
    <template #actions>
      <AppButton variant="primary" :icon="Plus" @click="showCreateModal = true">New Control</AppButton>
    </template>
  </PageHeader>

  <div v-if="isLoading" class="p-6 text-gray-400">Loading Risk Control Matrix...</div>
  <div v-else-if="controls.length === 0" class="p-8 text-center text-gray-500 italic">
    No controls recorded yet. Click 'New Control' to begin.
  </div>
  <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-4">
    <div class="lg:col-span-2 space-y-4">
      <SectionPanel title="Controls List" subtitle="Configure and test engagement internal controls">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-300">
            <thead>
              <tr class="border-b border-[color:var(--shell-border)] text-gray-400 text-xs uppercase">
                <th class="py-3 px-2">Control Name</th>
                <th class="py-3 px-2">Type</th>
                <th class="py-3 px-2">Owner</th>
                <th class="py-3 px-2">Testing Status</th>
                <th class="py-3 px-2 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="ctrl in controls" 
                :key="ctrl.id"
                class="border-b border-[color:var(--shell-border)] hover:bg-[color:var(--shell-900)] cursor-pointer"
                :class="{ 'bg-[color:var(--shell-900)]': selectedControl?.id === ctrl.id }"
                @click="selectedControl = ctrl"
              >
                <td class="py-3 px-2 font-medium text-white">{{ ctrl.name }}</td>
                <td class="py-3 px-2 capitalize">{{ ctrl.control_type }}</td>
                <td class="py-3 px-2">{{ ctrl.owner || '—' }}</td>
                <td class="py-3 px-2">
                  <select 
                    :value="ctrl.effectiveness"
                    class="bg-[color:var(--shell-950)] text-white text-xs rounded border border-[color:var(--shell-border)] p-1"
                    @change="updateEffectiveness(ctrl, ($event.target as HTMLSelectElement).value)"
                    @click.stop
                  >
                    <option value="not_tested">Not Tested</option>
                    <option value="effective">Effective</option>
                    <option value="partially_effective">Partially Effective</option>
                    <option value="ineffective">Ineffective</option>
                  </select>
                </td>
                <td class="py-3 px-2 text-right">
                  <button class="text-red-400 hover:text-red-300 p-1" @click.stop="handleDeleteControl(ctrl.id)">
                    <Trash class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </SectionPanel>
    </div>

    <div v-if="selectedControl && engagementId" class="space-y-4">
      <SectionPanel :title="selectedControl.name" subtitle="Control definition & details">
        <div class="text-sm text-gray-300 space-y-3">
          <div>
            <span class="block text-xs text-gray-500 font-semibold uppercase">Type</span>
            <span class="capitalize text-white">{{ selectedControl.control_type }}</span>
          </div>
          <div>
            <span class="block text-xs text-gray-500 font-semibold uppercase">Description</span>
            <p class="text-white bg-[color:var(--shell-950)] p-3 rounded border border-[color:var(--shell-border)] whitespace-pre-wrap">
              {{ selectedControl.description || 'No description provided.' }}
            </p>
          </div>
        </div>
      </SectionPanel>
      <ControlRiskList :engagement-id="engagementId" :control-id="selectedControl.id" />
    </div>
  </div>

  <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
    <div class="modal-content">
      <h3 class="text-lg text-white font-semibold mb-4">Record New Internal Control</h3>
      <div class="form-group">
        <AppInput v-model="newName" label="Control Name" placeholder="e.g. Segregation of duties for journal approval" required />
      </div>
      <div class="form-group-row">
        <div class="form-group">
          <label>Control Type</label>
          <select v-model="newType" class="form-select">
            <option value="preventive">Preventive</option>
            <option value="detective">Detective</option>
            <option value="corrective">Corrective</option>
          </select>
        </div>
        <div class="form-group">
          <AppInput v-model="newOwner" label="Control Owner" placeholder="e.g. Finance Controller" />
        </div>
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea v-model="newDesc" class="form-textarea" placeholder="Describe control mechanism..." rows="3"></textarea>
      </div>
      <div class="modal-buttons">
        <AppButton variant="secondary" @click="showCreateModal = false">Cancel</AppButton>
        <AppButton variant="primary" @click="handleCreateControl">Create Control</AppButton>
      </div>
    </div>
  </div>
</template>
