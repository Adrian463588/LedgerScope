import type {
  AccountingPeriod,
  Account,
  AdminRole,
  AdminUser,
  AuthUser,
  AuditLog,
  AuditPlan,
  AuditProgram,
  AuditProgramStep,
  ClientDocumentRequest,
  ChecklistItem,
  Company,
  CompanyContact,
  ControlRisk,
  DbNotification,
  DocumentRequest,
  Engagement,
  ExternalIntegrationStatus,
  EvidenceFile,
  FinancialAnalysisRatios,
  FinancialStatement,
  FinancialStatementComparison,
  FinancialTrends,
  Finding,
  FiscalYear,
  ImportBatch,
  InternalControl,
  JournalEntry,
  NotificationPreference,
  RedFlag,
  ReportItem,
  ReportFormat,
  ReportType,
  ReviewNote,
  RiskAssessment,
  Quarter,
  Reconciliation,
  StatementType,
  TrialBalanceRow,
  TrialBalanceSnapshot,
  WorkingPaper,
} from "@/types";
import type {
  ApiListParams,
  ApiPaginatedResponse,
  ApiResponse,
  PaginationMeta,
} from "@/types";

import { api, unwrap } from "./client";

export { initCsrf } from "./client";

export interface DashboardData {
  kpis: Array<{
    label: string;
    value: string;
    change: string | null;
    changeType: "up" | "down";
    isPrimary: boolean;
  }>;
  quarterlySnapshot: Array<{
    label: string;
    value: string;
    change: string | null;
    changeType: "up" | "down";
  }>;
  recentActivities: Array<{
    id: number;
    action: string;
    status: string;
    time: string;
    user: string;
  }>;
  quickAccess: Array<{ label: string; hasData: boolean }>;
  trend?: number[];
  progress?: Array<{ label: string; value: number }>;
  companies: Array<{ id: number; name: string; legal_name: string }>;
  openFindings: number;
  outstandingRequests: number;
}

export interface DashboardParams {
  companyId?: number;
  periodId?: number;
}

export interface LoginResponse {
  mfa_required: true;
  email: string;
}

export interface LoginPayload {
  email: string;
  password: string;
  remember: boolean;
}

export interface PagedResult<T> {
  items: T[];
  meta: PaginationMeta;
}

export interface CompanyCreatePayload {
  name: string;
  legal_name?: string;
  registration_number?: string;
  tax_id?: string;
  industry?: string;
  currency?: string;
  fiscal_year_start_month?: number;
  address?: string;
  city?: string;
  country?: string;
  phone?: string;
  email?: string;
  website?: string;
}

export interface CompanyUpdatePayload {
  name?: string;
  legal_name?: string;
  registration_number?: string;
  tax_id?: string;
  industry?: string;
  currency?: string;
  fiscal_year_start_month?: number;
  address?: string;
  city?: string;
  country?: string;
  phone?: string;
  email?: string;
  website?: string;
  is_active?: boolean;
}

export interface CreateJournalPayload {
  accounting_period_id: number;
  description: string;
  journal_date: string;
  reference?: string;
  lines: Array<{
    account_id: number;
    debit: string;
    credit: string;
    description?: string;
  }>;
}

export interface CreateAccountPayload {
  account_code: string;
  account_name: string;
  account_type:
    | "asset"
    | "liability"
    | "equity"
    | "revenue"
    | "cost_of_goods_sold"
    | "expense"
    | "other_income"
    | "other_expense";
  parent_id?: number;
  description?: string;
}

export interface UpdateAuditPlanPayload {
  overall_materiality: string;
  performance_materiality: string;
  trivial_threshold: string;
  audit_strategy: string;
  planning_checklist: Array<{
    key: string;
    name: string;
    is_completed: boolean;
  }>;
}

export interface CreateFindingPayload {
  title: string;
  description: string;
  severity: string;
  category: string;
  recommendation?: string;
}

export interface UpdateFindingPayload {
  title?: string;
  description?: string;
  severity?: string;
  category?: string;
  recommendation?: string | null;
}

export interface CreateDocumentRequestPayload {
  title: string;
  description?: string;
  due_date?: string;
}

export interface UpdateWorkingPaperPayload {
  title?: string;
  content?: string;
}

export interface UpdateRiskAssessmentPayload {
  risk_area?: string;
  risk_level?: "low" | "medium" | "high" | "critical";
  description?: string | null;
  mitigation?: string | null;
  inherent_risk?: string | null;
  control_risk?: string | null;
  residual_risk?: string | null;
  fraud_risk?: string | null;
  risk_category?: string | null;
  is_significant?: boolean;
}

export interface UpdateAdminUserPayload {
  name?: string;
  email?: string;
  phone?: string | null;
  status?: string;
}

export interface InviteUserPayload {
  email: string;
  name?: string;
  role_id: number;
}

