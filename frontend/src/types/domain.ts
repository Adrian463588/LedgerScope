export type StatusTone = 'neutral' | 'success' | 'warning' | 'danger' | 'info' | 'locked';

export type UserRole = 'admin' | 'manager' | 'accountant' | 'auditor' | 'client';

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
  industry: string;
  fiscal_year_end: string;
  status: 'active' | 'inactive' | 'archived';
  reporting_period: string;
}

export type JournalStatus = 'draft' | 'submitted' | 'reviewed' | 'approved' | 'posted' | 'rejected' | 'reversed';

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
  journal_number: string;
  company_id: number;
  period_id: number;
  date: string;
  description: string;
  reference_number: string | null;
  status: JournalStatus;
  source_type: 'manual' | 'import' | 'recurring' | 'reversal' | 'system';
  reversed_from_id: number | null;
  prepared_by: UserSummary;
  reviewed_by: UserSummary | null;
  approved_by: UserSummary | null;
  lines: JournalEntryLine[];
  created_at: string;
  updated_at: string;
}

export interface Account {
  id: number;
  code: string;
  name: string;
  type: 'asset' | 'liability' | 'equity' | 'revenue' | 'expense';
  statement: string;
  balance: string;
  status: 'mapped' | 'unmapped' | 'locked';
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

export interface Engagement {
  id: number;
  name: string;
  company_id: number;
  type: 'audit' | 'review' | 'compilation' | 'tax';
  status: 'planning' | 'fieldwork' | 'review' | 'reporting' | 'completed';
  period: string;
  progress: number;
  risk: 'low' | 'medium' | 'high' | 'critical';
}

export interface ReportItem {
  id: number;
  name: string;
  type: string;
  status: 'ready' | 'generating' | 'failed';
  version: string;
  generated_at: string;
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
