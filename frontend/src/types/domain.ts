export interface ReportParameters {
  company_id?: number;
  accounting_period_id?: number;
  financial_statement_id?: number;
  engagement_id?: number;
  [key: string]: string | number | boolean | null | undefined;
}

export type AuditSnapshotValue =
  | string
  | number
  | boolean
  | null
  | AuditSnapshotValue[]
  | { [key: string]: AuditSnapshotValue };

export type AuditSnapshot = { [key: string]: AuditSnapshotValue };

export interface RedFlagContext {
  debit?: string;
  credit?: string;
  threshold?: string;
  [key: string]: string | number | boolean | null | undefined;
}

export type StatusTone =
  | "neutral"
  | "success"
  | "warning"
  | "danger"
  | "info"
  | "locked";

export type UserRole =
  | "admin"
  | "manager"
  | "accountant"
  | "auditor"
  | "client";

export interface UserSummary {
  id: number;
  name: string;
  email: string;
}

export interface AuthUser extends UserSummary {
  phone: string | null;
  avatar_path: string | null;
  status: string;
  mfa_enabled: boolean;
  permissions: string[];
  roles: Array<{ id: number; name: string; display_name: string }>;
}

export interface Company {
  id: number;
  name: string;
  legal_name: string;
  registration_number?: string | null;
  tax_id?: string | null;
  industry: string;
  currency?: string;
  fiscal_year_start_month?: number;
  address?: string | null;
  city?: string | null;
  country?: string | null;
  phone?: string | null;
  email?: string | null;
  website?: string | null;
  fiscal_year_end?: string | null;
  status: "active" | "inactive" | "archived";
  reporting_period?: string | null;
}

export interface CompanyContact {
  id: number;
  company_id: number;
  name: string;
  email: string | null;
  phone: string | null;
  role: string | null;
  is_primary: boolean;
}

export type JournalStatus =
  | "draft"
  | "submitted"
  | "reviewed"
  | "approved"
  | "posted"
  | "rejected"
  | "reversed";

export interface JournalEntryLine {
  id: number;
  account_id: number;
  account_code: string;
  account_name: string;
  debit: string;
  credit: string;
  description: string | null;
}

export interface JournalEntry {
  id: number;
  journal_number: string | null;
  company_id: number;
  period_id?: number;
  accounting_period_id?: number;
  date?: string;
  journal_date?: string;
  description: string;
  reference_number?: string | null;
  reference?: string | null;
  status: JournalStatus;
  source_type: "manual" | "import" | "recurring" | "reversal" | "system";
  reversed_from_id: number | null;
  prepared_by?: UserSummary;
  reviewed_by?: UserSummary | null;
  approved_by?: UserSummary | null;
  lines: JournalEntryLine[];
  created_at: string;
  updated_at: string;
}

export interface Account {
  id: number;
  code: string;
  name: string;
  type: "asset" | "liability" | "equity" | "revenue" | "expense";
  statement: string;
  balance: string;
  status: "mapped" | "unmapped" | "locked";
  account_code?: string;
  account_name?: string;
  account_type?: string;
  is_active?: boolean;
}

export interface TrialBalanceRow {
  account_code: string;
  account_name: string;
  opening_debit: string;
  opening_credit: string;
  movement_debit: string;
  movement_credit: string;
  ending_debit: string;
  ending_credit: string;
}

export interface TrialBalanceSnapshot {
  id: number;
  company_id: number;
  accounting_period_id: number;
  status: string;
  total_debit: string;
  total_credit: string;
  is_balanced: boolean;
  lines: TrialBalanceRow[];
}

export interface Engagement {
  id: number;
  name: string;
  company_id: number;
  type: "audit" | "review" | "compilation" | "tax";
  engagement_type?: string;
  status: "planning" | "fieldwork" | "review" | "reporting" | "completed";
  period: string;
  progress?: number;
  risk?: "low" | "medium" | "high" | "critical" | "unassessed";
}