export interface InviteUserResult {
  user_id: number;
  email: string;
  expires_at: string;
}

export interface CreateInternalControlPayload {
  name: string;
  control_type?: string;
  owner?: string;
  description?: string;
  effectiveness?: string;
}

export interface UpdateInternalControlPayload {
  name?: string;
  control_type?: string;
  owner?: string | null;
  description?: string | null;
  effectiveness?: string;
}

export interface ControlRiskPayload {
  risk_name: string;
  likelihood: string;
  impact: string;
  residual_risk?: string;
}

export interface UpdateControlRiskPayload {
  risk_name?: string;
  likelihood?: string;
  impact?: string;
  residual_risk?: string;
}

export interface ReviewNoteReply {
  id: number;
  message: string;
  user?: { id: number; name: string; email: string } | null;
}

export interface RedFlagScanResult {
  total_journals_scanned: number;
  total_flags: number;
  flags: RedFlag[];
}

function unwrapPage<T>(response: {
  data: ApiPaginatedResponse<T>;
}): PagedResult<T> {
  return {
    items: response.data.data,
    meta: response.data.meta,
  };
}

function asFormData(file: File): FormData {
  const formData = new FormData();
  formData.append("file", file);

  return formData;
}

function mapReport(report: ReportItem): ReportItem {
  return {
    ...report,
    name: report.title ?? report.name ?? "Report",
    type: report.report_type ?? report.type ?? "unknown",
    version: report.version ?? String(report.id),
    generated_at: report.generated_at ?? null,
  };
}

export const authApi = {
  async login(payload: LoginPayload): Promise<AuthUser | LoginResponse> {
    return unwrap(
      await api.post<ApiResponse<AuthUser | LoginResponse>>(
        "/auth/login",
        payload,
      ),
    );
  },
  async me(): Promise<AuthUser> {
    return unwrap(await api.get<ApiResponse<AuthUser>>("/auth/me"));
  },
  async logout(): Promise<void> {
    await api.post("/auth/logout");
  },
  async verifyMfa(payload: { code: string }): Promise<AuthUser> {
    return unwrap(
      await api.post<ApiResponse<AuthUser>>("/auth/mfa/verify", payload),
    );
  },
  async forgotPassword(email: string): Promise<void> {
    await api.post<ApiResponse<null>>("/auth/forgot-password", { email });
  },
  async resetPassword(payload: {
    token: string;
    email: string;
    password: string;
    password_confirmation: string;
  }): Promise<void> {
    await api.post<ApiResponse<null>>("/auth/reset-password", payload);
  },
  async verifyEmail(token: string): Promise<void> {
    await api.post<ApiResponse<null>>(`/auth/verify-email/${token}`);
  },
  async resendVerification(email: string): Promise<void> {
    await api.post<ApiResponse<null>>("/auth/verify-email/resend", { email });
  },
  async acceptInvitation(
    token: string,
    payload: { name: string; password: string; password_confirmation: string },
  ): Promise<void> {
    await api.post<ApiResponse<null>>(`/invitations/${token}/accept`, payload);
  },
};

export const dashboardApi = {
  async getDashboardData(params: DashboardParams = {}): Promise<DashboardData> {
    return unwrap(
      await api.get<ApiResponse<DashboardData>>("/dashboard", {
        params: {
          company_id: params.companyId,
          period_id: params.periodId,
        },
      }),
    );
  },
};

export const companyApi = {
  async list(params: ApiListParams = {}): Promise<PagedResult<Company>> {
    return unwrapPage(
      await api.get<ApiPaginatedResponse<Company>>("/companies", { params }),
    );
  },
  async create(payload: CompanyCreatePayload): Promise<Company> {
    return unwrap(await api.post<ApiResponse<Company>>("/companies", payload));
  },
  async get(id: number): Promise<Company> {
    return unwrap(await api.get<ApiResponse<Company>>(`/companies/${id}`));
  },
  async update(id: number, payload: CompanyUpdatePayload): Promise<Company> {
    return unwrap(
      await api.put<ApiResponse<Company>>(`/companies/${id}`, payload),
    );
  },
  async remove(id: number): Promise<void> {
    await api.delete(`/companies/${id}`);
  },
  async contacts(id: number): Promise<CompanyContact[]> {
    return unwrap(
      await api.get<ApiResponse<CompanyContact[]>>(`/companies/${id}/contacts`),
    );
  },
  async addContact(
    id: number,
    payload: {
      name: string;
      email?: string;
      phone?: string;
      role?: string;
      is_primary?: boolean;
    },
  ): Promise<CompanyContact> {
    return unwrap(
      await api.post<ApiResponse<CompanyContact>>(
        `/companies/${id}/contacts`,
        payload,
      ),
    );
  },
};

