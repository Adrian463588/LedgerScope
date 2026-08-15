<script setup lang="ts">
import { computed } from "vue";

import ConfirmDialog from "@/components/ui/ConfirmDialog.vue";
import ToastHost from "@/components/ui/ToastHost.vue";
import AppLayout from "@/layouts/AppLayout.vue";
import AuthLayout from "@/layouts/AuthLayout.vue";
import ClientPortalLayout from "@/layouts/ClientPortalLayout.vue";
import { useRouter } from "@/router";

const { route } = useRouter();
const layoutComponent = computed(() => {
  if (route.value?.layout === "auth") return AuthLayout;
  if (route.value?.layout === "client") return ClientPortalLayout;
  return AppLayout;
});
</script>

<template>
  <component :is="layoutComponent">
    <RouterView />
  </component>
  <ToastHost />
  <ConfirmDialog />
</template>
