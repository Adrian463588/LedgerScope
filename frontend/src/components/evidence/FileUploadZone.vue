<script setup lang="ts">
import { UploadCloud } from "lucide-vue-next";
import { ref } from "vue";

import { useFileUpload } from "@/composables/useFileUpload";
import { useNotification } from "@/composables/useNotification";

const props = defineProps<{
  upload?: (file: File) => Promise<void>;
}>();

const fileName = ref("");
const { progress, error, isUploading, validate, uploadFile } = useFileUpload();
const notification = useNotification();

async function onFile(event: Event): Promise<void> {
  const input = event.target as HTMLInputElement;
  const file = input.files?.[0];
  if (!file) return;
  if (!validate(file)) return;
  fileName.value = file.name;
  if (!props.upload) return;

  try {
    await uploadFile(file, props.upload);
    notification.success("Evidence uploaded.");
  } catch {
    // The composable exposes the server error in the component state.
  }
}
</script>

<template>
  <section class="upload-zone">
    <UploadCloud aria-hidden="true" />
    <h3>Drop file here or click to browse</h3>
    <p>PDF, XLSX, PNG, JPG. Max 25 MB.</p>
    <label>
      <input type="file" @change="onFile" />
      <span class="choose-button">{{
        isUploading ? "Uploading…" : "Choose File"
      }}</span>
    </label>
    <p v-if="fileName" class="file-name">
      {{ fileName }}<span v-if="props.upload"> · {{ progress }}%</span>
    </p>
    <div v-if="progress > 0" class="bar">
      <i :style="{ width: `${progress}%` }" />
    </div>
    <p v-if="error" class="error">{{ error }}</p>
  </section>
</template>

<style scoped>
.upload-zone {
  display: grid;
  gap: 10px;
  justify-items: center;
  border: 1px dashed var(--border-strong);
  border-radius: 8px;
  background: white;
  padding: 32px;
  text-align: center;
}

svg {
  width: 42px;
  height: 42px;
  color: var(--text-muted);
}

h3,
p {
  margin: 0;
}

p {
  color: var(--text-secondary);
}

input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.choose-button {
  display: inline-flex;
  height: 36px;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border-strong);
  border-radius: 4px;
  background: white;
  color: var(--text-primary);
  cursor: pointer;
  font-weight: 500;
  padding: 0 16px;
}

.choose-button:hover {
  background: var(--surface-hover);
}

.bar {
  width: min(320px, 100%);
  height: 8px;
  overflow: hidden;
  border-radius: 999px;
  background: var(--surface-active);
}

.bar i {
  display: block;
  height: 100%;
  background: var(--status-success);
}

.file-name {
  font-family: "IBM Plex Mono", monospace;
}

.error {
  color: var(--status-danger);
}
</style>