export const accountingApi = {
  async accounts(companyId: number): Promise<Account[]> {
    return unwrap(
      await api.get<ApiResponse<Account[]>>(`/companies/${companyId}/accounts`),
    );
  },
  async createAccount(
    companyId: number,
    payload: CreateAccountPayload,
  ): Promise<Account> {
    return unwrap(
      await api.post<ApiResponse<Account>>(
        `/companies/${companyId}/accounts`,
        payload,
      ),
    );
  },
  async journals(
    companyId: number,
    params: ApiListParams = {},
  ): Promise<JournalEntry[]> {
    return unwrap(
      await api.get<ApiResponse<JournalEntry[]>>(
        `/companies/${companyId}/journals`,
        { params },
      ),
    );
  },
  async createJournal(
    companyId: number,
    payload: CreateJournalPayload,
  ): Promise<JournalEntry> {
    return unwrap(
      await api.post<ApiResponse<JournalEntry>>(
        `/companies/${companyId}/journals`,
        payload,
      ),
    );
  },
  async postJournal(
    companyId: number,
    journalId: number,
  ): Promise<JournalEntry> {
    return unwrap(
      await api.post<ApiResponse<JournalEntry>>(
        `/companies/${companyId}/journals/${journalId}/post`,
      ),
    );
  },
  async trialBalance(companyId: number): Promise<TrialBalanceRow[]> {
    return unwrap(
      await api.get<ApiResponse<TrialBalanceRow[]>>(
        `/companies/${companyId}/trial-balance`,
      ),
    );
  },
  async generateTrialBalance(
    companyId: number,
    accountingPeriodId: number,
  ): Promise<TrialBalanceSnapshot> {
    return unwrap(
      await api.post<ApiResponse<TrialBalanceSnapshot>>(
        `/companies/${companyId}/trial-balance/generate`,
        { accounting_period_id: accountingPeriodId },
      ),
    );
  },
  async reconciliations(companyId: number): Promise<Reconciliation[]> {
    return unwrap(
      await api.get<ApiResponse<Reconciliation[]>>(
        `/companies/${companyId}/reconciliations`,
      ),
    );
  },
  async createReconciliation(
    companyId: number,
    payload: {
      account_id: number;
      accounting_period_id: number;
      reconciliation_type: "bank" | "ar" | "ap";
      book_balance: string;
      bank_balance: string;
    },
  ): Promise<Reconciliation> {
    return unwrap(
      await api.post<ApiResponse<Reconciliation>>(
        `/companies/${companyId}/reconciliations`,
        payload,
      ),
    );
  },
  async approveReconciliation(
    companyId: number,
    reconciliationId: number,
  ): Promise<Reconciliation> {
    return unwrap(
      await api.post<ApiResponse<Reconciliation>>(
        `/companies/${companyId}/reconciliations/${reconciliationId}/approve`,
      ),
    );
  },
  async lockReconciliation(
    companyId: number,
    reconciliationId: number,
  ): Promise<Reconciliation> {
    return unwrap(
      await api.post<ApiResponse<Reconciliation>>(
        `/companies/${companyId}/reconciliations/${reconciliationId}/lock`,
      ),
    );
  },
  async fiscalYears(companyId: number): Promise<FiscalYear[]> {
    return unwrap(
      await api.get<ApiResponse<FiscalYear[]>>(
        `/companies/${companyId}/fiscal-years`,
      ),
    );
  },
  async createFiscalYear(companyId: number, year: number): Promise<FiscalYear> {
    return unwrap(
      await api.post<ApiResponse<FiscalYear>>(
        `/companies/${companyId}/fiscal-years`,
        { year },
      ),
    );
  },
  async quarters(companyId: number, fiscalYearId: number): Promise<Quarter[]> {
    return unwrap(
      await api.get<ApiResponse<Quarter[]>>(
        `/companies/${companyId}/fiscal-years/${fiscalYearId}/quarters`,
      ),
    );
  },
  async periods(
    companyId: number,
    fiscalYearId: number,
  ): Promise<AccountingPeriod[]> {
    return unwrap(
      await api.get<ApiResponse<AccountingPeriod[]>>(
        `/companies/${companyId}/fiscal-years/${fiscalYearId}/periods`,
      ),
    );
  },
  async lockQuarter(companyId: number, quarterId: number): Promise<Quarter> {
    return unwrap(
      await api.post<ApiResponse<Quarter>>(
        `/companies/${companyId}/quarters/${quarterId}/lock`,
      ),
    );
  },
  async unlockQuarter(
    companyId: number,
    quarterId: number,
    reason: string,
  ): Promise<Quarter> {
    return unwrap(
      await api.post<ApiResponse<Quarter>>(
        `/companies/${companyId}/quarters/${quarterId}/unlock`,
        { reason },
      ),
    );
  },
  async getQuarterChecklist(
    companyId: number,
    quarterId: number,
  ): Promise<ChecklistItem[]> {
    return unwrap(
      await api.get<ApiResponse<ChecklistItem[]>>(
        `/companies/${companyId}/quarters/${quarterId}/checklist`,
      ),
    );
  },
  async updateQuarterChecklist(
    companyId: number,
    quarterId: number,
    key: string,
    payload: { is_completed: boolean; notes?: string },
  ): Promise<ChecklistItem> {
    return unwrap(
      await api.patch<ApiResponse<ChecklistItem>>(
        `/companies/${companyId}/quarters/${quarterId}/checklist/${key}`,
        payload,
      ),
    );
  },
  async importAccounts(companyId: number, file: File): Promise<ImportBatch> {
    return unwrap(
      await api.post<ApiResponse<ImportBatch>>(
        `/companies/${companyId}/accounts/import`,
        asFormData(file),
        {
          headers: { "Content-Type": "multipart/form-data" },
        },
      ),
    );
  },
  async getAccountImportStatus(
    companyId: number,
    batchId: number,
  ): Promise<ImportBatch> {
    return unwrap(
      await api.get<ApiResponse<ImportBatch>>(
        `/companies/${companyId}/accounts/import/${batchId}`,
      ),
    );
  },
  async importJournals(companyId: number, file: File): Promise<ImportBatch> {
    return unwrap(
      await api.post<ApiResponse<ImportBatch>>(
        `/companies/${companyId}/journals/import`,
        asFormData(file),
        {
          headers: { "Content-Type": "multipart/form-data" },
        },
      ),
    );
  },
  async getRatios(
    companyId: number,
    params?: { accounting_period_id?: number },
  ): Promise<FinancialAnalysisRatios> {
    return unwrap(
      await api.get<ApiResponse<FinancialAnalysisRatios>>(
        `/companies/${companyId}/financial-analysis/ratios`,
        { params },
      ),
    );
  },
  async getTrends(companyId: number): Promise<FinancialTrends> {
    return unwrap(
      await api.get<ApiResponse<FinancialTrends>>(
        `/companies/${companyId}/financial-analysis/trends`,
      ),
    );
  },
  async getFinancialStatements(
    companyId: number,
  ): Promise<FinancialStatement[]> {
    return unwrap(
      await api.get<ApiResponse<FinancialStatement[]>>(
        `/companies/${companyId}/financial-statements`,
      ),
    );
  },
  async generateFinancialStatement(
    companyId: number,
    payload: { accounting_period_id: number; statement_type: StatementType },
  ): Promise<FinancialStatement> {
    return unwrap(
      await api.post<ApiResponse<FinancialStatement>>(
        `/companies/${companyId}/financial-statements/generate`,
        payload,
      ),
    );
  },
  async getFinancialStatement(
    companyId: number,
    statementId: number,
    params?: { compare_with?: number },
  ): Promise<FinancialStatement | FinancialStatementComparison> {
    return unwrap(
      await api.get<
        ApiResponse<FinancialStatement | FinancialStatementComparison>
      >(`/companies/${companyId}/financial-statements/${statementId}`, {
        params,
      }),
    );
  },
  async approveFinancialStatement(
    companyId: number,
    statementId: number,
  ): Promise<FinancialStatement> {
    return unwrap(
      await api.post<ApiResponse<FinancialStatement>>(
        `/companies/${companyId}/financial-statements/${statementId}/approve`,
      ),
    );
  },
  async lockFinancialStatement(
    companyId: number,
    statementId: number,
  ): Promise<FinancialStatement> {
    return unwrap(
      await api.post<ApiResponse<FinancialStatement>>(
        `/companies/${companyId}/financial-statements/${statementId}/lock`,
      ),
    );
  },
  getFinancialStatementExportUrl(
    companyId: number,
    statementId: number,
    format: ReportFormat,
  ): string {
    return `/api/v1/companies/${companyId}/financial-statements/${statementId}/export?format=${format}`;
  },
};

