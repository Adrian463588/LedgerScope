export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from?: number | null;
  to?: number | null;
}

export interface ApiResponse<T = unknown> {
  success: boolean;
  message: string;
  data: T;
  meta?: PaginationMeta;
}

export type ApiPaginatedResponse<T> = ApiResponse<T[]> & {
  meta: PaginationMeta;
};

export interface ApiError {
  success: false;
  message: string;
  code?: string;
  errors?: Record<string, string[]>;
}

export interface ApiListParams {
  page?: number;
  per_page?: number;
  search?: string;
  status?: string;
  period?: string;
  sort?: string;
  direction?: "asc" | "desc";
}
