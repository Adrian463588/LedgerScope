import { ref } from "vue";

export function useFileUpload(maxMegabytes = 25) {
  const progress = ref(0);
  const error = ref<string | null>(null);
  const isUploading = ref(false);

  function validate(file: File): boolean {
    error.value = null;
    const allowed = [
      "application/pdf",
      "image/png",
      "image/jpeg",
      "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    ];
    if (!allowed.includes(file.type)) {
      error.value = "Use PDF, PNG, JPG, or XLSX files.";
      return false;
    }

    if (file.size > maxMegabytes * 1024 * 1024) {
      error.value = `File must be ${maxMegabytes} MB or smaller.`;
      return false;
    }

    return true;
  }

  async function uploadFile(
    file: File,
    uploader: (file: File) => Promise<void>,
  ): Promise<void> {
    if (!validate(file)) return;

    progress.value = 0;
    isUploading.value = true;
    try {
      await uploader(file);
      progress.value = 100;
    } catch (caught) {
      error.value =
        caught instanceof Error ? caught.message : "File upload failed.";
      throw caught;
    } finally {
      isUploading.value = false;
    }
  }

  return { progress, error, isUploading, validate, uploadFile };
}
