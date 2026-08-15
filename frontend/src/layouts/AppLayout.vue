<script setup lang="ts">
import Sidebar from "@/components/shared/Sidebar.vue";
import Topbar from "@/components/shared/Topbar.vue";
import { useUiStore } from "@/stores/ui.store";

const ui = useUiStore();
</script>

<template>
  <div
    class="flex h-screen w-full overflow-hidden bg-[color:var(--shell-950)]"
    :class="{ collapsed: ui.sidebarCollapsed }"
  >
    <Sidebar class="flex-shrink-0" />
    <div
      v-if="ui.mobileSidebarOpen"
      class="fixed inset-0 z-[55] bg-[color:var(--overlay-backdrop)] md:hidden"
      @click="ui.setMobileSidebar(false)"
    ></div>
    <main
      class="flex-1 flex flex-col min-w-0 overflow-y-auto bg-[color:var(--page-bg)]"
    >
      <Topbar />
      <div class="flex-1 flex flex-col min-w-0">
        <slot />
      </div>
    </main>
  </div>
</template>
