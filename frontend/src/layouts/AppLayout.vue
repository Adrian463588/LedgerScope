<script setup lang="ts">
import Sidebar from '@/components/shared/Sidebar.vue';
import Topbar from '@/components/shared/Topbar.vue';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();
</script>

<template>
  <div class="app-shell" :class="{ collapsed: ui.sidebarCollapsed }">
    <Sidebar />
    <div v-if="ui.mobileSidebarOpen" class="mobile-backdrop" @click="ui.setMobileSidebar(false)" />
    <main id="main-content">
      <Topbar />
      <div class="app-shell__content page-content">
        <slot />
      </div>
    </main>
  </div>
</template>

<style scoped>
.app-shell {
  min-height: 100vh;
  background: var(--page-bg);
}

main {
  min-height: 100vh;
  margin-left: 240px;
  transition: margin-left 240ms cubic-bezier(0.16, 1, 0.3, 1);
}

.collapsed main {
  margin-left: 64px;
}

.app-shell__content {
  display: grid;
  width: min(1440px, 100%);
  gap: 32px;
  margin: 0 auto;
  padding: 32px;
}

.mobile-backdrop {
  position: fixed;
  inset: 0;
  z-index: 55;
  background: rgb(12 13 16 / 42%);
}

@media (max-width: 767px) {
  main,
  .collapsed main {
    margin-left: 0;
  }

  .app-shell__content {
    padding: 24px;
  }
}
</style>
