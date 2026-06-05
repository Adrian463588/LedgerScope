import { useNotification } from './useNotification';

export function useExport() {
  const notification = useNotification();

  function downloadSignedUrl(url: string): void {
    if (!url) {
      notification.error('Download link is not ready yet.');
      return;
    }

    window.location.assign(url);
  }

  return { downloadSignedUrl };
}
