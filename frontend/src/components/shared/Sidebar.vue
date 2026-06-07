<script setup lang="ts">
import {
  Activity,
  AlertTriangle,
  BarChart3,
  BookOpen,
  Briefcase,
  Building2,
  FileBarChart2,
  FileText,
  LayoutDashboard,
  List,
  Paperclip,
  Scale,
  Settings2,
  ShieldCheck,
  Menu,
  X,
  Users,
  History,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import CompanySwitcher from './CompanySwitcher.vue';
import NavLink from './NavLink.vue';
import { routes } from '@/router';
import { useUiStore } from '@/stores/ui.store';

const ui = useUiStore();

const icons = {
  Dashboard: LayoutDashboard,
  Companies: Building2,
  'Chart of Accounts': List,
  'Journal Entries': BookOpen,
  'Trial Balance': Scale,
  'Quarterly Closing': Activity,
  Reconciliation: ShieldCheck,
  'Financial Statements': FileBarChart2,
  'Statement Builder': FileText,
  'Ratio Analysis': BarChart3,
  'Audit Engagements': Briefcase,
  'Risk Assessment': AlertTriangle,
  'Risk Control Matrix': ShieldCheck,
  'Audit Program': FileText,
  'Working Paper': FileText,
  'Audit Findings': AlertTriangle,
  'Journal Red-Flags': AlertTriangle,
  Evidence: Paperclip,
  'Reporting Hub': FileBarChart2,
  'User Management': Users,
  'Audit Trail': History,
  Settings: Settings2,
};

const groups = computed(() => {
  const result = new Map<string, typeof routes>();
  routes
    .filter((route) => route.layout === 'app' && route.group)
    .forEach((route) => {
      const group = route.group ?? 'Main';
      result.set(group, [...(result.get(group) ?? []), route]);
    });
  return Array.from(result.entries());
});

const isMobileMenuOpen = ref(false);

function toggleMobileMenu() {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
}
</script>

<template>
  <!-- Mobile Toggle Button (Visible only on small screens) -->
  <button 
    class="md:hidden fixed top-4 left-4 z-[60] p-2 text-[var(--text-inverse-muted)] hover:text-white"
    @click="toggleMobileMenu"
    aria-label="Toggle Sidebar"
  >
    <Menu v-if="!isMobileMenuOpen" class="w-6 h-6" />
  </button>

  <!-- Mobile Backdrop Overlay -->
  <div 
    v-if="isMobileMenuOpen" 
    class="fixed inset-0 z-40 bg-black/50 md:hidden" 
    @click="isMobileMenuOpen = false"
  ></div>

  <!-- Sidebar -->
  <aside 
    class="fixed inset-y-0 left-0 z-50 flex flex-col transform transition-transform duration-200 ease-in-out md:static"
    :class="[
      isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
      ui.sidebarCollapsed ? 'w-16' : 'w-[240px]'
    ]"
    style="background: var(--shell-bg); border-right: 1px solid var(--shell-border);"
  >
    <!-- Brand -->
    <div class="flex items-center justify-between min-h-[56px] px-[18px]">
      <span class="text-base tracking-normal text-[var(--brand-red)]">
        <strong class="text-white font-semibold">Ledger</strong><i class="inline-block w-px h-[14px] mx-[6px] align-middle bg-[var(--brand-red)]" />Scope
      </span>
      <span v-if="!ui.sidebarCollapsed" class="text-[var(--text-inverse-muted)] font-mono text-[0.6875rem]">v1.0</span>
      <button v-if="isMobileMenuOpen" class="md:hidden text-[var(--text-inverse-muted)]" @click="isMobileMenuOpen = false">
        <X class="w-5 h-5" />
      </button>
    </div>

    <CompanySwitcher v-if="!ui.sidebarCollapsed" />

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto p-2">
      <section v-for="[group, items] in groups" :key="group" class="grid gap-1 mb-2">
        <p v-if="!ui.sidebarCollapsed" class="m-0 text-[var(--text-inverse-muted)] text-[0.625rem] font-semibold tracking-[0.08em] opacity-50 px-3 pt-4 pb-1.5 uppercase">
          {{ group }}
        </p>
        <NavLink 
          v-for="item in items" 
          :key="item.path" 
          :href="item.path" 
          :icon="icons[item.label as keyof typeof icons]" 
          :compact="ui.sidebarCollapsed"
          @click="isMobileMenuOpen = false"
        >
          {{ item.label }}
        </NavLink>
      </section>
    </nav>
  </aside>
</template>
