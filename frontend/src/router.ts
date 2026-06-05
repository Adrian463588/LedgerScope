import type { Component } from 'vue';
import { computed, ref } from 'vue';

import AuditEngagementsPage from '@/pages/Audit/AuditEngagementsPage.vue';
import AuditFindingsPage from '@/pages/Audit/AuditFindingsPage.vue';
import AuditProgramPage from '@/pages/Audit/AuditProgramPage.vue';
import RiskAssessmentPage from '@/pages/Audit/RiskAssessmentPage.vue';
import RiskControlMatrixPage from '@/pages/Audit/RiskControlMatrixPage.vue';
import WorkingPaperPage from '@/pages/Audit/WorkingPaperPage.vue';
import ChartOfAccountsPage from '@/pages/Accounting/ChartOfAccountsPage.vue';
import JournalEntriesPage from '@/pages/Accounting/JournalEntriesPage.vue';
import NewJournalPage from '@/pages/Accounting/NewJournalPage.vue';
import QuarterlyClosingPage from '@/pages/Accounting/QuarterlyClosingPage.vue';
import ReconciliationPage from '@/pages/Accounting/ReconciliationPage.vue';
import TrialBalancePage from '@/pages/Accounting/TrialBalancePage.vue';
import LoginPage from '@/pages/Auth/LoginPage.vue';
import MfaPage from '@/pages/Auth/MfaPage.vue';
import ClientEvidencePortalPage from '@/pages/Client/ClientEvidencePortalPage.vue';
import CompaniesPage from '@/pages/Companies/CompaniesPage.vue';
import CompanyProfilePage from '@/pages/Companies/CompanyProfilePage.vue';
import DashboardPage from '@/pages/Dashboard/DashboardPage.vue';
import EvidencePage from '@/pages/Evidence/EvidencePage.vue';
import FinancialStatementsPage from '@/pages/Financial/FinancialStatementsPage.vue';
import RatioAnalysisPage from '@/pages/Financial/RatioAnalysisPage.vue';
import StatementBuilderPage from '@/pages/Financial/StatementBuilderPage.vue';
import ReportingHubPage from '@/pages/Reports/ReportingHubPage.vue';

export interface AppRoute {
  path: string;
  label: string;
  component: Component;
  layout: 'app' | 'auth' | 'client';
  group?: string;
}

export const routes: AppRoute[] = [
  { path: '/login', label: 'Login', component: LoginPage, layout: 'auth' },
  { path: '/mfa', label: 'MFA Verification', component: MfaPage, layout: 'auth' },
  { path: '/', label: 'Dashboard', component: DashboardPage, layout: 'app', group: 'Main' },
  { path: '/dashboard', label: 'Dashboard', component: DashboardPage, layout: 'app', group: 'Main' },
  { path: '/companies', label: 'Companies', component: CompaniesPage, layout: 'app', group: 'Main' },
  { path: '/companies/acme', label: 'Company Profile', component: CompanyProfilePage, layout: 'app', group: 'Main' },
  { path: '/chart-of-accounts', label: 'Chart of Accounts', component: ChartOfAccountsPage, layout: 'app', group: 'Accounting' },
  { path: '/journal-entries', label: 'Journal Entries', component: JournalEntriesPage, layout: 'app', group: 'Accounting' },
  { path: '/journal-entries/new', label: 'New Journal', component: NewJournalPage, layout: 'app', group: 'Accounting' },
  { path: '/trial-balance', label: 'Trial Balance', component: TrialBalancePage, layout: 'app', group: 'Accounting' },
  { path: '/quarterly-closing', label: 'Quarterly Closing', component: QuarterlyClosingPage, layout: 'app', group: 'Accounting' },
  { path: '/reconciliation', label: 'Reconciliation', component: ReconciliationPage, layout: 'app', group: 'Accounting' },
  { path: '/financial-statements', label: 'Financial Statements', component: FinancialStatementsPage, layout: 'app', group: 'Financial' },
  { path: '/statement-builder', label: 'Statement Builder', component: StatementBuilderPage, layout: 'app', group: 'Financial' },
  { path: '/ratio-analysis', label: 'Ratio Analysis', component: RatioAnalysisPage, layout: 'app', group: 'Financial' },
  { path: '/audit-engagements', label: 'Audit Engagements', component: AuditEngagementsPage, layout: 'app', group: 'Audit' },
  { path: '/risk-assessment', label: 'Risk Assessment', component: RiskAssessmentPage, layout: 'app', group: 'Audit' },
  { path: '/risk-control-matrix', label: 'Risk Control Matrix', component: RiskControlMatrixPage, layout: 'app', group: 'Audit' },
  { path: '/audit-program', label: 'Audit Program', component: AuditProgramPage, layout: 'app', group: 'Audit' },
  { path: '/working-paper', label: 'Working Paper', component: WorkingPaperPage, layout: 'app', group: 'Audit' },
  { path: '/audit-findings', label: 'Audit Findings', component: AuditFindingsPage, layout: 'app', group: 'Audit' },
  { path: '/evidence', label: 'Evidence', component: EvidencePage, layout: 'app', group: 'Evidence' },
  { path: '/client/evidence', label: 'Client Evidence Portal', component: ClientEvidencePortalPage, layout: 'client', group: 'Evidence' },
  { path: '/reports', label: 'Reporting Hub', component: ReportingHubPage, layout: 'app', group: 'Reports' },
];

const currentPath = ref(normalizePath(window.location.pathname));

window.addEventListener('popstate', () => {
  currentPath.value = normalizePath(window.location.pathname);
});

function normalizePath(path: string): string {
  return path === '' ? '/' : path;
}

export function navigateTo(path: string): void {
  if (currentPath.value === path) {
    return;
  }

  window.history.pushState({}, '', path);
  currentPath.value = normalizePath(path);
  window.scrollTo({ top: 0 });
}

export function useRouter() {
  const route = computed(() => routes.find((item) => item.path === currentPath.value) ?? routes.find((item) => item.path === '/') ?? routes[0]);
  return { currentPath, route, routes, navigateTo };
}
