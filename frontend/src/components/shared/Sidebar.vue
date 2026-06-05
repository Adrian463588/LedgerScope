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
} from 'lucide-vue-next';
import { computed } from 'vue';

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
  Evidence: Paperclip,
  'Reporting Hub': FileBarChart2,
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
</script>

<template>
  <aside class="sidebar" :class="{ collapsed: ui.sidebarCollapsed, open: ui.mobileSidebarOpen }">
    <div class="sidebar__brand">
      <span class="wordmark"><strong>Ledger</strong><i />Scope</span>
      <span v-if="!ui.sidebarCollapsed" class="version">v1.0</span>
    </div>
    <CompanySwitcher v-if="!ui.sidebarCollapsed" />
    <nav>
      <section v-for="[group, items] in groups" :key="group">
        <p v-if="!ui.sidebarCollapsed">{{ group }}</p>
        <NavLink v-for="item in items" :key="item.path" :href="item.path" :icon="icons[item.label as keyof typeof icons]" :compact="ui.sidebarCollapsed">
          {{ item.label }}
        </NavLink>
      </section>
    </nav>
  </aside>
</template>

<style scoped>
.sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  z-index: 60;
  display: flex;
  width: 240px;
  flex-direction: column;
  background: var(--shell-bg);
  border-right: 1px solid var(--shell-border);
  transition: width 240ms cubic-bezier(0.16, 1, 0.3, 1), transform 240ms cubic-bezier(0.16, 1, 0.3, 1);
}

.sidebar.collapsed {
  width: 64px;
}

.sidebar__brand {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 56px;
  padding: 0 18px;
}

.wordmark {
  color: var(--brand-red);
  font-size: 1rem;
  letter-spacing: 0;
}

.wordmark strong {
  color: white;
  font-weight: 600;
}

.wordmark i {
  display: inline-block;
  width: 1px;
  height: 14px;
  margin: 0 6px;
  background: var(--brand-red);
  vertical-align: middle;
}

.version {
  color: var(--text-inverse-muted);
  font-family: 'IBM Plex Mono', monospace;
  font-size: 0.6875rem;
}

nav {
  flex: 1;
  overflow-y: auto;
  padding: 8px;
}

section {
  display: grid;
  gap: 4px;
  margin-bottom: 8px;
}

p {
  margin: 0;
  color: var(--text-inverse-muted);
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  opacity: 0.5;
  padding: 16px 12px 6px;
  text-transform: uppercase;
}

@media (max-width: 767px) {
  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar.open {
    transform: translateX(0);
  }
}
</style>
