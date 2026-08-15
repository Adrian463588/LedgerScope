<script setup lang="ts">
import { onMounted, reactive, ref } from "vue";
import { Plus, Shield, UserX, UserCheck, Trash, X } from "lucide-vue-next";
import { useUiStore } from "@/stores/ui.store";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { adminApi } = useLedgerScopeApi();
import { useNotification } from "@/composables/useNotification";
import { useConfirmDialog } from "@/composables/useConfirmDialog";
import SectionPanel from "@/components/shared/SectionPanel.vue";
import AppButton from "@/components/ui/AppButton.vue";
import AppInput from "@/components/ui/AppInput.vue";
import AppModal from "@/components/ui/AppModal.vue";
import AppTable from "@/components/ui/AppTable.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import StatusBadge from "@/components/ui/StatusBadge.vue";
import type { AdminRole, AdminUser } from "@/types";
import type { TableRow } from "@/components/ui/AppTable.vue";

const ui = useUiStore();
const notification = useNotification();
const confirmDialog = useConfirmDialog();

const users = ref<AdminUser[]>([]);
const roles = ref<AdminRole[]>([]);
const isLoading = ref(true);
const selectedUser = ref<AdminUser | null>(null);
const showDetailModal = ref(false);
const showInviteModal = ref(false);
const newRoleId = ref<number | null>(null);
const isInviting = ref(false);
const inviteError = ref<string | null>(null);
const inviteForm = reactive({
  email: "",
  name: "",
  roleId: null as number | null,
});

async function loadData() {
  isLoading.value = true;
  try {
    const res = await adminApi.listUsers();
    users.value = res.items;
    roles.value = await adminApi.listRoles();
  } catch {
    notification.error("Failed to load user management data.");
  } finally {
    isLoading.value = false;
  }
}

async function handleSuspend(user: AdminUser): Promise<void> {
  try {
    await adminApi.suspendUser(user.id);
    user.status = "suspended";
    notification.success("User suspended.");
  } catch {
    notification.error("Failed to suspend user.");
  }
}

async function handleActivate(user: AdminUser): Promise<void> {
  try {
    await adminApi.activateUser(user.id);
    user.status = "active";
    notification.success("User activated.");
  } catch {
    notification.error("Failed to activate user.");
  }
}

async function handleDelete(user: AdminUser): Promise<void> {
  const confirmed = await confirmDialog.confirm({
    title: "Delete User",
    message: `Are you sure you want to permanently delete user ${user.name}? This action cannot be undone.`,
    tone: "danger",
    confirmLabel: "Delete",
  });
  if (!confirmed) return;
  try {
    await adminApi.deleteUser(user.id);
    users.value = users.value.filter((u) => u.id !== user.id);
    showDetailModal.value = false;
    notification.success("User deleted.");
  } catch {
    notification.error("Failed to delete user.");
  }
}

async function handleAssignRole() {
  if (!selectedUser.value || !newRoleId.value) return;
  try {
    const updatedUser = await adminApi.assignRole(
      selectedUser.value.id,
      newRoleId.value,
    );
    selectedUser.value.roles = updatedUser.roles;
    notification.success("Role assigned successfully.");
  } catch {
    notification.error("Failed to assign role.");
  }
}

function openInvite(): void {
  inviteForm.email = "";
  inviteForm.name = "";
  inviteForm.roleId = roles.value[0]?.id ?? null;
  inviteError.value = null;
  showInviteModal.value = true;
}

async function handleInvite(): Promise<void> {
  if (!inviteForm.email.trim() || !inviteForm.roleId) {
    inviteError.value = "Email and role are required.";
    return;
  }

  isInviting.value = true;
  inviteError.value = null;
  try {
    await adminApi.inviteUser({
      email: inviteForm.email.trim(),
      name: inviteForm.name.trim() || undefined,
      role_id: inviteForm.roleId,
    });
    showInviteModal.value = false;
    notification.success("Invitation created successfully.");
    await loadData();
  } catch (caught) {
    inviteError.value =
      caught instanceof Error ? caught.message : "Unable to create invitation.";
  } finally {
    isInviting.value = false;
  }
}

async function handleRevokeRole(roleId: number) {
  if (!selectedUser.value) return;
  try {
    const updatedUser = await adminApi.revokeRole(
      selectedUser.value.id,
      roleId,
    );
    selectedUser.value.roles = updatedUser.roles;
    notification.success("Role revoked.");
  } catch {
    notification.error("Failed to revoke role.");
  }
}

function openDetail(user: AdminUser): void {
  selectedUser.value = user;
  showDetailModal.value = true;
}

function handleUserRow(row: TableRow): void {
  if (typeof row["id"] === "number") {
    const user = users.value.find((candidate) => candidate.id === row["id"]);
    if (user) openDetail(user);
  }
}

function asAdminUser(row: TableRow): AdminUser {
  return row as unknown as AdminUser;
}