export const engagementApi = {
  async list(companyId: number): Promise<Engagement[]> {
    return unwrap(
      await api.get<ApiResponse<Engagement[]>>(
        `/companies/${companyId}/engagements`,
      ),
    );
  },
  async get(engagementId: number): Promise<Engagement> {
    return unwrap(
      await api.get<ApiResponse<Engagement>>(`/engagements/${engagementId}`),
    );
  },
  async create(
    companyId: number,
    payload: {
      name: string;
      engagement_type: string;
      start_date: string;
      end_date: string;
      scope?: string;
      objectives?: string;
    },
  ): Promise<Engagement> {
    return unwrap(
      await api.post<ApiResponse<Engagement>>(
        `/companies/${companyId}/engagements`,
        payload,
      ),
    );
  },
  async listEvidence(engagementId: number): Promise<EvidenceFile[]> {
    return unwrap(
      await api.get<ApiResponse<EvidenceFile[]>>(
        `/engagements/${engagementId}/evidence`,
      ),
    );
  },
  async uploadEvidence(
    engagementId: number,
    formData: FormData,
  ): Promise<EvidenceFile> {
    return unwrap(
      await api.post<ApiResponse<EvidenceFile>>(
        `/engagements/${engagementId}/evidence`,
        formData,
        { headers: { "Content-Type": "multipart/form-data" } },
      ),
    );
  },
  async getEvidenceDownloadUrl(
    engagementId: number,
    evidenceId: number,
  ): Promise<{ url: string; expires_at: string }> {
    return unwrap(
      await api.get<ApiResponse<{ url: string; expires_at: string }>>(
        `/engagements/${engagementId}/evidence/${evidenceId}/download`,
      ),
    );
  },
  async listFindings(engagementId: number): Promise<Finding[]> {
    return unwrap(
      await api.get<ApiResponse<Finding[]>>(
        `/engagements/${engagementId}/findings`,
      ),
    );
  },
  async createFinding(
    engagementId: number,
    payload: CreateFindingPayload,
  ): Promise<Finding> {
    return unwrap(
      await api.post<ApiResponse<Finding>>(
        `/engagements/${engagementId}/findings`,
        payload,
      ),
    );
  },
  async listDocumentRequests(engagementId: number): Promise<DocumentRequest[]> {
    return unwrap(
      await api.get<ApiResponse<DocumentRequest[]>>(
        `/engagements/${engagementId}/document-requests`,
      ),
    );
  },
  async createDocumentRequest(
    engagementId: number,
    payload: CreateDocumentRequestPayload,
  ): Promise<DocumentRequest> {
    return unwrap(
      await api.post<ApiResponse<DocumentRequest>>(
        `/engagements/${engagementId}/document-requests`,
        payload,
      ),
    );
  },
  async listWorkingPapers(engagementId: number): Promise<WorkingPaper[]> {
    return unwrap(
      await api.get<ApiResponse<WorkingPaper[]>>(
        `/engagements/${engagementId}/working-papers`,
      ),
    );
  },
  async getWorkingPaper(
    engagementId: number,
    workingPaperId: number,
  ): Promise<WorkingPaper> {
    return unwrap(
      await api.get<ApiResponse<WorkingPaper>>(
        `/engagements/${engagementId}/working-papers/${workingPaperId}`,
      ),
    );
  },
  async updateWorkingPaper(
    engagementId: number,
    workingPaperId: number,
    payload: UpdateWorkingPaperPayload,
  ): Promise<WorkingPaper> {
    return unwrap(
      await api.put<ApiResponse<WorkingPaper>>(
        `/engagements/${engagementId}/working-papers/${workingPaperId}`,
        payload,
      ),
    );
  },
  async signOffWorkingPaper(
    engagementId: number,
    workingPaperId: number,
  ): Promise<WorkingPaper> {
    return unwrap(
      await api.post<ApiResponse<WorkingPaper>>(
        `/engagements/${engagementId}/working-papers/${workingPaperId}/sign-off`,
      ),
    );
  },
  async lockWorkingPaper(
    engagementId: number,
    workingPaperId: number,
  ): Promise<WorkingPaper> {
    return unwrap(
      await api.post<ApiResponse<WorkingPaper>>(
        `/engagements/${engagementId}/working-papers/${workingPaperId}/lock`,
      ),
    );
  },
  async unlockWorkingPaper(
    engagementId: number,
    workingPaperId: number,
  ): Promise<WorkingPaper> {
    return unwrap(
      await api.post<ApiResponse<WorkingPaper>>(
        `/engagements/${engagementId}/working-papers/${workingPaperId}/unlock`,
      ),
    );
  },
  async listReviewNotes(engagementId: number): Promise<ReviewNote[]> {
    return unwrap(
      await api.get<ApiResponse<ReviewNote[]>>(
        `/engagements/${engagementId}/review-notes`,
      ),
    );
  },
  async createReviewNote(
    engagementId: number,
    payload: { working_paper_id: number; content: string },
  ): Promise<ReviewNote> {
    return unwrap(
      await api.post<ApiResponse<ReviewNote>>(
        `/engagements/${engagementId}/review-notes`,
        payload,
      ),
    );
  },
  async resolveReviewNote(
    engagementId: number,
    reviewNoteId: number,
  ): Promise<ReviewNote> {
    return unwrap(
      await api.post<ApiResponse<ReviewNote>>(
        `/engagements/${engagementId}/review-notes/${reviewNoteId}/resolve`,
      ),
    );
  },
  async replyReviewNote(
    engagementId: number,
    reviewNoteId: number,
    message: string,
  ): Promise<ReviewNoteReply> {
    return unwrap(
      await api.post<ApiResponse<ReviewNoteReply>>(
        `/engagements/${engagementId}/review-notes/${reviewNoteId}/reply`,
        { message },
      ),
    );
  },
  async deleteReviewNote(
    engagementId: number,
    reviewNoteId: number,
  ): Promise<void> {
    await api.delete(
      `/engagements/${engagementId}/review-notes/${reviewNoteId}`,
    );
  },
  async listClientDocumentRequests(): Promise<ClientDocumentRequest[]> {
    return unwrap(
      await api.get<ApiResponse<ClientDocumentRequest[]>>(
        "/client/document-requests",
      ),
    );
  },
  async getClientDocumentRequest(id: number): Promise<ClientDocumentRequest> {
    return unwrap(
      await api.get<ApiResponse<ClientDocumentRequest>>(
        `/client/document-requests/${id}`,
      ),
    );
  },
  async uploadClientDocumentRequest(
    id: number,
    file: File,
    description?: string,
  ): Promise<ClientDocumentRequest> {
    const formData = asFormData(file);
    if (description) formData.append("description", description);

    return unwrap(
      await api.post<ApiResponse<ClientDocumentRequest>>(
        `/client/document-requests/${id}/upload`,
        formData,
        { headers: { "Content-Type": "multipart/form-data" } },
      ),
    );
  },
  async getFinding(engagementId: number, findingId: number): Promise<Finding> {
    return unwrap(
      await api.get<ApiResponse<Finding>>(
        `/engagements/${engagementId}/findings/${findingId}`,
      ),
    );
  },
  async updateFinding(
    engagementId: number,
    findingId: number,
    payload: UpdateFindingPayload,
  ): Promise<Finding> {
    return unwrap(
      await api.put<ApiResponse<Finding>>(
        `/engagements/${engagementId}/findings/${findingId}`,
        payload,
      ),
    );
  },
  async resolveFinding(
    engagementId: number,
    findingId: number,
  ): Promise<Finding> {
    return unwrap(
      await api.post<ApiResponse<Finding>>(
        `/engagements/${engagementId}/findings/${findingId}/resolve`,
      ),
    );
  },
  async reopenFinding(
    engagementId: number,
    findingId: number,
    reason: string,
  ): Promise<Finding> {
    return unwrap(
      await api.post<ApiResponse<Finding>>(
        `/engagements/${engagementId}/findings/${findingId}/reopen`,
        { reason },
      ),
    );
  },
  async managementResponseFinding(
    engagementId: number,
    findingId: number,
    payload: { management_response: string },
  ): Promise<Finding> {
    return unwrap(
      await api.post<ApiResponse<Finding>>(
        `/engagements/${engagementId}/findings/${findingId}/management-response`,
        payload,
      ),
    );
  },
  async getAuditPlan(engagementId: number): Promise<AuditPlan> {
    return unwrap(
      await api.get<ApiResponse<AuditPlan>>(
        `/engagements/${engagementId}/audit-plan`,
      ),
    );
  },
  async updateAuditPlan(
    engagementId: number,
    payload: UpdateAuditPlanPayload,
  ): Promise<AuditPlan> {
    return unwrap(
      await api.put<ApiResponse<AuditPlan>>(
        `/engagements/${engagementId}/audit-plan`,
        payload,
      ),
    );
  },
  async listRiskAssessments(engagementId: number): Promise<RiskAssessment[]> {
    return unwrap(
      await api.get<ApiResponse<RiskAssessment[]>>(
        `/engagements/${engagementId}/risk-assessments`,
      ),
    );
  },
  async createRiskAssessment(
    engagementId: number,
    payload: {
      risk_area: string;
      risk_level: "low" | "medium" | "high" | "critical";
      description?: string;
      mitigation?: string;
      inherent_risk?: string;
      control_risk?: string;
      residual_risk?: string;
      fraud_risk?: string;
      risk_category?: string;
      is_significant?: boolean;
    },
  ): Promise<RiskAssessment> {
    return unwrap(
      await api.post<ApiResponse<RiskAssessment>>(
        `/engagements/${engagementId}/risk-assessments`,
        payload,
      ),
    );
  },
  async updateRiskAssessment(
    engagementId: number,
    riskId: number,
    payload: UpdateRiskAssessmentPayload,
  ): Promise<RiskAssessment> {
    return unwrap(
      await api.put<ApiResponse<RiskAssessment>>(
        `/engagements/${engagementId}/risk-assessments/${riskId}`,
        payload,
      ),
    );
  },
  async deleteRiskAssessment(
    engagementId: number,
    riskId: number,
  ): Promise<void> {
    await api.delete(`/engagements/${engagementId}/risk-assessments/${riskId}`);
  },
  async listAuditPrograms(engagementId: number): Promise<AuditProgram[]> {
    return unwrap(
      await api.get<ApiResponse<AuditProgram[]>>(
        `/engagements/${engagementId}/audit-programs`,
      ),
    );
  },
  async createAuditProgram(
    engagementId: number,
    payload: { name: string; objectives?: string },
  ): Promise<AuditProgram> {
    return unwrap(
      await api.post<ApiResponse<AuditProgram>>(
        `/engagements/${engagementId}/audit-programs`,
        payload,
      ),
    );
  },
  async addAuditProgramStep(
    engagementId: number,
    programId: number,
    payload: { step_number: string; procedure: string; assigned_to?: number },
  ): Promise<AuditProgramStep> {
    return unwrap(
      await api.post<ApiResponse<AuditProgramStep>>(
        `/engagements/${engagementId}/audit-programs/${programId}/steps`,
        payload,
      ),
    );
  },
  async completeAuditProgramStep(
    engagementId: number,
    programId: number,
    stepId: number,
  ): Promise<AuditProgramStep> {
    return unwrap(
      await api.post<ApiResponse<AuditProgramStep>>(
        `/engagements/${engagementId}/audit-programs/${programId}/steps/${stepId}/complete`,
      ),
    );
  },
};

