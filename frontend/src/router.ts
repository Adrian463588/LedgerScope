import { createRouter, createWebHistory } from 'vue-router';
import type { Component } from 'vue';
import { computed } from 'vue';

import { useAuthStore } from '@/stores/auth.store';

import AuditEngagementsPage from '@/pages/Audit/AuditEngagementsPage.vue';
import AuditFindingsPage from '@/pages/Audit/AuditFindingsPage.vue';
import AuditPlanningPage from '@/pages/Audit/AuditPlanningPage.vue';
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
import NotFoundPage from '@/pages/not-found-page.vue';

import RedFlagScanPage from '@/pages/Audit/red-flag-scan-page.vue';
import AdminUsersPage from '@/pages/Admin/admin-users-page.vue';
import AuditTrailPage from '@/pages/Admin/audit-trail-page.vue';


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
  { path: '/', label: 'Home', component: DashboardPage, layout: 'app' },
  { path: '/dashboard', label: 'Dashboard', component: DashboardPage, layout: 'app', group: 'Main' },
  { path: '/companies', label: 'Companies', component: CompaniesPage, layout: 'app', group: 'Main' },
  { path: '/companies/:id', label: 'Company Profile', component: CompanyProfilePage, layout: 'app', group: 'Main' },
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
  { path: '/engagements/:id/risk-assessment', label: 'Risk Assessment', component: RiskAssessmentPage, layout: 'app' },
  { path: '/engagements/:id/risk-control-matrix', label: 'Risk Control Matrix', component: RiskControlMatrixPage, layout: 'app' },
  { path: '/audit-program', label: 'Audit Program', component: AuditProgramPage, layout: 'app', group: 'Audit' },
  { path: '/working-paper', label: 'Working Paper', component: WorkingPaperPage, layout: 'app', group: 'Audit' },
  { path: '/engagements/:id/working-papers/:wpId', label: 'Working Paper Details', component: WorkingPaperPage, layout: 'app' },
  { path: '/engagements/:id/audit-plan', label: 'Audit Planning', component: AuditPlanningPage, layout: 'app' },
  { path: '/audit-findings', label: 'Audit Findings', component: AuditFindingsPage, layout: 'app', group: 'Audit' },
  { path: '/red-flag-scan', label: 'Journal Red-Flags', component: RedFlagScanPage, layout: 'app', group: 'Audit' },
  { path: '/evidence', label: 'Evidence', component: EvidencePage, layout: 'app', group: 'Evidence' },
  { path: '/client/evidence', label: 'Client Evidence Portal', component: ClientEvidencePortalPage, layout: 'client', group: 'Evidence' },
  { path: '/reports', label: 'Reporting Hub', component: ReportingHubPage, layout: 'app', group: 'Reports' },
  { path: '/admin/users', label: 'User Management', component: AdminUsersPage, layout: 'app', group: 'Admin' },
  { path: '/admin/audit-trail', label: 'Audit Trail', component: AuditTrailPage, layout: 'app', group: 'Admin' },
];

export const router = createRouter({
  history: createWebHistory(),
  routes: [
    ...routes.map((r) => ({
      path: r.path,
      component: r.component,
      meta: {
        label: r.label,
        layout: r.layout,
        group: r.group,
        component: r.component,
      },
    })),
    // Fallback 404
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      component: NotFoundPage,
      meta: { label: 'Not Found', layout: 'auth', component: NotFoundPage },
    },
  ],
});

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // Attempt to fetch user profile once if not logged in and not currently loading,
  // to support page refresh/initial load.
  if (authStore.user === null && !authStore.isLoading && to.path !== '/login') {
    try {
      await authStore.fetchMe();
    } catch {
      // Ignored: fetchMe updates store user state, 401 stays null
    }
  }

  const isAuthenticated = authStore.isAuthenticated;

  if ((to.path === '/login' || to.path === '/mfa') && isAuthenticated) {
    next('/dashboard');
  } else if (to.path !== '/login' && to.path !== '/mfa' && !isAuthenticated) {
    next('/login');
  } else {
    // Role-based layout isolation guard
    if (isAuthenticated && authStore.user) {
      const isClient = authStore.user.roles?.some(
        (r) => r.name === 'client_user' || r.name === 'client_admin'
      );
      const routeLayout = to.meta?.['layout'];

      if (isClient && routeLayout === 'app') {
        next('/client/evidence');
        return;
      }

      if (!isClient && routeLayout === 'client') {
        next('/dashboard');
        return;
      }
    }
    next();
  }
});

export function navigateTo(path: string): void {
  void router.push(path);
}

export function useRouter() {
  const currentPath = computed(() => router.currentRoute.value.path);
  const route = computed(() => {
    const matchedRoute = router.currentRoute.value;
    return {
      path: matchedRoute.path,
      label: (matchedRoute.meta['label'] || '') as string,
      component: (matchedRoute.meta['component'] || NotFoundPage) as Component,
      layout: (matchedRoute.meta['layout'] || 'app') as 'app' | 'auth' | 'client',
      group: matchedRoute.meta['group'] as string | undefined,
    };
  });

  return { currentPath, route, routes, navigateTo };
}
