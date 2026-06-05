import axios, { AxiosError } from 'axios';

import type { ApiError, ApiResponse } from '@/types';

export const api = axios.create({
  baseURL: '/api/v1',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: true,
});

export async function initCsrf(): Promise<void> {
  await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
}

api.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiError>) => {
    if (error.response?.status === 401 && window.location.pathname !== '/login') {
      window.history.pushState({}, '', '/login');
      window.dispatchEvent(new PopStateEvent('popstate'));
    }

    return Promise.reject(error);
  },
);

export function unwrap<T>(response: { data: ApiResponse<T> }): T {
  return response.data.data;
}

export function getApiError(error: unknown): ApiError {
  if (axios.isAxiosError<ApiError>(error) && error.response?.data) {
    return error.response.data;
  }

  return {
    success: false,
    message: error instanceof Error ? error.message : 'Something went wrong. Please try again.',
  };
}