export const reportingApi = {
  async list(companyId: number): Promise<PagedResult<ReportItem>> {
    const page = unwrapPage(
      await api.get<ApiPaginatedResponse<ReportItem>>(
        `/companies/${companyId}/reports`,
      ),
    );

    return { ...page, items: page.items.map(mapReport) };
  },
  async generate(
    companyId: number,
    payload: {
      report_type: ReportType;
      title: string;
      format?: ReportFormat;
      parameters?: {
        accounting_period_id?: number;
        engagement_id?: number;
        financial_statement_id?: number;
      };
    },
  ): Promise<ReportItem> {
    const report = unwrap(
      await api.post<ApiResponse<ReportItem>>(
        `/companies/${companyId}/reports/generate`,
        payload,
      ),
    );

    return mapReport(report);
  },
  async download(
    companyId: number,
    reportId: number,
  ): Promise<{ url: string; expires_at: string }> {
    return unwrap(
      await api.get<ApiResponse<{ url: string; expires_at: string }>>(
        `/companies/${companyId}/reports/${reportId}/download`,
      ),
    );
  },
  async approve(companyId: number, reportId: number): Promise<ReportItem> {
    return mapReport(
      unwrap(
        await api.post<ApiResponse<ReportItem>>(
          `/companies/${companyId}/reports/${reportId}/approve`,
        ),
      ),
    );
  },
  async get(companyId: number, reportId: number): Promise<ReportItem> {
    return mapReport(
      unwrap(
        await api.get<ApiResponse<ReportItem>>(
          `/companies/${companyId}/reports/${reportId}`,
        ),
      ),
    );
  },
  async getDownloadUrl(
    companyId: number,
    reportId: number,
  ): Promise<{ url: string; expires_at: string }> {
    return unwrap(
      await api.get<ApiResponse<{ url: string; expires_at: string }>>(
        `/companies/${companyId}/reports/${reportId}/download`,
      ),
    );
  },
};

