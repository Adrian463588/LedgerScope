<script setup lang="ts">
import { Menu, Search, Settings2 } from 'lucide-vue-next';
import { storeToRefs } from 'pinia';

import { useAuthStore } from '@/stores/auth.store';
import { useUiStore } from '@/stores/ui.store';
import NotificationBell from '@/components/shared/notification-bell.vue';

const ui = useUiStore();
const auth = useAuthStore();
const { breadcrumbs } = storeToRefs(ui);
const { userInitials } = storeToRefs(auth);
</script>

<template>
  <header class="topbar">
    <div class="topbar__left">
      <button aria-label="Toggle navigation" @click="ui.setMobileSidebar(true); ui.toggleSidebar()"><Menu aria-hidden="true" /></button>
      <nav aria-label="Breadcrumb">
        <ol>
          <li v-for="(crumb, index) in breadcrumbs" :key="crumb">
            <span>{{ crumb }}</span>
            <span v-if="index < breadcrumbs.length - 1" class="separator">/</span>
          </li>
        </ol>
      </nav>
    </div>
    <div class="topbar__right">
      <label class="search">
        <Search aria-hidden="true" />
        <input placeholder="Search records..." />
      </label>
      <NotificationBell />
      <button class="icon-button" aria-label="Settings"><Settings2 aria-hidden="true" /></button>
      <div class="avatar" :title="auth.user?.name ?? 'User'" aria-label="Current user">{{ userInitials }}</div>
    </div>
  </header>
</template>

<style scoped>
.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  display: flex;
  height: 56px;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--border);
  background: white;
  padding: 0 32px;
}

.topbar__left,
.topbar__right,
ol,
li,
.search {
  display: flex;
  align-items: center;
}

.topbar__left,
.topbar__right {
  gap: 16px;
}

button {
  display: grid;
  width: 36px;
  height: 36px;
  place-items: center;
  border: 0;
  border-radius: 4px;
  background: transparent;
  color: var(--text-secondary);
}

button:hover {
  background: var(--surface-hover);
  color: var(--text-primary);
}

svg {
  width: 18px;
  height: 18px;
}

ol {
  gap: 8px;
  margin: 0;
  padding: 0;
  list-style: none;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

li:last-child {
  color: var(--text-primary);
  font-weight: 500;
}

.separator {
  color: var(--text-muted);
  margin-left: 8px;
}

.search {
  height: 36px;
  gap: 8px;
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 0 10px;
}

.search input {
  width: 180px;
  border: 0;
  outline: 0;
}

.icon-button {
  position: relative;
}

.icon-button span {
  position: absolute;
  top: 3px;
  right: 3px;
  display: grid;
  width: 16px;
  height: 16px;
  place-items: center;
  border-radius: 50%;
  background: var(--brand-red);
  color: white;
  font: 500 0.625rem 'IBM Plex Mono', monospace;
}

.avatar {
  display: grid;
  width: 32px;
  height: 32px;
  place-items: center;
  border: 1px solid var(--border);
  border-radius: 50%;
  background: var(--surface-alt);
  color: var(--text-primary);
  font-weight: 700;
}

@media (max-width: 900px) {
  .search {
    display: none;
  }
}
</style>
