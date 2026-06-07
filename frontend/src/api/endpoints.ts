import { api, unwrap } from './client';
export { initCsrf } from './client';
import type { Account, ApiListParams, ApiResponse, AuthUser, Company, Engagement, JournalEntry, ReportItem, TrialBalanceRow } from '@/types';

export interface DashboardData {
  kpis: Array<{
    label: string;
    value: string;
    change: string;
    changeType: 'up' | 'down';
    isPrimary: boolean;
  }>;
  quarterlySnapshot: Array<{
    label: string;
    value: string;
    change: string;
    changeType: 'up' | 'down';
  }>;
  recentActivities: Array<{
    id: number;
    action: string;
    status: string;
    time: string;
    user: string;
  }>;
  quickAccess: Array<{
    label: string;
    hasData: boolean;
  }>;
  companies: Array<{
    id: number;
    name: string;
    legal_name: string;
  }>;
  openFindings: number;
  outstandingRequests: number;
}

export const authApi = {
  async login(payload: { email: string; password: string; remember: boolean }): Promise<AuthUser | { mfa_required: true; email: string }> {
    return unwrap(await api.post<ApiResponse<AuthUser | { mfa_required: true; email: string }>>('/auth/login', payload));
  },
  async me(): Promise<AuthUser> {
    return unwrap(await api.get<ApiResponse<AuthUser>>('/auth/me'));
  },
  async logout(): Promise<void> {
    await api.post('/auth/logout');
  },
  async verifyMfa(payload: { code: string }): Promise<AuthUser> {
    return unwrap(await api.post<ApiResponse<AuthUser>>('/auth/mfa/verify', payload));
  },
};