export const notificationsApi = {
  async list(page = 1): Promise<PagedResult<DbNotification>> {
    return unwrapPage(
      await api.get<ApiPaginatedResponse<DbNotification>>(
        `/notifications?page=${page}`,
      ),
    );
  },
  async markRead(id: string): Promise<void> {
    await api.post<ApiResponse<null>>(`/notifications/${id}/read`);
  },
  async markAllRead(): Promise<void> {
    await api.post<ApiResponse<null>>("/notifications/read-all");
  },
  async preferences(): Promise<NotificationPreference[]> {
    return unwrap(
      await api.get<ApiResponse<NotificationPreference[]>>(
        "/notifications/preferences",
      ),
    );
  },
  async updatePreferences(
    preferences: Array<
      Pick<NotificationPreference, "channel" | "event_type" | "enabled">
    >,
  ): Promise<NotificationPreference[]> {
    return unwrap(
      await api.put<ApiResponse<NotificationPreference[]>>(
        "/notifications/preferences",
        { preferences },
      ),
    );
  },
};

export const futureIntegrationsApi = {
  async statuses(): Promise<ExternalIntegrationStatus[]> {
    return unwrap(
      await api.get<ApiResponse<ExternalIntegrationStatus[]>>(
        "/future/integrations",
      ),
    );
  },
  async execute(
    integration: string,
    operation: string,
    parameters: Record<string, string | number | boolean | null> = {},
  ): Promise<never> {
    return unwrap(
      await api.post<ApiResponse<never>>(
        `/future/integrations/${integration}/execute`,
        { operation, parameters },
      ),
    );
  },
};

