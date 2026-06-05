import { getApiError } from '@/api/client';

export function useApiError() {
  function friendlyMessage(error: unknown): string {
    return getApiError(error).message;
  }

  function fieldErrors(error: unknown): Record<string, string[]> {
    return getApiError(error).errors ?? {};
  }

  return { friendlyMessage, fieldErrors };
}