export const dashboardApi = {
  async getDashboardData(): Promise<DashboardData> {
    return unwrap(await api.get<ApiResponse<DashboardData>>('/dashboard'));
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
  async fiscalYears(companyId: number): Promise<any[]> {
    return unwrap(await api.get<ApiResponse<any[]>>(`/companies/${companyId}/fiscal-years`));
  },
  async quarters(companyId: number, fiscalYearId: number): Promise<any[]> {
    return unwrap(await api.get<ApiResponse<any[]>>(`/companies/${companyId}/fiscal-years/${fiscalYearId}/quarters`));
  },
  async periods(companyId: number, fiscalYearId: number): Promise<any[]> {
    return unwrap(await api.get<ApiResponse<any[]>>(`/companies/${companyId}/fiscal-years/${fiscalYearId}/periods`));
  },
  async lockQuarter(companyId: number, quarterId: number): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/companies/${companyId}/quarters/${quarterId}/lock`));
  },
  async unlockQuarter(companyId: number, quarterId: number, reason: string): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/companies/${companyId}/quarters/${quarterId}/unlock`, { reason }));
  },
  async getQuarterChecklist(companyId: number, quarterId: number): Promise<any[]> {
    return unwrap(await api.get<ApiResponse<any[]>>(`/companies/${companyId}/quarters/${quarterId}/checklist`));
  },
  async updateQuarterChecklist(companyId: number, quarterId: number, key: string, payload: { is_completed: boolean; notes?: string }): Promise<any> {
    return unwrap(await api.patch<ApiResponse<any>>(`/companies/${companyId}/quarters/${quarterId}/checklist/${key}`, payload));
  },
  async importAccounts(companyId: number, file: File): Promise<any> {
    const formData = new FormData();
    formData.append('file', file);
    return unwrap(await api.post<ApiResponse<any>>(`/companies/${companyId}/accounts/import`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }));
  },
  async getAccountImportStatus(companyId: number, batchId: number): Promise<any> {
    return unwrap(await api.get<ApiResponse<any>>(`/companies/${companyId}/accounts/import/${batchId}`));
  },
  async importJournals(companyId: number, file: File): Promise<any> {
    const formData = new FormData();
    formData.append('file', file);
    return unwrap(await api.post<ApiResponse<any>>(`/companies/${companyId}/journals/import`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }));
  },
  async getRatios(companyId: number, params?: { accounting_period_id?: number }): Promise<any> {
    return unwrap(await api.get<ApiResponse<any>>(`/companies/${companyId}/financial-analysis/ratios`, { params }));
  },
  async getTrends(companyId: number): Promise<any> {
    return unwrap(await api.get<ApiResponse<any>>(`/companies/${companyId}/financial-analysis/trends`));
  },
  async getFinancialStatements(companyId: number): Promise<any[]> {
    return unwrap(await api.get<ApiResponse<any[]>>(`/companies/${companyId}/financial-statements`));
  },
  async generateFinancialStatement(companyId: number, payload: { accounting_period_id: number; statement_type: string }): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/companies/${companyId}/financial-statements/generate`, payload));
  },
  async getFinancialStatement(companyId: number, versionId: number, params?: { compare_with?: number }): Promise<any> {
    return unwrap(await api.get<ApiResponse<any>>(`/companies/${companyId}/financial-statements/${versionId}`, { params }));
  },
  async approveFinancialStatement(companyId: number, versionId: number): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/companies/${companyId}/financial-statements/${versionId}/approve`));
  },
  async lockFinancialStatement(companyId: number, versionId: number): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/companies/${companyId}/financial-statements/${versionId}/lock`));
  },
  getFinancialStatementExportUrl(companyId: number, versionId: number, format: 'pdf' | 'xlsx'): string {
    return `/api/v1/companies/${companyId}/financial-statements/${versionId}/export?format=${format}`;
  },
};

export const engagementApi = {
  async list(companyId: number): Promise<Engagement[]> {
    return unwrap(await api.get<ApiResponse<Engagement[]>>(`/companies/${companyId}/engagements`));
  },
  async get(engagementId: number): Promise<Engagement> {
    return unwrap(await api.get<ApiResponse<Engagement>>(`/engagements/${engagementId}`));
  },
  async create(companyId: number, payload: {
    name: string;
    engagement_type: string;
    start_date: string;
    end_date: string;
    scope?: string;
    objectives?: string;
  }): Promise<Engagement> {
    return unwrap(await api.post<ApiResponse<Engagement>>(`/companies/${companyId}/engagements`, payload));
  },
  // Evidence
  async listEvidence(engagementId: number) {
    return unwrap(await api.get<ApiResponse<unknown[]>>(`/engagements/${engagementId}/evidence`));
  },
  async uploadEvidence(engagementId: number, formData: FormData) {
    return unwrap(await api.post<ApiResponse<unknown>>(`/engagements/${engagementId}/evidence`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }));
  },
  async getEvidenceDownloadUrl(engagementId: number, evidenceId: number) {
    return unwrap(await api.get<ApiResponse<{ url: string; expires_at: string }>>(`/engagements/${engagementId}/evidence/${evidenceId}/download`));
  },
  // Findings
  async listFindings(engagementId: number) {
    return unwrap(await api.get<ApiResponse<unknown[]>>(`/engagements/${engagementId}/findings`));
  },
  async createFinding(engagementId: number, payload: Record<string, unknown>) {
    return unwrap(await api.post<ApiResponse<unknown>>(`/engagements/${engagementId}/findings`, payload));
  },
  // Document Requests (PBC)
  async listDocumentRequests(engagementId: number) {
    return unwrap(await api.get<ApiResponse<unknown[]>>(`/engagements/${engagementId}/document-requests`));
  },
  async createDocumentRequest(engagementId: number, payload: Record<string, unknown>) {
    return unwrap(await api.post<ApiResponse<unknown>>(`/engagements/${engagementId}/document-requests`, payload));
  },
  // Working Papers
  async listWorkingPapers(engagementId: number) {
    return unwrap(await api.get<ApiResponse<any[]>>(`/engagements/${engagementId}/working-papers`));
  },
  async getWorkingPaper(engagementId: number, workingPaperId: number) {
    return unwrap(await api.get<ApiResponse<any>>(`/engagements/${engagementId}/working-papers/${workingPaperId}`));
  },
  async updateWorkingPaper(engagementId: number, workingPaperId: number, payload: Record<string, any>) {
    return unwrap(await api.put<ApiResponse<any>>(`/engagements/${engagementId}/working-papers/${workingPaperId}`, payload));
  },
  async signOffWorkingPaper(engagementId: number, workingPaperId: number) {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/working-papers/${workingPaperId}/sign-off`));
  },
  async lockWorkingPaper(engagementId: number, workingPaperId: number) {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/working-papers/${workingPaperId}/lock`));
  },
  async unlockWorkingPaper(engagementId: number, workingPaperId: number) {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/working-papers/${workingPaperId}/unlock`));
  },
  // Review Notes (EPIC 4)
  async listReviewNotes(engagementId: number) {
    return unwrap(await api.get<ApiResponse<any[]>>(`/engagements/${engagementId}/review-notes`));
  },
  async createReviewNote(engagementId: number, payload: { working_paper_id: number; content: string }) {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/review-notes`, payload));
  },
  async resolveReviewNote(engagementId: number, reviewNoteId: number) {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/review-notes/${reviewNoteId}/resolve`));
  },
  async replyReviewNote(engagementId: number, reviewNoteId: number, message: string) {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/review-notes/${reviewNoteId}/reply`, { message }));
  },
  async deleteReviewNote(engagementId: number, reviewNoteId: number) {
    await api.delete(`/engagements/${engagementId}/review-notes/${reviewNoteId}`);
  },
  // Client Portal requests (EPIC 4)
  async listClientDocumentRequests() {
    return unwrap(await api.get<ApiResponse<any[]>>('/client/document-requests'));
  },
  async getClientDocumentRequest(id: number) {
    return unwrap(await api.get<ApiResponse<any>>(`/client/document-requests/${id}`));
  },
  async uploadClientDocumentRequest(id: number, file: File, description?: string) {
    const formData = new FormData();
    formData.append('file', file);
    if (description) {
      formData.append('description', description);
    }
    return unwrap(await api.post<ApiResponse<any>>(`/client/document-requests/${id}/upload`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }));
  },
  // Findings details, update, resolve, reopen, management response (EPIC 4)
  async getFinding(engagementId: number, findingId: number) {
    return unwrap(await api.get<ApiResponse<any>>(`/engagements/${engagementId}/findings/${findingId}`));
  },
  async updateFinding(engagementId: number, findingId: number, payload: Record<string, any>) {
    return unwrap(await api.put<ApiResponse<any>>(`/engagements/${engagementId}/findings/${findingId}`, payload));
  },
  async resolveFinding(engagementId: number, findingId: number) {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/findings/${findingId}/resolve`));
  },
  async reopenFinding(engagementId: number, findingId: number, reason: string) {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/findings/${findingId}/reopen`, { reason }));
  },
  async managementResponseFinding(engagementId: number, findingId: number, payload: { response: string }) {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/findings/${findingId}/management-response`, payload));
  },
  // Audit Plan (EPIC 4)
  async getAuditPlan(engagementId: number) {
    return unwrap(await api.get<ApiResponse<any>>(`/engagements/${engagementId}/audit-plan`));
  },
  async updateAuditPlan(engagementId: number, payload: Record<string, any>) {
    return unwrap(await api.put<ApiResponse<any>>(`/engagements/${engagementId}/audit-plan`, payload));
  },
  // Risk Assessments
  async listRiskAssessments(engagementId: number) {
    return unwrap(await api.get<ApiResponse<any[]>>(`/engagements/${engagementId}/risk-assessments`));
  },
  // Audit Programs
  async listAuditPrograms(engagementId: number) {
    return unwrap(await api.get<ApiResponse<any[]>>(`/engagements/${engagementId}/audit-programs`));
  },
};