export const adminApi = {
  async inviteUser(payload: InviteUserPayload): Promise<InviteUserResult> {
    return unwrap(
      await api.post<ApiResponse<InviteUserResult>>(
        "/admin/users/invite",
        payload,
      ),
    );
  },
  async listUsers(params?: ApiListParams): Promise<PagedResult<AdminUser>> {
    return unwrapPage(
      await api.get<ApiPaginatedResponse<AdminUser>>("/admin/users", {
        params,
      }),
    );
  },
  async getUser(userId: number): Promise<AdminUser> {
    return unwrap(
      await api.get<ApiResponse<AdminUser>>(`/admin/users/${userId}`),
    );
  },
  async updateUser(
    userId: number,
    payload: UpdateAdminUserPayload,
  ): Promise<AdminUser> {
    return unwrap(
      await api.put<ApiResponse<AdminUser>>(`/admin/users/${userId}`, payload),
    );
  },
  async deleteUser(userId: number): Promise<void> {
    await api.delete(`/admin/users/${userId}`);
  },
  async suspendUser(userId: number): Promise<AdminUser> {
    return unwrap(
      await api.post<ApiResponse<AdminUser>>(`/admin/users/${userId}/suspend`),
    );
  },
  async activateUser(userId: number): Promise<AdminUser> {
    return unwrap(
      await api.post<ApiResponse<AdminUser>>(`/admin/users/${userId}/activate`),
    );
  },
  async listRoles(): Promise<AdminRole[]> {
    return unwrap(await api.get<ApiResponse<AdminRole[]>>("/admin/roles"));
  },
  async assignRole(userId: number, roleId: number): Promise<AdminUser> {
    return unwrap(
      await api.post<ApiResponse<AdminUser>>(`/admin/users/${userId}/roles`, {
        role_id: roleId,
      }),
    );
  },
  async revokeRole(userId: number, roleId: number): Promise<AdminUser> {
    return unwrap(
      await api.delete<ApiResponse<AdminUser>>(
        `/admin/users/${userId}/roles/${roleId}`,
      ),
    );
  },
  async listAuditTrail(params?: ApiListParams): Promise<PagedResult<AuditLog>> {
    return unwrapPage(
      await api.get<ApiPaginatedResponse<AuditLog>>("/admin/audit-trail", {
        params,
      }),
    );
  },
};