function rowRoles(row: TableRow): AdminRole[] {
  return Array.isArray(row["roles"]) ? (row["roles"] as AdminRole[]) : [];
}

onMounted(() => {
  ui.setBreadcrumbs(["Admin", "User Management"]);
  void loadData();
});
</script>

<template>
  <PageHeader
    title="User Management"
    subtitle="Manage system users, assign security roles, and control account statuses."
  >
    <template #actions>
      <AppButton variant="primary" :icon="Plus" @click="openInvite"
        >Invite User</AppButton
      >
    </template>
  </PageHeader>

  <div class="p-4">
    <SectionPanel
      title="All Users"
      subtitle="System roles, permissions and account activity"
    >
      <AppTable
        :loading="isLoading"
        :columns="[
          { key: 'name', label: 'Name' },
          { key: 'email', label: 'Email' },
          { key: 'status', label: 'Status', isStatus: true },
          { key: 'roles', label: 'Assigned Roles' },
        ]"
        :data="users"
        @row-click="handleUserRow"
      >
        <template #cell-name="{ row }">
          <button
            class="hover:underline text-left text-white font-semibold"
            @click="openDetail(asAdminUser(row))"
          >
            {{ row["name"] }}
          </button>
        </template>
        <template #cell-roles="{ row }">
          <div class="flex flex-wrap gap-1">
            <span
              v-for="r in rowRoles(row)"
              :key="r.id"
              class="px-2 py-0.5 rounded text-xs bg-slate-800 text-slate-300 border border-slate-700"
            >
              {{ r.display_name }}
            </span>
            <span
              v-if="rowRoles(row).length === 0"
              class="text-xs text-gray-500 italic"
              >No roles</span
            >
          </div>
        </template>
      </AppTable>
    </SectionPanel>
  </div>

  <AppModal
    :open="showInviteModal"
    title="Invite user"
    @close="showInviteModal = false"
  >
    <div class="invite-form">
      <AppInput v-model="inviteForm.name" label="Name" />
      <AppInput
        v-model="inviteForm.email"
        label="Email"
        type="email"
        required
      />
      <label class="form-label">
        <span>Role</span>
        <select v-model="inviteForm.roleId" class="form-select">
          <option :value="null">Select role</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.display_name }}
          </option>
        </select>
      </label>
    </div>
    <p v-if="inviteError" class="invite-error">{{ inviteError }}</p>
    <template #footer>
      <AppButton @click="showInviteModal = false">Cancel</AppButton>
      <AppButton variant="primary" :loading="isInviting" @click="handleInvite">
        Send invitation
      </AppButton>
    </template>
  </AppModal>

  <div
    v-if="showDetailModal && selectedUser"
    class="modal-overlay"
    @click.self="showDetailModal = false"
  >
    <div class="modal-content">
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-lg text-white font-bold">{{ selectedUser.name }}</h3>
          <span class="text-xs text-gray-400 font-mono">{{
            selectedUser.email
          }}</span>
        </div>
        <button
          class="p-1 text-gray-400 hover:text-white"
          @click="showDetailModal = false"
        >
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="space-y-4 text-sm text-gray-300">
        <div
          class="flex justify-between items-center bg-[color:var(--shell-950)] p-3 rounded border border-[color:var(--shell-border)]"
        >
          <div>
            <span class="block text-xs text-gray-500 font-semibold uppercase"
              >Account Status</span
            >
            <StatusBadge :status="selectedUser.status" />
          </div>
          <div class="flex gap-2">
            <AppButton
              v-if="selectedUser.status === 'active'"
              variant="secondary"
              :icon="UserX"
              @click="handleSuspend(selectedUser)"
              >Suspend</AppButton
            >
            <AppButton
              v-else
              variant="secondary"
              :icon="UserCheck"
              @click="handleActivate(selectedUser)"
              >Activate</AppButton
            >
            <AppButton
              variant="secondary"
              class="text-red-400 hover:bg-red-500/10"
              :icon="Trash"
              @click="handleDelete(selectedUser)"
              >Delete</AppButton
            >
          </div>
        </div>

        <div>
          <h4 class="text-xs text-gray-500 font-semibold uppercase mb-2">
            Role Management
          </h4>
          <div class="flex flex-wrap gap-1.5 mb-3">
            <span
              v-for="r in selectedUser.roles"
              :key="r.id"
              class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-800 text-slate-200 border border-slate-700"
            >
              {{ r.display_name }}
              <button
                class="text-gray-400 hover:text-red-400"
                @click="handleRevokeRole(r.id)"
              >
                <X class="w-3 h-3" />
              </button>
            </span>
          </div>
          <div class="flex gap-2 items-end">
            <select v-model="newRoleId" class="form-select flex-1">
              <option :value="null">Select role to assign...</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.display_name }}
              </option>
            </select>
            <AppButton
              variant="primary"
              :icon="Shield"
              @click="handleAssignRole"
              >Assign</AppButton
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
