import { api, unwrap } from './client';
import type { Account, ApiListParams, ApiResponse, AuthUser, Company, Engagement, JournalEntry, ReportItem, TrialBalanceRow } from '@/types';

export const authApi = {
  async login(payload: { email: string; password: string; remember: boolean }): Promise<AuthUser> {
    return unwrap(await api.post<ApiResponse<AuthUser>>('/auth/login', payload));
  },
  async me(): Promise<AuthUser> {
    return unwrap(await api.get<ApiResponse<AuthUser>>('/auth/me'));
  },
  async logout(): Promise<void> {
    await api.post('/auth/logout');
  },
  async verifyMfa(payload: { code: string }): Promise<void> {
    await api.post('/auth/mfa/verify', payload);
  },
};

export const companyApi = {
  async list(params: ApiListParams = {}): Promise<Company[]> {
    return unwrap(await api.get<ApiResponse<Company[]>>('/companies', { params }));
  },
  async create(payload: Partial<Company>): Promise<Company> {
    return unwrap(await api.post<ApiResponse<Company>>('/companies', payload));
  },
  async update(id: number, payload: Partial<Company>): Promise<Company> {
    return unwrap(await api.put<ApiResponse<Company>>(`/companies/${id}`, payload));
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/companies/${id}`);
  },
};

export const accountingApi = {
  async accounts(companyId: number): Promise<Account[]> {
    return unwrap(await api.get<ApiResponse<Account[]>>(`/companies/${companyId}/accounts`));
  },
  async journals(companyId: number, params: ApiListParams = {}): Promise<JournalEntry[]> {
    return unwrap(await api.get<ApiResponse<JournalEntry[]>>(`/companies/${companyId}/journals`, { params }));
  },
  async createJournal(companyId: number, payload: Partial<JournalEntry>): Promise<JournalEntry> {
    return unwrap(await api.post<ApiResponse<JournalEntry>>(`/companies/${companyId}/journals`, payload));
  },
  async postJournal(companyId: number, journalId: number): Promise<JournalEntry> {
    return unwrap(await api.post<ApiResponse<JournalEntry>>(`/companies/${companyId}/journals/${journalId}/post`));
  },
  async trialBalance(companyId: number): Promise<TrialBalanceRow[]> {
    return unwrap(await api.get<ApiResponse<TrialBalanceRow[]>>(`/companies/${companyId}/trial-balance`));
  },
  async generateTrialBalance(companyId: number): Promise<void> {
    await api.post(`/companies/${companyId}/trial-balance/generate`);
  },
};

export const engagementApi = {
  async list(companyId: number): Promise<Engagement[]> {
    return unwrap(await api.get<ApiResponse<Engagement[]>>(`/companies/${companyId}/engagements`));
  },
};

export const reportingApi = {
  async list(companyId: number): Promise<ReportItem[]> {
    return unwrap(await api.get<ApiResponse<ReportItem[]>>(`/companies/${companyId}/reports`));
  },
  async generate(companyId: number, payload: { type: string; period: string }): Promise<void> {
    await api.post(`/companies/${companyId}/reports/generate`, payload);
  },
};
