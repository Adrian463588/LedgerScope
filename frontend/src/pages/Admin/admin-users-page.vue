<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Plus, Shield, UserX, UserCheck, Trash, X } from 'lucide-vue-next';
import { useUiStore } from '@/stores/ui.store';
import { adminApi } from '@/api/endpoints';
import { useNotification } from '@/composables/useNotification';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import SectionPanel from '@/components/shared/SectionPanel.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppTable from '@/components/ui/AppTable.vue';
import PageHeader from '@/components/ui/PageHeader.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';

const ui = useUiStore();
const notification = useNotification();
const confirmDialog = useConfirmDialog();

const users = ref<any[]>([]);
const roles = ref<any[]>([]);
const isLoading = ref(true);
const selectedUser = ref<any>(null);
const showDetailModal = ref(false);
const newRoleId = ref<number | null>(null);

async function loadData() {
  isLoading.value = true;
  try {
    const res = await adminApi.listUsers();
    // Paginated response usually contains a 'data' array
    users.value = Array.isArray(res) ? res : (res as any).data || [];
    roles.value = await adminApi.listRoles();
  } catch (error) {
    notification.error('Failed to load user management data.');
  } finally {
    isLoading.value = false;
  }
}

async function handleSuspend(user: any) {
  try {
    await adminApi.suspendUser(user.id);
    user.status = 'suspended';
    notification.success('User suspended.');
  } catch (error) {
    notification.error('Failed to suspend user.');
  }
}

async function handleActivate(user: any) {
  try {
    await adminApi.activateUser(user.id);
    user.status = 'active';
    notification.success('User activated.');
  } catch (error) {
    notification.error('Failed to activate user.');
  }
}

async function handleDelete(user: any) {
  const confirmed = await confirmDialog.confirm({
    title: 'Delete User',
    message: `Are you sure you want to permanently delete user ${user.name}? This action cannot be undone.`,
    tone: 'danger',
    confirmLabel: 'Delete',
  });
  if (!confirmed) return;
  try {
    await adminApi.deleteUser(user.id);
    users.value = users.value.filter((u) => u.id !== user.id);
    showDetailModal.value = false;
    notification.success('User deleted.');
  } catch (error) {
    notification.error('Failed to delete user.');
  }
}

async function handleAssignRole() {
  if (!selectedUser.value || !newRoleId.value) return;
  try {
    const updatedUser = await adminApi.assignRole(selectedUser.value.id, newRoleId.value);
    selectedUser.value.roles = updatedUser.roles;
    notification.success('Role assigned successfully.');
  } catch (error) {
    notification.error('Failed to assign role.');
  }
}

async function handleRevokeRole(roleId: number) {
  if (!selectedUser.value) return;
  try {
    const updatedUser = await adminApi.revokeRole(selectedUser.value.id, roleId);
    selectedUser.value.roles = updatedUser.roles;
    notification.success('Role revoked.');
  } catch (error) {
    notification.error('Failed to revoke role.');
  }
}

function openDetail(user: any) {
  selectedUser.value = user;
  showDetailModal.value = true;
}

onMounted(() => {
  ui.setBreadcrumbs(['Admin', 'User Management']);
  void loadData();
});
</script>

<template>
  <PageHeader title="User Management" subtitle="Manage system users, assign security roles, and control account statuses.">
    <template #actions>
      <AppButton variant="primary" :icon="Plus" href="/admin/users/invite">Invite User</AppButton>
    </template>
  </PageHeader>

  <div class="p-4">
    <SectionPanel title="All Users" subtitle="System roles, permissions and account activity">
      <AppTable
        :loading="isLoading"
        :columns="[
          { key: 'name', label: 'Name' },
          { key: 'email', label: 'Email' },
          { key: 'status', label: 'Status', isStatus: true },
          { key: 'roles', label: 'Assigned Roles' },
        ]"
        :data="users"
      >
        <template #cell-name="{ row }">
          <button class="hover:underline text-left text-white font-semibold" @click="openDetail(row)">
            {{ row.name }}
          </button>
        </template>
        <template #cell-roles="{ row }">
          <div class="flex flex-wrap gap-1">
            <span v-for="r in row.roles" :key="r.id" class="px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-300 border border-slate-700">
              {{ r.display_name }}
            </span>
            <span v-if="!row.roles || row.roles.length === 0" class="text-xs text-gray-500 italic">No roles</span>
          </div>
        </template>
      </AppTable>
    </SectionPanel>
  </div>

  <div v-if="showDetailModal && selectedUser" class="modal-overlay" @click.self="showDetailModal = false">
    <div class="modal-content">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-lg text-white font-bold">{{ selectedUser.name }}</h3>
          <span class="text-xs text-gray-400 font-mono">{{ selectedUser.email }}</span>
        </div>
        <button class="p-1 text-gray-400 hover:text-white" @click="showDetailModal = false">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="space-y-4 text-sm text-gray-300">
        <div class="flex justify-between items-center bg-[color:var(--shell-950)] p-3 rounded border border-[color:var(--shell-border)]">
          <div>
            <span class="block text-xs text-gray-500 font-semibold uppercase">Account Status</span>
            <StatusBadge :status="selectedUser.status" />
          </div>
          <div class="flex gap-2">
            <AppButton v-if="selectedUser.status === 'active'" variant="secondary" :icon="UserX" @click="handleSuspend(selectedUser)">Suspend</AppButton>
            <AppButton v-else variant="secondary" :icon="UserCheck" @click="handleActivate(selectedUser)">Activate</AppButton>
            <AppButton variant="secondary" class="text-red-400 hover:bg-red-500/10" :icon="Trash" @click="handleDelete(selectedUser)">Delete</AppButton>
          </div>
        </div>

        <div>
          <h4 class="text-xs text-gray-500 font-semibold uppercase mb-2">Role Management</h4>
          <div class="flex flex-wrap gap-1.5 mb-3">
            <span v-for="r in selectedUser.roles" :key="r.id" class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-800 text-slate-200 border border-slate-700">
              {{ r.display_name }}
              <button class="text-gray-400 hover:text-red-400" @click="handleRevokeRole(r.id)"><X class="w-3 h-3" /></button>
            </span>
          </div>
          <div class="flex gap-2 items-end">
            <select v-model="newRoleId" class="form-select flex-1">
              <option :value="null">Select role to assign...</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.display_name }}</option>
            </select>
            <AppButton variant="primary" :icon="Shield" @click="handleAssignRole">Assign</AppButton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
