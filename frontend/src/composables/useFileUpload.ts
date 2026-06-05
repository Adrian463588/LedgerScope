import { ref } from 'vue';

export function useFileUpload(maxMegabytes = 25) {
  const progress = ref(0);
  const error = ref<string | null>(null);

  function validate(file: File): boolean {
    error.value = null;
    const allowed = ['application/pdf', 'image/png', 'image/jpeg', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    if (!allowed.includes(file.type)) {
      error.value = 'Use PDF, PNG, JPG, or XLSX files.';
      return false;
    }

    if (file.size > maxMegabytes * 1024 * 1024) {
      error.value = `File must be ${maxMegabytes} MB or smaller.`;
      return false;
    }

    return true;
  }

  function simulateUpload(): void {
    progress.value = 0;
    const timer = window.setInterval(() => {
      progress.value = Math.min(100, progress.value + 20);
      if (progress.value === 100) {
        window.clearInterval(timer);
      }
    }, 120);
  }

  return { progress, error, validate, simulateUpload };
}
