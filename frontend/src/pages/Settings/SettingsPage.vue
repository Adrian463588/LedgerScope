<script setup lang="ts">
import { onMounted, ref } from "vue";

import AppButton from "@/components/ui/AppButton.vue";
import PageHeader from "@/components/ui/PageHeader.vue";
import { useLedgerScopeApi } from "@/composables/useLedgerScopeApi";

const { notificationsApi } = useLedgerScopeApi();
import { useNotification } from "@/composables/useNotification";
import { useUiStore } from "@/stores/ui.store";
import type {
  NotificationChannel,
  NotificationEventType,
  NotificationPreference,
} from "@/types";

interface PreferenceRow {
  channel: NotificationChannel;
  event_type: NotificationEventType;
  enabled: boolean;
}

const ui = useUiStore();
const notification = useNotification();
const rows = ref<PreferenceRow[]>([]);
const isLoading = ref(false);
const isSaving = ref(false);
const error = ref<string | null>(null);

const channels: Array<{ key: NotificationChannel; label: string }> = [
  { key: "app", label: "In-app" },
  { key: "email", label: "Email" },
  { key: "weekly_digest", label: "Weekly digest" },
];

const events: Array<{ key: NotificationEventType; label: string }> = [
  { key: "document_request", label: "Document requests" },
  { key: "review_note", label: "Review notes" },
  { key: "finding", label: "Audit findings" },
  { key: "evidence", label: "Evidence" },
  { key: "report", label: "Reports" },
];

function defaultRows(): PreferenceRow[] {
  return events.flatMap((event) =>
    channels.map((channel) => ({
      channel: channel.key,
      event_type: event.key,
      enabled: true,
    })),
  );
}

function applyPreferences(preferences: NotificationPreference[]): void {
  const defaults = defaultRows();
  rows.value = defaults.map((row) => {
    const saved = preferences.find(
      (item) =>
        item.channel === row.channel && item.event_type === row.event_type,
    );
    return saved
      ? {
          channel: saved.channel,
          event_type: saved.event_type,
          enabled: saved.enabled,
        }
      : row;
  });
}

function getRow(
  channel: NotificationChannel,
  eventType: NotificationEventType,
): PreferenceRow | undefined {
  return rows.value.find(
    (row) => row.channel === channel && row.event_type === eventType,
  );
}

function setRowEnabled(
  channel: NotificationChannel,
  eventType: NotificationEventType,
  event: Event,
): void {
  const row = getRow(channel, eventType);
  const target = event.target;
  if (row && target instanceof HTMLInputElement) {
    row.enabled = target.checked;
  }
}

function isMandatory(
  channel: NotificationChannel,
  eventType: NotificationEventType,
): boolean {
  return channel === "app" && eventType === "finding";
}

async function load(): Promise<void> {
  isLoading.value = true;
  error.value = null;
  try {
    applyPreferences(await notificationsApi.preferences());
  } catch (caught) {
    error.value =
      caught instanceof Error ? caught.message : "Unable to load preferences.";
  } finally {
    isLoading.value = false;
  }
}

async function save(): Promise<void> {
  isSaving.value = true;
  try {
    await notificationsApi.updatePreferences(rows.value);
    notification.success("Notification preferences updated.");
  } catch (caught) {
    notification.error(
      caught instanceof Error
        ? caught.message
        : "Unable to update notification preferences.",
    );
  } finally {
    isSaving.value = false;
  }
}

onMounted(() => {
  ui.setBreadcrumbs(["Settings"]);
  void load();
});
</script>

<template>
  <PageHeader
    title="Settings"
    subtitle="Manage notification delivery preferences for your account."
  >
    <template #actions>
      <AppButton variant="primary" :loading="isSaving" @click="save">
        Save changes
      </AppButton>
    </template>
  </PageHeader>

  <section class="settings-panel" aria-labelledby="notification-preferences">
    <div class="panel-heading">
      <div>
        <h2 id="notification-preferences">Notification preferences</h2>
        <p>Critical in-app finding alerts cannot be disabled.</p>
      </div>
    </div>

    <div v-if="isLoading" class="state">Loading preferences...</div>
    <div v-else-if="error" class="state state--error">
      <p>{{ error }}</p>
      <AppButton @click="load">Retry</AppButton>
    </div>
    <div
      v-else
      class="preference-table"
      role="table"
      aria-label="Notification preferences"
    >
      <div class="preference-row preference-row--header" role="row">
        <span role="columnheader">Event</span>
        <span
          v-for="channel in channels"
          :key="channel.key"
          role="columnheader"
        >
          {{ channel.label }}
        </span>
      </div>
      <div
        v-for="event in events"
        :key="event.key"
        class="preference-row"
        role="row"
      >
        <strong role="rowheader">{{ event.label }}</strong>
        <label
          v-for="channel in channels"
          :key="channel.key"
          class="preference-toggle"
        >
          <input
            :checked="getRow(channel.key, event.key)?.enabled ?? false"
            type="checkbox"
            :disabled="isMandatory(channel.key, event.key)"
            @change="setRowEnabled(channel.key, event.key, $event)"
          />
          <span class="sr-only">
            {{ channel.label }} for {{ event.label }}
          </span>
        </label>
      </div>
    </div>
  </section>
</template>

<style scoped>
.settings-panel {
  margin-top: 24px;
  overflow: hidden;
  border: 1px solid var(--border);
  border-radius: 8px;
  background: var(--surface);
}

.panel-heading {
  border-bottom: 1px solid var(--border);
  padding: 20px;
}

h2 {
  margin: 0;
  color: var(--text-primary);
  font-size: 1rem;
}

.panel-heading p {
  margin: 6px 0 0;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.preference-table {
  overflow-x: auto;
}

.preference-row {
  display: grid;
  grid-template-columns: minmax(180px, 1fr) repeat(3, minmax(110px, 0.35fr));
  align-items: center;
  min-width: 620px;
  border-bottom: 1px solid var(--border);
  padding: 16px 20px;
  color: var(--text-primary);
}

.preference-row:last-child {
  border-bottom: 0;
}

.preference-row--header {
  background: var(--surface-alt);
  color: var(--text-secondary);
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.preference-toggle {
  display: grid;
  place-items: center;
}

.preference-toggle input {
  width: 18px;
  height: 18px;
  accent-color: var(--brand-red);
}

.state {
  display: grid;
  min-height: 180px;
  place-items: center;
  align-content: center;
  gap: 12px;
  color: var(--text-secondary);
  text-align: center;
}

.state--error {
  color: var(--status-danger);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
}
</style>