export type ReportStatus =
  | "pending"
  | "queued"
  | "generating"
  | "completed"
  | "failed"
  | "approved"
  | "expired";

export type ReportFormat = "pdf" | "xlsx" | "csv";

export type ReportType =
  | "trial_balance"
  | "income_statement"
  | "balance_sheet"
  | "cash_flow"
  | "equity_changes"
  | "audit_report"
  | "engagement_summary";

export interface ReportItem {
  id: number;
  name: string;
  type: ReportType | string;
  status: ReportStatus;
  version: string;
  generated_at: string | null;
  company_id?: number;
  report_type?: ReportType;
  title?: string;
  format?: ReportFormat;
  parameters?: ReportParameters | null;
  error_message?: string | null;
}

export interface FiscalYear {
  id: number;
  company_id: number;
  year: number;
  start_date: string;
  end_date: string;
  status: string;
}

export interface AccountingPeriod {
  id: number;
  company_id: number;
  fiscal_year_id: number;
  period_name: string;
  period_type: string;
  start_date: string;
  end_date: string;
  status: string;
  is_locked?: boolean;
}

export interface Quarter {
  id: number;
  company_id: number;
  fiscal_year_id: number;
  quarter_code: string;
  start_date: string;
  end_date: string;
  status: string;
  is_locked: boolean;
  locked_at?: string | null;
  quarter_name?: string;
  unlock_reason?: string | null;
}

export interface ChecklistItem {
  id: number;
  quarter_id: number;
  checklist_key: string;
  is_required: boolean;
  is_completed: boolean;
  notes: string | null;
  completed_at?: string | null;
  checklist_name?: string;
  description?: string | null;
}

export interface ImportBatch {
  id: number;
  company_id: number;
  import_type: string;
  status: string;
  original_filename: string;
  error_message?: string | null;
  created_at: string;
}

export type ReconciliationStatus = "draft" | "approved" | "locked";

export interface Reconciliation {
  id: number;
  company_id: number;
  account_id: number;
  accounting_period_id: number;
  reconciliation_type: "bank" | "ar" | "ap";
  status: ReconciliationStatus;
  book_balance: string;
  bank_balance: string;
  difference: string;
  approved_at?: string | null;
  locked_at?: string | null;
}

export interface FinancialAnalysisRatios {
  current_ratio: string;
  quick_ratio: string;
  debt_to_equity: string;
  gross_profit_margin: string;
  net_profit_margin: string;
  roa: string;
  roe: string;
  raw?: Record<string, string>;
}

export interface FinancialTrends {
  labels: string[];
  revenues: string[];
  expenses: string[];
  net_incomes: string[];
}

export type StatementType =
  | "balance_sheet"
  | "income_statement"
  | "cash_flow"
  | "equity_changes";

export interface StatementLine {
  account_id: number;
  account_name: string;
  amount: string;
}

export interface StatementGroup {
  lines: StatementLine[];
  total: string;
}

export interface FinancialStatementData {
  revenue?: StatementGroup;
  cogs?: StatementGroup;
  expenses?: StatementGroup;
  other_income?: StatementGroup;
  other_expenses?: StatementGroup;
  assets?: StatementGroup;
  liabilities_and_equity?: StatementGroup;
  net_income?: string;
}

export interface FinancialStatement {
  id: number;
  company_id: number;
  accounting_period_id: number;
  statement_type: StatementType;
  status: string;
  version: number;
  is_locked: boolean;
  data: FinancialStatementData;
  generated_at?: string | null;
}

export interface FinancialStatementComparison {
  statement: FinancialStatement;
  comparison: FinancialStatement | null;
}

export interface EvidenceFile {
  id: number;
  engagement_id: number;
  file_name: string;
  original_name?: string;
  file_size_bytes: number;
  status?: string;
}

export interface Finding {
  id: number;
  title: string;
  description: string;
  category: string;
  severity: string;
  status: string;
  management_response?: string | null;
  recommendation?: string | null;
}

