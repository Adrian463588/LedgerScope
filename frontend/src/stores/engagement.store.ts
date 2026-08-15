import { defineStore } from "pinia";
import { ref } from "vue";

import { engagementApi } from "@/api/endpoints";
import type { Engagement } from "@/types";

export const useEngagementStore = defineStore("engagement", () => {
  const engagements = ref<Engagement[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  async function fetchEngagements(companyId: number): Promise<void> {
    isLoading.value = true;
    error.value = null;
    try {
      engagements.value = await engagementApi.list(companyId);
    } catch (caught) {
      error.value =
        caught instanceof Error
          ? caught.message
          : "API is currently unreachable.";
    } finally {
      isLoading.value = false;
    }
  }

  function reset(): void {
    engagements.value = [];
  }

  return { engagements, isLoading, error, fetchEngagements, reset };
});
