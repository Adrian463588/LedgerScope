<script setup lang="ts">
import type { Component } from 'vue';

import { navigateTo, useRouter } from '@/router';

const props = withDefaults(
  defineProps<{
    href: string;
    icon?: Component;
    compact?: boolean;
  }>(),
  {
    icon: undefined,
    compact: false,
  },
);

const { currentPath } = useRouter();
</script>

<template>
  <a class="nav-link" :class="{ active: currentPath === props.href, compact: props.compact }" :href="props.href" @click.prevent="navigateTo(props.href)">
    <component :is="props.icon" v-if="props.icon" class="nav-link__icon" aria-hidden="true" />
    <span v-if="!props.compact"><slot /></span>
  </a>
</template>

<style scoped>
.nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  border-left: 3px solid transparent;
  border-radius: 4px;
  color: var(--text-inverse-muted);
  padding: 8px 12px;
  text-decoration: none;
  transition: all 140ms ease;
}

.nav-link:hover {
  background: var(--shell-surface);
  color: var(--text-inverse);
}

.nav-link.active {
  background: var(--shell-elevated);
  border-left-color: var(--brand-red);
  color: white;
  font-weight: 500;
}

.nav-link.compact {
  justify-content: center;
  padding: 10px 8px;
}

.nav-link__icon {
  width: 18px;
  height: 18px;
  flex: 0 0 auto;
}
</style>