// EPIC 13 fix: aligned field names with backend (report_type, title, format, parameters)
export const reportingApi = {
  async list(companyId: number): Promise<ReportItem[]> {
    // Backend returns paginated — extract data array
    const response = await api.get<{ success: boolean; data: ReportItem[] }>(`/companies/${companyId}/reports`);
    return response.data.data ?? [];
  },
  async generate(companyId: number, payload: {
    report_type: string;  // fixed: was 'type'
    title: string;        // added: required by backend
    format?: 'pdf' | 'xlsx';
    parameters?: Record<string, unknown>;
  }): Promise<ReportItem> {
    return unwrap(await api.post<ApiResponse<ReportItem>>(`/companies/${companyId}/reports/generate`, payload));
  },
  async get(companyId: number, reportId: number): Promise<ReportItem> {
    return unwrap(await api.get<ApiResponse<ReportItem>>(`/companies/${companyId}/reports/${reportId}`));
  },
  async getDownloadUrl(companyId: number, reportId: number): Promise<{ url: string; expires_at: string }> {
    return unwrap(await api.get<ApiResponse<{ url: string; expires_at: string }>>(`/companies/${companyId}/reports/${reportId}/download`));
  },
};

export const notificationsApi = {
  async list(page = 1) {
    return unwrap(await api.get<ApiResponse<any>>(`/notifications?page=${page}`));
  },
  async markRead(id: string) {
    return unwrap(await api.post<ApiResponse<null>>(`/notifications/${id}/read`));
  },
};