export interface DocumentRequest {
  id: number;
  title: string;
  description?: string | null;
  status: string;
  due_date?: string | null;
}

export interface WorkingPaper {
  id: number;
  engagement_id: number;
  title: string;
  paper_ref?: string | null;
  status: string;
  is_locked: boolean;
  content?: string | null;
  evidence_files?: EvidenceFile[];
  review_notes?: ReviewNote[];
  prepared_by?: UserSummary | string | null;
  reviewed_by?: UserSummary | string | null;
  reviewed_at?: string | null;
}

export interface ReviewReply {
  id: number;
  message: string;
  user?: UserSummary | null;
}

export interface ReviewNote {
  id: number;
  working_paper_id: number;
  content: string;
  status: string;
  created_by?: UserSummary | null;
  replies?: ReviewReply[];
}

export interface AuditPlan {
  id: number;
  engagement_id: number;
  status?: string;
  overall_materiality?: string;
  performance_materiality?: string;
  trivial_threshold?: string;
  audit_strategy?: string;
  planning_checklist?: Array<{
    key: string;
    name: string;
    is_completed: boolean;
  }>;
}

export interface RiskAssessment {
  id: number;
  engagement_id: number;
  risk_area: string;
  description?: string | null;
  mitigation?: string | null;
  risk_level?: string;
  likelihood?: string | null;
  impact?: string | null;
  inherent_risk?: string | null;
  control_risk?: string | null;
  residual_risk?: string | null;
  fraud_risk?: string | null;
  risk_category?: string | null;
  is_significant?: boolean;
}

export interface AuditProgramStep {
  id: number;
  audit_program_id: number;
  step_number: string;
  procedure: string;
  assigned_to?: number | null;
  is_completed: boolean;
  completed_at?: string | null;
}

export interface AuditProgram {
  id: number;
  engagement_id: number;
  name: string;
  objectives?: string | null;
  status?: string;
  steps?: AuditProgramStep[];
}

export interface InternalControl {
  id: number;
  engagement_id: number;
  name: string;
  control_type?: string;
  owner?: string | null;
  description?: string | null;
  effectiveness?: string;
}

export interface ControlRisk {
  id: number;
  risk_name: string;
  likelihood: string;
  impact: string;
  residual_risk?: string;
}

export interface ClientDocumentRequest extends DocumentRequest {
  engagement?: Engagement;
  evidence_file?: EvidenceFile | null;
  rejection_reason?: string | null;
}

export interface AdminRole {
  id: number;
  name: string;
  display_name: string;
}

export interface AdminUser extends UserSummary {
  status: string;
  roles: AdminRole[];
}

export interface AuditLog {
  id: number;
  action: string;
  ip_address?: string | null;
  user_agent?: string | null;
  created_at: string;
  user?: UserSummary | null;
  before_value?: AuditSnapshot | null;
  after_value?: AuditSnapshot | null;
}

export interface DbNotification {
  id: string;
  type: string;
  data: {
    title: string;
    message: string;
    type: string;
    action_url?: string;
  };
  read_at: string | null;
  created_at: string;
}

export type NotificationChannel = "email" | "app" | "weekly_digest";
export type NotificationEventType =
  | "document_request"
  | "review_note"
  | "finding"
  | "evidence"
  | "report";

export interface NotificationPreference {
  id: number;
  channel: NotificationChannel;
  event_type: NotificationEventType;
  enabled: boolean;
}

export interface ExternalIntegrationStatus {
  key: string;
  mode: string;
  configured: boolean;
  message: string;
}

export interface RedFlag {
  journal_id: number;
  journal_number: string | null;
  journal_date: string;
  rule: string;
  message: string;
  context?: RedFlagContext;
}

export interface KpiCard {
  label: string;
  value: string;
  trend: string;
  tone: StatusTone;
}

export interface UnsupportedFeature {
  title: string;
  body: string;
  endpoint: string;
}
