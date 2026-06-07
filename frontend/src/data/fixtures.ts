export const demoKpis = [
  { label: 'Total Assets', value: 'IDR 50.2B', trend: 'up' },
  { label: 'Total Liabilities', value: 'IDR 12.4B', trend: 'down' },
  { label: 'Net Income', value: 'IDR 5.1B', trend: 'up' },
  { label: 'Cash Flow', value: 'IDR 2.3B', trend: 'down' },
];

export const unsupportedFeatures = {
  evidence: { title: 'Evidence Management', body: 'This module is planned for a future phase.', endpoint: '/api/v1/engagements/{id}/evidence' },
  audit: { title: 'Audit Procedures', body: 'This module is planned for a future phase.', endpoint: '/api/v1/engagements/{id}/audit-programs' },
  risk: { title: 'Risk Assessment', body: 'This module is planned for a future phase.', endpoint: '/api/v1/engagements/{id}/risk-assessments' },
  workingPaper: { title: 'Working Papers', body: 'This module is planned for a future phase.', endpoint: '/api/v1/engagements/{id}/working-papers' },
  findings: { title: 'Audit Findings', body: 'This module is planned for a future phase.', endpoint: '/api/v1/engagements/{id}/findings' },
};