export const auditControlsApi = {
  async listControls(engagementId: number): Promise<InternalControl[]> {
    return unwrap(
      await api.get<ApiResponse<InternalControl[]>>(
        `/engagements/${engagementId}/internal-controls`,
      ),
    );
  },
  async getControl(
    engagementId: number,
    controlId: number,
  ): Promise<InternalControl> {
    return unwrap(
      await api.get<ApiResponse<InternalControl>>(
        `/engagements/${engagementId}/internal-controls/${controlId}`,
      ),
    );
  },
  async createControl(
    engagementId: number,
    payload: CreateInternalControlPayload,
  ): Promise<InternalControl> {
    return unwrap(
      await api.post<ApiResponse<InternalControl>>(
        `/engagements/${engagementId}/internal-controls`,
        payload,
      ),
    );
  },
  async updateControl(
    engagementId: number,
    controlId: number,
    payload: UpdateInternalControlPayload,
  ): Promise<InternalControl> {
    return unwrap(
      await api.put<ApiResponse<InternalControl>>(
        `/engagements/${engagementId}/internal-controls/${controlId}`,
        payload,
      ),
    );
  },
  async deleteControl(engagementId: number, controlId: number): Promise<void> {
    await api.delete(
      `/engagements/${engagementId}/internal-controls/${controlId}`,
    );
  },
  async listRisks(
    engagementId: number,
    controlId: number,
  ): Promise<ControlRisk[]> {
    return unwrap(
      await api.get<ApiResponse<ControlRisk[]>>(
        `/engagements/${engagementId}/internal-controls/${controlId}/risks`,
      ),
    );
  },
  async addRisk(
    engagementId: number,
    controlId: number,
    payload: ControlRiskPayload,
  ): Promise<ControlRisk> {
    return unwrap(
      await api.post<ApiResponse<ControlRisk>>(
        `/engagements/${engagementId}/internal-controls/${controlId}/risks`,
        payload,
      ),
    );
  },
  async updateRisk(
    engagementId: number,
    controlId: number,
    riskId: number,
    payload: UpdateControlRiskPayload,
  ): Promise<ControlRisk> {
    return unwrap(
      await api.put<ApiResponse<ControlRisk>>(
        `/engagements/${engagementId}/internal-controls/${controlId}/risks/${riskId}`,
        payload,
      ),
    );
  },
  async deleteRisk(
    engagementId: number,
    controlId: number,
    riskId: number,
  ): Promise<void> {
    await api.delete(
      `/engagements/${engagementId}/internal-controls/${controlId}/risks/${riskId}`,
    );
  },
};

export const redFlagApi = {
  async scanJournals(companyId: number): Promise<RedFlagScanResult> {
    return unwrap(
      await api.post<ApiResponse<RedFlagScanResult>>(
        `/companies/${companyId}/journals/red-flag-scan`,
      ),
    );
  },
};