export const adminApi = {
  // Users
  async listUsers(params?: Record<string, any>): Promise<any[]> {
    return unwrap(await api.get<ApiResponse<any[]>>('/admin/users', { params }));
  },
  async getUser(userId: number): Promise<any> {
    return unwrap(await api.get<ApiResponse<any>>(`/admin/users/${userId}`));
  },
  async updateUser(userId: number, payload: Record<string, any>): Promise<any> {
    return unwrap(await api.put<ApiResponse<any>>(`/admin/users/${userId}`, payload));
  },
  async deleteUser(userId: number): Promise<void> {
    await api.delete(`/admin/users/${userId}`);
  },
  async suspendUser(userId: number): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/admin/users/${userId}/suspend`));
  },
  async activateUser(userId: number): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/admin/users/${userId}/activate`));
  },
  // Roles
  async listRoles(): Promise<any[]> {
    return unwrap(await api.get<ApiResponse<any[]>>('/admin/roles'));
  },
  async assignRole(userId: number, roleId: number): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/admin/users/${userId}/roles`, { role_id: roleId }));
  },
  async revokeRole(userId: number, roleId: number): Promise<any> {
    return unwrap(await api.delete<ApiResponse<any>>(`/admin/users/${userId}/roles/${roleId}`));
  },
  // Audit Trail
  async listAuditTrail(params?: Record<string, any>): Promise<any> {
    return unwrap(await api.get<ApiResponse<any>>('/admin/audit-trail', { params }));
  },
};

export const auditControlsApi = {
  // Internal Controls
  async listControls(engagementId: number): Promise<any[]> {
    return unwrap(await api.get<ApiResponse<any[]>>(`/engagements/${engagementId}/internal-controls`));
  },
  async getControl(engagementId: number, controlId: number): Promise<any> {
    return unwrap(await api.get<ApiResponse<any>>(`/engagements/${engagementId}/internal-controls/${controlId}`));
  },
  async createControl(engagementId: number, payload: Record<string, any>): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/internal-controls`, payload));
  },
  async updateControl(engagementId: number, controlId: number, payload: Record<string, any>): Promise<any> {
    return unwrap(await api.put<ApiResponse<any>>(`/engagements/${engagementId}/internal-controls/${controlId}`, payload));
  },
  async deleteControl(engagementId: number, controlId: number): Promise<void> {
    await api.delete(`/engagements/${engagementId}/internal-controls/${controlId}`);
  },
  // Control Risks
  async listRisks(engagementId: number, controlId: number): Promise<any[]> {
    return unwrap(await api.get<ApiResponse<any[]>>(`/engagements/${engagementId}/internal-controls/${controlId}/risks`));
  },
  async addRisk(engagementId: number, controlId: number, payload: Record<string, any>): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/engagements/${engagementId}/internal-controls/${controlId}/risks`, payload));
  },
  async updateRisk(engagementId: number, controlId: number, riskId: number, payload: Record<string, any>): Promise<any> {
    return unwrap(await api.put<ApiResponse<any>>(`/engagements/${engagementId}/internal-controls/${controlId}/risks/${riskId}`, payload));
  },
  async deleteRisk(engagementId: number, controlId: number, riskId: number): Promise<void> {
    await api.delete(`/engagements/${engagementId}/internal-controls/${controlId}/risks/${riskId}`);
  },
};

export const redFlagApi = {
  async scanJournals(companyId: number): Promise<any> {
    return unwrap(await api.post<ApiResponse<any>>(`/companies/${companyId}/journals/red-flag-scan`));
  },
};


