# Infrastructure.md — LedgerScope E2E Accounting, Financial Analysis & Audit Platform

## 1. Document Overview

### 1.1 Purpose

This document defines the end-to-end infrastructure design for **LedgerScope**, a Laravel-based accounting, financial analysis, quarterly bookkeeping, and audit management platform.

This document covers:

1. User Flow
2. Architecture
3. Database Schema

The goal is to provide a complete technical blueprint for development, deployment, security, and data design.

---

## 2. System Context

### 2.1 Product Name

**LedgerScope**

### 2.2 Product Type

Web-based accounting, financial analysis, and audit management platform.

### 2.3 Main Platform Stack

```txt
Backend      : PHP 8.4 + Laravel 13
Frontend     : Inertia.js + Vue 3 + TypeScript + Tailwind CSS
Database     : PostgreSQL 17+
Cache        : Redis
Queue        : Laravel Queue + Redis
Queue Monitor: Laravel Horizon
Storage      : S3-compatible private object storage
Search       : PostgreSQL full-text search / Meilisearch optional
Web Server   : Nginx
Runtime      : PHP-FPM
Deployment   : Docker + GitHub Actions CI/CD
Monitoring   : Sentry / Bugsnag + Laravel Horizon + Health Check
```

### 2.4 Core Business Domains

LedgerScope is divided into the following domains:

```txt
Identity & Access
Company & Client Management
Engagement Management
Bookkeeping
Quarterly Closing
Financial Statements
Financial Analysis
Audit Planning
Risk & Internal Control
Evidence Management
Working Papers
Review Notes
Audit Findings
Reports
Notifications
Audit Trail & Compliance
```

---

# 3. User Flow

## 3.1 User Roles

### 3.1.1 Super Admin

Responsible for global system administration.

Main actions:

* Manage all organizations.
* Manage users.
* Manage roles and permissions.
* View audit logs.
* Configure global system settings.
* Monitor system health.

### 3.1.2 Firm Admin

Responsible for managing firm-level users, clients, and configuration.

Main actions:

* Manage company/client records.
* Invite internal users and client users.
* Assign users to companies.
* Configure templates.
* View firm-level dashboard.
* Manage engagement setup.

### 3.1.3 Partner

Responsible for high-level approval and final audit/report sign-off.

Main actions:

* Review engagement status.
* Approve final reports.
* Review high-risk audit findings.
* Approve completion.
* View management dashboard.

### 3.1.4 Audit Manager

Responsible for planning, review, supervision, and approval.

Main actions:

* Create audit engagement.
* Assign audit team.
* Review planning.
* Approve working papers.
* Review findings.
* Approve quarterly closing.
* Review financial reports.

### 3.1.5 Senior Auditor

Responsible for reviewing junior auditor work.

Main actions:

* Review evidence.
* Review working papers.
* Create review notes.
* Resolve audit issues.
* Review audit procedures.
* Escalate findings.

### 3.1.6 Junior Auditor

Responsible for audit fieldwork execution.

Main actions:

* Complete audit procedures.
* Request evidence.
* Prepare working papers.
* Link evidence.
* Draft audit findings.
* Respond to review notes.

### 3.1.7 Accountant

Responsible for bookkeeping and financial reporting.

Main actions:

* Manage chart of accounts.
* Create journal entries.
* Import accounting data.
* Generate trial balance.
* Perform quarterly closing.
* Prepare financial statements.
* Run reconciliation.

### 3.1.8 Financial Analyst

Responsible for financial analysis and management reporting.

Main actions:

* View financial dashboards.
* Run ratio analysis.
* Run trend analysis.
* Run variance analysis.
* Export analysis reports.

### 3.1.9 Client User

Responsible for uploading requested documents and responding to audit/accounting requests.

Main actions:

* View document requests.
* Upload evidence.
* Comment on requests.
* View accepted/rejected files.
* Submit management responses.
* Download final reports if permitted.

---

## 3.2 Global Authentication Flow

### 3.2.1 Login Flow

```txt
User opens login page
User enters email and password
System validates credentials
System checks user status
System checks MFA requirement
If MFA enabled, system requests OTP
User submits OTP
System creates authenticated session
System loads user role and permissions
System redirects user to role-based dashboard
System writes login event to audit_logs
```

### 3.2.2 Failed Login Flow

```txt
User submits invalid credentials
System increments failed attempt counter
System returns generic error message
System logs failed login attempt
If threshold exceeded:
  System rate limits the user/IP
  System may temporarily lock account
  System sends security notification if configured
```

### 3.2.3 Logout Flow

```txt
User clicks logout
System invalidates session
System revokes active session token if needed
System logs logout event
System redirects to login page
```

---

## 3.3 User Invitation Flow

```txt
Admin opens User Management
Admin enters user name and email
Admin selects role
Admin assigns company or engagement access
System creates pending user invitation
System sends invitation email
Invited user opens invitation link
User sets password
User verifies email
System activates user account
System logs user activation event
```

---

## 3.4 Company Setup Flow

```txt
Admin opens Companies module
Admin creates company profile
Admin enters legal information
Admin sets fiscal year start month
Admin sets reporting currency
Admin selects accounting standard
Admin assigns internal users
Admin invites client users if needed
System creates company workspace
System generates default company folders
System logs company creation
```

---

## 3.5 Fiscal Year & Period Setup Flow

```txt
Accountant opens Bookkeeping > Fiscal Years
Accountant creates fiscal year
System generates 12 monthly accounting periods
System generates 4 quarters
System maps months into quarters
Accountant reviews generated periods
Manager approves fiscal year setup
System activates accounting periods
System logs fiscal year creation
```

### 3.5.1 Period Generation Example

```txt
Fiscal Year 2026
Q1: January - March
Q2: April - June
Q3: July - September
Q4: October - December

Monthly Periods:
2026-01
2026-02
2026-03
...
2026-12
```

---

## 3.6 Chart of Accounts Flow

```txt
Accountant opens Chart of Accounts
Accountant chooses manual creation or import
If manual:
  Accountant creates account code, name, type, and classification
If import:
  Accountant uploads Excel/CSV
  System previews data
  Accountant maps columns
  System validates duplicate account codes
  System imports valid accounts
System logs COA creation/import
```

### 3.6.1 COA Validation Flow

```txt
System checks required fields
System checks account code uniqueness per company
System checks valid account type
System checks parent account exists if selected
System rejects invalid rows
System stores failed rows in import error report
```

---

## 3.7 Journal Entry Flow

### 3.7.1 Manual Journal Flow

```txt
Accountant opens Journal Entries
Accountant creates new journal
Accountant selects company and period
Accountant enters journal date
Accountant adds journal lines
System calculates total debit and total credit
System validates balanced journal
Accountant saves as draft or submits for review
Reviewer reviews journal
Reviewer approves or rejects journal
If approved:
  Accountant posts journal
  System updates ledger balances
  System logs journal posting
```

### 3.7.2 Journal Import Flow

```txt
Accountant uploads journal Excel/CSV
System creates import batch
System previews uploaded data
Accountant maps columns
System validates period, accounts, debit, credit
System rejects invalid rows
System creates draft journal entries
Accountant reviews imported journals
Accountant submits for approval
Reviewer approves journals
System posts approved journals
System logs import and posting events
```

### 3.7.3 Journal Reversal Flow

```txt
Accountant opens posted journal
Accountant clicks Reverse
System asks for reversal reason
Accountant confirms reversal
System creates reverse journal entry
System links reversal to original journal
System posts reversal into open period
System logs reversal event
```

---

## 3.8 Quarterly Bookkeeping Flow

### 3.8.1 Full Quarter Closing Flow

```txt
Accountant selects company
Accountant selects fiscal year
Accountant selects quarter
System loads quarterly closing dashboard

Step 1: Data completeness check
  System verifies all monthly periods exist
  System checks journal posting status

Step 2: Trial balance
  Accountant generates quarterly trial balance
  System validates debit and credit balance

Step 3: Reconciliation
  Accountant performs bank reconciliation
  Accountant performs AR reconciliation
  Accountant performs AP reconciliation
  Accountant performs tax account reconciliation if needed

Step 4: Adjustment entries
  Accountant posts accruals
  Accountant posts prepayments
  Accountant posts depreciation
  Accountant posts tax adjustments
  Accountant posts reclassification entries

Step 5: Financial statement generation
  System generates quarterly financial statements
  Accountant reviews statement lines
  Accountant adds notes if needed

Step 6: Management review
  Manager reviews quarterly report
  Manager creates review notes if needed
  Accountant resolves review notes

Step 7: Approval and lock
  Manager approves quarter closing
  System locks quarterly period
  System logs quarter lock event

Step 8: Export
  User exports quarterly financial report
```

### 3.8.2 Quarterly Closing Checklist

```txt
All journal entries posted
All imported data validated
Trial balance balanced
Bank reconciliation completed
AR reconciliation completed
AP reconciliation completed
Tax account reviewed
Accrual entries posted
Prepayment entries posted
Depreciation entries posted
Financial statements generated
Manager review completed
Quarter approved
Quarter locked
```

### 3.8.3 Quarter Unlock Flow

```txt
Authorized user requests quarter unlock
System asks for reason
Manager or Partner reviews unlock request
If approved:
  System unlocks quarter
  System records reason
  System marks reports as outdated
  System logs unlock event
If rejected:
  Quarter remains locked
```

---

## 3.9 Trial Balance Flow

```txt
User opens Trial Balance
User selects company
User selects period or quarter
System fetches posted journal entries
System aggregates debit and credit by account
System calculates opening balance
System calculates movement
System calculates ending balance
System validates total debit equals total credit
System displays trial balance
User exports PDF/Excel if needed
```

---

## 3.10 Financial Statement Flow

```txt
Accountant opens Financial Statement Builder
Accountant selects company and period
System loads trial balance
System checks account mapping
If unmapped accounts exist:
  System shows exception list
  Accountant maps accounts to statement lines
System generates draft statements
Accountant reviews:
  Balance Sheet
  Profit or Loss
  Cash Flow
  Equity Changes
  Notes
Accountant submits report for review
Manager reviews report
Manager approves final version
System locks final report
System stores report version
User exports PDF/Excel
```

---

## 3.11 Financial Analysis Flow

```txt
Analyst opens Analysis Dashboard
Analyst selects company
Analyst selects fiscal year and quarter
System loads financial statement data
System calculates financial ratios
System calculates quarter-over-quarter movement
System calculates year-over-year movement
System identifies negative trends
System displays charts and KPIs
Analyst exports analysis report
```

---

## 3.12 Audit Engagement Flow

```txt
Manager creates engagement
Manager selects company
Manager selects engagement type
Manager selects reporting period
Manager assigns audit team
Manager sets timeline and milestones
Auditor completes planning checklist
Auditor performs risk assessment
Auditor creates audit procedures
Auditor creates document requests
Client uploads evidence
Auditor reviews evidence
Auditor prepares working papers
Senior reviews working papers
Senior creates review notes
Auditor resolves review notes
Manager reviews final documentation
Partner approves final report
System archives completed engagement
```

---

## 3.13 Document Request / PBC Flow

```txt
Auditor opens Document Request module
Auditor creates request title and description
Auditor sets category, priority, and due date
Auditor assigns request to client user
System sends notification to client
Client uploads evidence
System changes status to Submitted
Auditor reviews evidence
If accepted:
  System changes status to Accepted
If rejected:
  Auditor writes rejection reason
  System changes status to Rejected
  Client receives notification
Client uploads revised evidence
System keeps file version history
```

---

## 3.14 Evidence Management Flow

```txt
User uploads evidence
System validates file type and size
System stores file in private storage
System creates evidence record
System assigns evidence version
System links evidence to request or engagement
Reviewer reviews evidence
Reviewer accepts or rejects evidence
System logs review event
Evidence can be linked to working paper, procedure, risk, control, or finding
```

---

## 3.15 Working Paper Flow

```txt
Auditor opens Working Papers
Auditor creates working paper
Auditor selects audit area
Auditor adds objective
Auditor links audit procedure
Auditor links evidence
Auditor writes conclusion
Auditor marks as Prepared
Senior reviews working paper
Senior creates review notes if needed
Auditor resolves review notes
Senior marks as Reviewed
Manager approves working paper
System locks working paper
System logs sign-off events
```

---

## 3.16 Review Note Flow

```txt
Reviewer opens object
Reviewer creates review note
System assigns note to preparer
Preparer receives notification
Preparer responds and updates object
Reviewer reviews response
Reviewer resolves note
System logs note resolution
Object can proceed to next review level
```

Objects that can have review notes:

```txt
Journal Entry
Quarter Closing
Financial Statement
Evidence
Working Paper
Audit Procedure
Audit Finding
Report
```

---

## 3.17 Audit Finding Flow

```txt
Auditor identifies issue
Auditor creates audit finding
Auditor adds root cause, impact, and recommendation
Auditor links evidence and working paper
Manager reviews finding
If high or critical:
  Partner approval required
Client adds management response
Client adds action plan
Manager tracks remediation
Finding is resolved
Manager closes finding
System logs all status changes
```

---

## 3.18 Report Generation Flow

```txt
User selects report type
User selects company and period
System validates access permission
System gathers required data
If report is small:
  System generates immediately
If report is large:
  System dispatches report generation job
Queue worker processes report
System stores generated file privately
System notifies user
User downloads report via signed URL
System logs download event
```

---

# 4. Architecture

## 4.1 Architecture Goals

The architecture must support:

* Secure financial data processing.
* Multi-company workspace.
* Role-based access control.
* Quarterly bookkeeping workflow.
* Audit evidence traceability.
* Background processing.
* Private file management.
* Scalable reporting.
* Strong audit trail.
* Maintainable modular Laravel codebase.

---

## 4.2 High-Level Architecture

```txt
User Browser
  |
  | HTTPS
  v
Nginx Reverse Proxy
  |
  v
Laravel Application / PHP-FPM
  |
  |-- PostgreSQL Database
  |-- Redis Cache / Queue / Session
  |-- S3-Compatible Private Object Storage
  |-- SMTP Email Provider
  |-- Queue Workers
  |-- Scheduler
  |-- Monitoring / Error Tracking
```

---

## 4.3 Application Layer Architecture

```txt
Frontend Layer
  - Inertia.js
  - Vue 3
  - TypeScript
  - Tailwind CSS
  - Form components
  - Data tables
  - Charts
  - File upload UI

HTTP Layer
  - Laravel Routes
  - Controllers
  - Form Requests
  - API Resources
  - Middleware

Authorization Layer
  - Laravel Policies
  - Gates
  - Role Permissions
  - Company Access Scope
  - Engagement Access Scope

Domain Layer
  - Accounting Services
  - Quarter Closing Services
  - Financial Statement Services
  - Audit Services
  - Evidence Services
  - Risk Services
  - Reporting Services

Persistence Layer
  - Eloquent Models
  - Query Builders
  - Database Transactions
  - PostgreSQL Constraints
  - Soft Deletes

Async Layer
  - Laravel Jobs
  - Redis Queue
  - Horizon Monitoring
  - Scheduled Commands

Infrastructure Layer
  - File Storage
  - Email
  - PDF/Excel Export
  - Observability
  - Backup
```

---

## 4.4 Recommended Laravel Module Structure

```txt
app/
  Actions/
    Accounting/
    Audit/
    Company/
    Engagement/
    Evidence/
    Reporting/
    Security/

  Enums/
    Accounting/
    Audit/
    Common/
    Reporting/

  Events/
    Accounting/
    Audit/
    Evidence/
    Reporting/

  Http/
    Controllers/
    Middleware/
    Requests/
    Resources/

  Jobs/
    Imports/
    Reports/
    Notifications/
    Analytics/

  Listeners/
    AuditTrail/
    Notifications/

  Models/

  Policies/

  Services/
    Accounting/
    Audit/
    Company/
    Evidence/
    FinancialStatement/
    Reporting/
    Risk/

  Support/
    Money/
    Period/
    QueryFilters/

  ValueObjects/
    Money.php
    PeriodRange.php
    FiscalQuarter.php

database/
  migrations/
  seeders/
  factories/

resources/
  js/
    Pages/
    Components/
    Layouts/
    Stores/
  views/

routes/
  web.php
  api.php
  console.php
```

---

## 4.5 Domain Boundaries

### 4.5.1 Identity Domain

Responsible for:

* Users.
* Roles.
* Permissions.
* Sessions.
* MFA.
* Invitations.
* Access control.

### 4.5.2 Company Domain

Responsible for:

* Companies.
* Company users.
* Fiscal settings.
* Company documents.
* Client contacts.

### 4.5.3 Accounting Domain

Responsible for:

* Chart of accounts.
* Accounting periods.
* Fiscal years.
* Quarters.
* Journal entries.
* Trial balance.
* Reconciliation.
* Period locks.

### 4.5.4 Financial Statement Domain

Responsible for:

* Statement templates.
* Statement line mapping.
* Statement versions.
* Report approval.
* Notes to financial statements.

### 4.5.5 Analysis Domain

Responsible for:

* Ratio calculations.
* Trend calculations.
* Variance analysis.
* KPI dashboard.

### 4.5.6 Audit Domain

Responsible for:

* Engagements.
* Audit planning.
* Risk assessment.
* Audit procedures.
* Working papers.
* Review notes.
* Audit findings.

### 4.5.7 Evidence Domain

Responsible for:

* Document requests.
* Evidence files.
* Evidence versioning.
* Evidence status.
* Secure file access.

### 4.5.8 Reporting Domain

Responsible for:

* Report templates.
* PDF generation.
* Excel generation.
* Report versioning.
* Download history.

### 4.5.9 Compliance Domain

Responsible for:

* Audit logs.
* Security events.
* Access history.
* Immutable activity records.

---

## 4.6 Request Lifecycle

```txt
Browser sends request
Nginx forwards request to PHP-FPM
Laravel receives request
Middleware checks authentication
Middleware checks tenant/company context
Controller receives request
Form Request validates input
Policy checks permission
Service executes business logic
Database transaction is started if needed
Model changes are persisted
Domain event is dispatched
Audit log listener records event
Notification job is queued if needed
Response is returned to frontend
```

---

## 4.7 Background Job Architecture

### 4.7.1 Queue Use Cases

Use background jobs for:

* Excel/CSV imports.
* Report generation.
* PDF generation.
* Large financial statement exports.
* Notification sending.
* Audit analytics processing.
* Journal entry risk scoring.
* File processing.
* Data reconciliation.
* Weekly digest emails.

### 4.7.2 Queue Names

```txt
default
imports
reports
notifications
analytics
emails
critical
```

### 4.7.3 Queue Worker Strategy

```txt
Queue: critical
  Purpose: urgent workflow actions
  Workers: high priority

Queue: imports
  Purpose: Excel/CSV processing
  Workers: medium priority

Queue: reports
  Purpose: PDF/Excel generation
  Workers: medium priority

Queue: analytics
  Purpose: anomaly detection and risk scoring
  Workers: low to medium priority

Queue: notifications
  Purpose: email and in-app notifications
  Workers: medium priority
```

---

## 4.8 Scheduler Architecture

### 4.8.1 Scheduled Tasks

```txt
Every 5 minutes:
  - Send due-soon document request notifications
  - Process failed notification retry

Hourly:
  - Mark overdue document requests
  - Mark overdue findings
  - Check failed jobs

Daily:
  - Send daily digest
  - Backup database
  - Clean temporary uploads
  - Recalculate dashboard metrics if needed

Weekly:
  - Send weekly engagement progress digest
  - Archive old temporary files
  - Run access review report

Monthly:
  - Generate period status report
  - Generate system usage report
```

---

## 4.9 File Storage Architecture

### 4.9.1 Storage Design

```txt
Private Object Storage
  /companies/{company_id}
    /engagements/{engagement_id}
      /document-requests/{request_id}
      /evidence
      /working-papers
      /reports
    /bookkeeping
      /imports
      /journals
      /reconciliations
    /financial-statements
      /drafts
      /final
```

### 4.9.2 File Access Rules

* Files must be private by default.
* Public access is not allowed.
* Download must use temporary signed URLs.
* File access must be checked through Laravel Policies.
* Every upload and download must be logged.
* Deleted files should be soft-deleted at database level.
* Physical deletion should be delayed based on retention policy.

### 4.9.3 File Metadata

Each file must store:

```txt
company_id
engagement_id
document_request_id
uploaded_by
file_name
original_file_name
file_path
storage_disk
mime_type
file_size
checksum
version
status
reviewed_by
reviewed_at
deleted_at
```

---

## 4.10 Security Architecture

### 4.10.1 Security Principles

* Least privilege access.
* Company-level data isolation.
* Engagement-level authorization.
* Private file storage.
* Immutable audit trail.
* Secure session configuration.
* Strong input validation.
* Background jobs must respect authorization context.
* Sensitive actions must be logged.

### 4.10.2 Authentication

Recommended:

```txt
Laravel Sanctum
Session-based auth for SPA
Optional API token for external integration
MFA for privileged users
```

### 4.10.3 Authorization

Use:

```txt
Laravel Policies
Laravel Gates
Role and permission tables
Company access scope
Engagement access scope
```

Authorization must be enforced at:

```txt
Route middleware
Controller policy check
Service-level guard for critical operations
Database query scope
File access policy
```

### 4.10.4 Data Isolation

Every major table must include at least one of:

```txt
company_id
engagement_id
created_by
```

Rules:

* Client user can only access assigned company.
* Auditor can only access assigned engagement.
* Manager can access engagements under their team.
* Super Admin can access all data.
* Query scopes must enforce company access.

### 4.10.5 Audit Logging

Log these actions:

```txt
login
logout
failed_login
create_company
update_company
create_journal
post_journal
reverse_journal
lock_period
unlock_period
upload_file
download_file
accept_evidence
reject_evidence
create_working_paper
sign_off_working_paper
create_review_note
resolve_review_note
create_finding
approve_report
change_role
change_permission
```

---

## 4.11 Reporting Architecture

### 4.11.1 Report Generation Modes

```txt
Synchronous:
  - Small reports
  - Quick exports
  - Dashboard summary

Asynchronous:
  - Full quarterly report
  - Annual financial report
  - Working paper package
  - Evidence listing
  - Large Excel exports
```

### 4.11.2 Report Lifecycle

```txt
Draft
Generated
Under Review
Approved
Locked
Archived
```

### 4.11.3 Report Storage

Generated report files must be stored in private object storage.

Each report must have:

```txt
report_type
company_id
engagement_id
period_id
version
status
generated_by
approved_by
file_path
checksum
generated_at
approved_at
```

---

## 4.12 Financial Calculation Architecture

### 4.12.1 Money Handling

Use decimal columns for financial values.

Recommended:

```txt
DECIMAL(20, 2) for standard currency
DECIMAL(20, 4) if multi-currency or high precision is needed
```

Avoid floating point types for financial calculations.

### 4.12.2 Calculation Rules

* Journal total debit must equal total credit.
* Posted journals are immutable.
* Reversal must create new journal.
* Trial balance must be generated from posted journal entries.
* Financial statements must be generated from trial balance.
* Approved financial statements must be versioned and locked.
* Changes after approval must create new version.

### 4.12.3 Transaction Safety

Use database transactions for:

```txt
posting journal entries
reversing journal entries
locking accounting period
closing quarter
approving financial statement
accepting evidence
signing off working paper
closing audit finding
```

---

## 4.13 Deployment Architecture

### 4.13.1 Production Services

```txt
Nginx
PHP-FPM
Laravel Application
PostgreSQL
Redis
Queue Worker
Scheduler
Object Storage
SMTP Provider
Monitoring Provider
Backup Storage
```

### 4.13.2 Container Architecture

```txt
app
  - Laravel application
  - PHP-FPM
  - Composer dependencies

web
  - Nginx
  - Static assets
  - Reverse proxy to app

worker
  - Laravel queue worker
  - Runs Horizon

scheduler
  - Laravel scheduler

db
  - PostgreSQL

redis
  - Cache, queue, session

storage
  - S3-compatible external storage
```

### 4.13.3 CI/CD Pipeline

```txt
Push to main branch
Run linting
Run static analysis
Run unit tests
Run feature tests
Build frontend assets
Build Docker image
Push image to registry
Deploy to server
Run migrations
Cache Laravel config
Cache routes
Cache views
Restart PHP-FPM
Restart queue workers
Run health check
Notify deployment result
```

### 4.13.4 Production Optimization Commands

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart
```

---

# 5. Database Schema

## 5.1 Database Design Principles

The database schema must follow these principles:

* Use PostgreSQL as primary database.
* Use UUID or BIGINT consistently.
* Use foreign keys for data integrity.
* Use indexes for high-traffic filters.
* Use soft deletes for critical business records.
* Use decimal type for money values.
* Use status enums carefully.
* Keep audit logs append-only.
* Keep financial records traceable.
* Keep posted accounting records immutable.
* Use pivot tables for many-to-many relationships.
* Use polymorphic references only where they reduce duplication without harming clarity.

Recommended primary key strategy:

```txt
For MVP: BIGINT auto-increment is acceptable.
For SaaS scale: UUID or ULID is recommended.
```

This document uses `BIGINT` style for readability.

---

## 5.2 Entity Relationship Overview

```txt
users
  └── company_users
        └── companies
              ├── fiscal_years
              │     ├── quarters
              │     └── accounting_periods
              ├── chart_of_accounts
              ├── journal_entries
              │     └── journal_entry_lines
              ├── trial_balances
              ├── financial_statement_versions
              ├── engagements
              │     ├── engagement_members
              │     ├── document_requests
              │     │     └── evidence_files
              │     ├── audit_plans
              │     ├── risks
              │     ├── controls
              │     ├── audit_procedures
              │     ├── working_papers
              │     ├── review_notes
              │     └── audit_findings
              └── reports
```

---

## 5.3 Identity & Access Tables

## 5.3.1 users

Stores user accounts.

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    avatar_path VARCHAR(500),
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    email_verified_at TIMESTAMP NULL,
    mfa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

Indexes:

```sql
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_deleted_at ON users(deleted_at);
```

---

## 5.3.2 roles

Stores application roles.

```sql
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    display_name VARCHAR(150) NOT NULL,
    description TEXT,
    is_system BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.3.3 permissions

Stores granular permissions.

```sql
CREATE TABLE permissions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    module VARCHAR(100) NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Example permission names:

```txt
company.view
company.create
company.update
journal.create
journal.post
quarter.close
quarter.unlock
evidence.upload
evidence.review
working_paper.signoff
report.approve
audit_log.view
```

---

## 5.3.4 role_permissions

Pivot table between roles and permissions.

```sql
CREATE TABLE role_permissions (
    id BIGSERIAL PRIMARY KEY,
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permission_id BIGINT NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(role_id, permission_id)
);
```

---

## 5.3.5 user_roles

Pivot table between users and roles.

```sql
CREATE TABLE user_roles (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, role_id)
);
```

---

## 5.3.6 user_invitations

Stores user invitation tokens.

```sql
CREATE TABLE user_invitations (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(180) NOT NULL,
    role_id BIGINT REFERENCES roles(id),
    company_id BIGINT NULL,
    invited_by BIGINT REFERENCES users(id),
    token VARCHAR(255) NOT NULL UNIQUE,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    expires_at TIMESTAMP NOT NULL,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_user_invitations_email ON user_invitations(email);
CREATE INDEX idx_user_invitations_status ON user_invitations(status);
```

---

## 5.4 Company & Client Tables

## 5.4.1 companies

Stores company/client master data.

```sql
CREATE TABLE companies (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    legal_name VARCHAR(220),
    tax_id VARCHAR(100),
    registration_number VARCHAR(100),
    industry VARCHAR(120),
    address TEXT,
    currency CHAR(3) NOT NULL DEFAULT 'IDR',
    fiscal_year_start_month SMALLINT NOT NULL DEFAULT 1,
    accounting_standard VARCHAR(80) NOT NULL DEFAULT 'SAK/IFRS',
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

Indexes:

```sql
CREATE INDEX idx_companies_status ON companies(status);
CREATE INDEX idx_companies_tax_id ON companies(tax_id);
CREATE INDEX idx_companies_deleted_at ON companies(deleted_at);
```

---

## 5.4.2 company_users

Maps users to companies.

```sql
CREATE TABLE company_users (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    access_level VARCHAR(50) NOT NULL DEFAULT 'member',
    is_client_user BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(company_id, user_id)
);
```

Indexes:

```sql
CREATE INDEX idx_company_users_company_id ON company_users(company_id);
CREATE INDEX idx_company_users_user_id ON company_users(user_id);
```

---

## 5.4.3 company_contacts

Stores client contact persons.

```sql
CREATE TABLE company_contacts (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(180),
    phone VARCHAR(50),
    position VARCHAR(120),
    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.5 Fiscal Year, Period & Quarter Tables

## 5.5.1 fiscal_years

Stores fiscal year information.

```sql
CREATE TABLE fiscal_years (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    year SMALLINT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    created_by BIGINT REFERENCES users(id),
    approved_by BIGINT REFERENCES users(id),
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(company_id, year)
);
```

Indexes:

```sql
CREATE INDEX idx_fiscal_years_company_id ON fiscal_years(company_id);
CREATE INDEX idx_fiscal_years_status ON fiscal_years(status);
```

---

## 5.5.2 quarters

Stores company quarter periods.

```sql
CREATE TABLE quarters (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    fiscal_year_id BIGINT NOT NULL REFERENCES fiscal_years(id) ON DELETE CASCADE,
    quarter_code VARCHAR(10) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    is_locked BOOLEAN NOT NULL DEFAULT FALSE,
    locked_at TIMESTAMP NULL,
    locked_by BIGINT REFERENCES users(id),
    closed_at TIMESTAMP NULL,
    closed_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(company_id, fiscal_year_id, quarter_code)
);
```

Indexes:

```sql
CREATE INDEX idx_quarters_company_id ON quarters(company_id);
CREATE INDEX idx_quarters_fiscal_year_id ON quarters(fiscal_year_id);
CREATE INDEX idx_quarters_status ON quarters(status);
```

---

## 5.5.3 accounting_periods

Stores monthly, quarterly, and annual periods.

```sql
CREATE TABLE accounting_periods (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    fiscal_year_id BIGINT NOT NULL REFERENCES fiscal_years(id) ON DELETE CASCADE,
    quarter_id BIGINT REFERENCES quarters(id) ON DELETE SET NULL,
    period_name VARCHAR(50) NOT NULL,
    period_type VARCHAR(30) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_locked BOOLEAN NOT NULL DEFAULT FALSE,
    locked_at TIMESTAMP NULL,
    locked_by BIGINT REFERENCES users(id),
    unlock_reason TEXT,
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(company_id, period_name, period_type)
);
```

Indexes:

```sql
CREATE INDEX idx_accounting_periods_company_id ON accounting_periods(company_id);
CREATE INDEX idx_accounting_periods_fiscal_year_id ON accounting_periods(fiscal_year_id);
CREATE INDEX idx_accounting_periods_quarter_id ON accounting_periods(quarter_id);
CREATE INDEX idx_accounting_periods_status ON accounting_periods(status);
CREATE INDEX idx_accounting_periods_is_locked ON accounting_periods(is_locked);
```

---

## 5.5.4 quarter_closing_checklists

Stores quarter closing checklist status.

```sql
CREATE TABLE quarter_closing_checklists (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    quarter_id BIGINT NOT NULL REFERENCES quarters(id) ON DELETE CASCADE,
    checklist_key VARCHAR(120) NOT NULL,
    checklist_label VARCHAR(255) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    completed_by BIGINT REFERENCES users(id),
    completed_at TIMESTAMP NULL,
    reviewer_id BIGINT REFERENCES users(id),
    reviewed_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(quarter_id, checklist_key)
);
```

---

## 5.6 Accounting Tables

## 5.6.1 chart_of_accounts

Stores account structure.

```sql
CREATE TABLE chart_of_accounts (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    parent_id BIGINT REFERENCES chart_of_accounts(id) ON DELETE SET NULL,
    account_code VARCHAR(80) NOT NULL,
    account_name VARCHAR(180) NOT NULL,
    account_type VARCHAR(80) NOT NULL,
    account_classification VARCHAR(120),
    financial_statement_line VARCHAR(150),
    cash_flow_category VARCHAR(80),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(company_id, account_code)
);
```

Indexes:

```sql
CREATE INDEX idx_chart_accounts_company_id ON chart_of_accounts(company_id);
CREATE INDEX idx_chart_accounts_parent_id ON chart_of_accounts(parent_id);
CREATE INDEX idx_chart_accounts_type ON chart_of_accounts(account_type);
CREATE INDEX idx_chart_accounts_active ON chart_of_accounts(is_active);
```

---

## 5.6.2 journal_entries

Stores journal header.

```sql
CREATE TABLE journal_entries (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    accounting_period_id BIGINT NOT NULL REFERENCES accounting_periods(id),
    journal_number VARCHAR(100) NOT NULL,
    journal_date DATE NOT NULL,
    description TEXT NOT NULL,
    reference_number VARCHAR(150),
    status VARCHAR(30) NOT NULL DEFAULT 'draft',
    total_debit DECIMAL(20,2) NOT NULL DEFAULT 0,
    total_credit DECIMAL(20,2) NOT NULL DEFAULT 0,
    prepared_by BIGINT REFERENCES users(id),
    reviewed_by BIGINT REFERENCES users(id),
    approved_by BIGINT REFERENCES users(id),
    posted_at TIMESTAMP NULL,
    reversed_from_id BIGINT REFERENCES journal_entries(id),
    source_type VARCHAR(50) DEFAULT 'manual',
    import_batch_id BIGINT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(company_id, journal_number)
);
```

Indexes:

```sql
CREATE INDEX idx_journal_entries_company_id ON journal_entries(company_id);
CREATE INDEX idx_journal_entries_period_id ON journal_entries(accounting_period_id);
CREATE INDEX idx_journal_entries_status ON journal_entries(status);
CREATE INDEX idx_journal_entries_journal_date ON journal_entries(journal_date);
CREATE INDEX idx_journal_entries_posted_at ON journal_entries(posted_at);
```

---

## 5.6.3 journal_entry_lines

Stores journal detail lines.

```sql
CREATE TABLE journal_entry_lines (
    id BIGSERIAL PRIMARY KEY,
    journal_entry_id BIGINT NOT NULL REFERENCES journal_entries(id) ON DELETE CASCADE,
    account_id BIGINT NOT NULL REFERENCES chart_of_accounts(id),
    description TEXT,
    debit DECIMAL(20,2) NOT NULL DEFAULT 0,
    credit DECIMAL(20,2) NOT NULL DEFAULT 0,
    line_order INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (debit >= 0),
    CHECK (credit >= 0),
    CHECK (NOT (debit > 0 AND credit > 0))
);
```

Indexes:

```sql
CREATE INDEX idx_journal_lines_journal_id ON journal_entry_lines(journal_entry_id);
CREATE INDEX idx_journal_lines_account_id ON journal_entry_lines(account_id);
```

---

## 5.6.4 import_batches

Stores uploaded import batches.

```sql
CREATE TABLE import_batches (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    import_type VARCHAR(80) NOT NULL,
    original_file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    total_rows INTEGER NOT NULL DEFAULT 0,
    success_rows INTEGER NOT NULL DEFAULT 0,
    failed_rows INTEGER NOT NULL DEFAULT 0,
    error_report_path VARCHAR(500),
    uploaded_by BIGINT REFERENCES users(id),
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_import_batches_company_id ON import_batches(company_id);
CREATE INDEX idx_import_batches_type ON import_batches(import_type);
CREATE INDEX idx_import_batches_status ON import_batches(status);
```

---

## 5.6.5 trial_balances

Stores generated trial balance snapshots.

```sql
CREATE TABLE trial_balances (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    accounting_period_id BIGINT REFERENCES accounting_periods(id),
    quarter_id BIGINT REFERENCES quarters(id),
    account_id BIGINT NOT NULL REFERENCES chart_of_accounts(id),
    opening_debit DECIMAL(20,2) NOT NULL DEFAULT 0,
    opening_credit DECIMAL(20,2) NOT NULL DEFAULT 0,
    movement_debit DECIMAL(20,2) NOT NULL DEFAULT 0,
    movement_credit DECIMAL(20,2) NOT NULL DEFAULT 0,
    ending_debit DECIMAL(20,2) NOT NULL DEFAULT 0,
    ending_credit DECIMAL(20,2) NOT NULL DEFAULT 0,
    generated_by BIGINT REFERENCES users(id),
    generated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_trial_balances_company_id ON trial_balances(company_id);
CREATE INDEX idx_trial_balances_period_id ON trial_balances(accounting_period_id);
CREATE INDEX idx_trial_balances_quarter_id ON trial_balances(quarter_id);
CREATE INDEX idx_trial_balances_account_id ON trial_balances(account_id);
```

---

## 5.7 Reconciliation Tables

## 5.7.1 reconciliations

Stores reconciliation header.

```sql
CREATE TABLE reconciliations (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    accounting_period_id BIGINT REFERENCES accounting_periods(id),
    quarter_id BIGINT REFERENCES quarters(id),
    reconciliation_type VARCHAR(80) NOT NULL,
    account_id BIGINT REFERENCES chart_of_accounts(id),
    status VARCHAR(30) NOT NULL DEFAULT 'draft',
    prepared_by BIGINT REFERENCES users(id),
    reviewed_by BIGINT REFERENCES users(id),
    approved_by BIGINT REFERENCES users(id),
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.7.2 reconciliation_items

Stores reconciliation items.

```sql
CREATE TABLE reconciliation_items (
    id BIGSERIAL PRIMARY KEY,
    reconciliation_id BIGINT NOT NULL REFERENCES reconciliations(id) ON DELETE CASCADE,
    source_type VARCHAR(50) NOT NULL,
    source_reference VARCHAR(150),
    transaction_date DATE,
    description TEXT,
    amount DECIMAL(20,2) NOT NULL DEFAULT 0,
    match_status VARCHAR(30) NOT NULL DEFAULT 'unmatched',
    matched_with_id BIGINT REFERENCES reconciliation_items(id),
    matched_by BIGINT REFERENCES users(id),
    matched_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_reconciliation_items_reconciliation_id ON reconciliation_items(reconciliation_id);
CREATE INDEX idx_reconciliation_items_match_status ON reconciliation_items(match_status);
CREATE INDEX idx_reconciliation_items_transaction_date ON reconciliation_items(transaction_date);
```

---

## 5.8 Financial Statement Tables

## 5.8.1 financial_statement_templates

Stores statement templates.

```sql
CREATE TABLE financial_statement_templates (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT REFERENCES companies(id) ON DELETE CASCADE,
    template_name VARCHAR(180) NOT NULL,
    statement_type VARCHAR(80) NOT NULL,
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.8.2 financial_statement_lines

Stores statement line definitions.

```sql
CREATE TABLE financial_statement_lines (
    id BIGSERIAL PRIMARY KEY,
    template_id BIGINT NOT NULL REFERENCES financial_statement_templates(id) ON DELETE CASCADE,
    parent_id BIGINT REFERENCES financial_statement_lines(id) ON DELETE SET NULL,
    line_code VARCHAR(80) NOT NULL,
    line_name VARCHAR(180) NOT NULL,
    line_type VARCHAR(80) NOT NULL,
    display_order INTEGER NOT NULL DEFAULT 1,
    formula TEXT,
    is_subtotal BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.8.3 account_statement_mappings

Maps chart of accounts to financial statement lines.

```sql
CREATE TABLE account_statement_mappings (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    account_id BIGINT NOT NULL REFERENCES chart_of_accounts(id) ON DELETE CASCADE,
    statement_line_id BIGINT NOT NULL REFERENCES financial_statement_lines(id) ON DELETE CASCADE,
    mapping_type VARCHAR(80) NOT NULL DEFAULT 'default',
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(company_id, account_id, statement_line_id)
);
```

---

## 5.8.4 financial_statement_versions

Stores generated statement versions.

```sql
CREATE TABLE financial_statement_versions (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    accounting_period_id BIGINT REFERENCES accounting_periods(id),
    quarter_id BIGINT REFERENCES quarters(id),
    fiscal_year_id BIGINT REFERENCES fiscal_years(id),
    version_number INTEGER NOT NULL DEFAULT 1,
    status VARCHAR(30) NOT NULL DEFAULT 'draft',
    generated_by BIGINT REFERENCES users(id),
    reviewed_by BIGINT REFERENCES users(id),
    approved_by BIGINT REFERENCES users(id),
    approved_at TIMESTAMP NULL,
    locked_at TIMESTAMP NULL,
    file_path VARCHAR(500),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_fs_versions_company_id ON financial_statement_versions(company_id);
CREATE INDEX idx_fs_versions_period_id ON financial_statement_versions(accounting_period_id);
CREATE INDEX idx_fs_versions_quarter_id ON financial_statement_versions(quarter_id);
CREATE INDEX idx_fs_versions_status ON financial_statement_versions(status);
```

---

## 5.8.5 financial_statement_values

Stores calculated statement values per line.

```sql
CREATE TABLE financial_statement_values (
    id BIGSERIAL PRIMARY KEY,
    financial_statement_version_id BIGINT NOT NULL REFERENCES financial_statement_versions(id) ON DELETE CASCADE,
    statement_line_id BIGINT NOT NULL REFERENCES financial_statement_lines(id),
    current_amount DECIMAL(20,2) NOT NULL DEFAULT 0,
    comparative_amount DECIMAL(20,2) NOT NULL DEFAULT 0,
    variance_amount DECIMAL(20,2) NOT NULL DEFAULT 0,
    variance_percent DECIMAL(10,4) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.9 Engagement & Audit Tables

## 5.9.1 engagements

Stores engagements.

```sql
CREATE TABLE engagements (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    engagement_code VARCHAR(100) NOT NULL,
    engagement_name VARCHAR(180) NOT NULL,
    engagement_type VARCHAR(80) NOT NULL,
    fiscal_year_id BIGINT REFERENCES fiscal_years(id),
    accounting_period_id BIGINT REFERENCES accounting_periods(id),
    quarter_id BIGINT REFERENCES quarters(id),
    status VARCHAR(30) NOT NULL DEFAULT 'draft',
    start_date DATE,
    end_date DATE,
    budgeted_hours DECIMAL(10,2),
    manager_id BIGINT REFERENCES users(id),
    partner_id BIGINT REFERENCES users(id),
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(company_id, engagement_code)
);
```

Indexes:

```sql
CREATE INDEX idx_engagements_company_id ON engagements(company_id);
CREATE INDEX idx_engagements_status ON engagements(status);
CREATE INDEX idx_engagements_type ON engagements(engagement_type);
```

---

## 5.9.2 engagement_members

Stores engagement team members.

```sql
CREATE TABLE engagement_members (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT NOT NULL REFERENCES engagements(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    engagement_role VARCHAR(80) NOT NULL,
    assigned_by BIGINT REFERENCES users(id),
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(engagement_id, user_id)
);
```

---

## 5.9.3 audit_plans

Stores audit planning data.

```sql
CREATE TABLE audit_plans (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT NOT NULL REFERENCES engagements(id) ON DELETE CASCADE,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    understanding_entity TEXT,
    audit_scope TEXT,
    planning_materiality DECIMAL(20,2),
    performance_materiality DECIMAL(20,2),
    trivial_threshold DECIMAL(20,2),
    preliminary_analytics_summary TEXT,
    audit_strategy TEXT,
    status VARCHAR(30) NOT NULL DEFAULT 'draft',
    prepared_by BIGINT REFERENCES users(id),
    reviewed_by BIGINT REFERENCES users(id),
    approved_by BIGINT REFERENCES users(id),
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.9.4 risks

Stores risk register.

```sql
CREATE TABLE risks (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT NOT NULL REFERENCES engagements(id) ON DELETE CASCADE,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    title VARCHAR(220) NOT NULL,
    description TEXT,
    risk_category VARCHAR(100) NOT NULL,
    likelihood SMALLINT NOT NULL DEFAULT 1,
    impact SMALLINT NOT NULL DEFAULT 1,
    inherent_risk_score SMALLINT NOT NULL DEFAULT 1,
    control_risk_score SMALLINT,
    residual_risk_score SMALLINT,
    is_significant BOOLEAN NOT NULL DEFAULT FALSE,
    response TEXT,
    owner_id BIGINT REFERENCES users(id),
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_risks_engagement_id ON risks(engagement_id);
CREATE INDEX idx_risks_category ON risks(risk_category);
CREATE INDEX idx_risks_significant ON risks(is_significant);
```

---

## 5.9.5 controls

Stores internal controls.

```sql
CREATE TABLE controls (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT REFERENCES engagements(id) ON DELETE CASCADE,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    risk_id BIGINT REFERENCES risks(id) ON DELETE SET NULL,
    control_code VARCHAR(100),
    control_name VARCHAR(220) NOT NULL,
    description TEXT,
    control_type VARCHAR(50) NOT NULL,
    control_nature VARCHAR(80) NOT NULL,
    frequency VARCHAR(80),
    owner_id BIGINT REFERENCES users(id),
    design_effectiveness VARCHAR(50),
    operating_effectiveness VARCHAR(50),
    status VARCHAR(30) NOT NULL DEFAULT 'active',
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.9.6 audit_procedures

Stores audit procedures.

```sql
CREATE TABLE audit_procedures (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT NOT NULL REFERENCES engagements(id) ON DELETE CASCADE,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    risk_id BIGINT REFERENCES risks(id) ON DELETE SET NULL,
    procedure_code VARCHAR(100),
    title VARCHAR(220) NOT NULL,
    procedure_type VARCHAR(80) NOT NULL,
    description TEXT,
    assigned_to BIGINT REFERENCES users(id),
    status VARCHAR(30) NOT NULL DEFAULT 'not_started',
    result TEXT,
    conclusion TEXT,
    prepared_by BIGINT REFERENCES users(id),
    reviewed_by BIGINT REFERENCES users(id),
    completed_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.9.7 working_papers

Stores audit working papers.

```sql
CREATE TABLE working_papers (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT NOT NULL REFERENCES engagements(id) ON DELETE CASCADE,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    audit_procedure_id BIGINT REFERENCES audit_procedures(id) ON DELETE SET NULL,
    index_code VARCHAR(100) NOT NULL,
    title VARCHAR(220) NOT NULL,
    audit_area VARCHAR(120),
    objective TEXT,
    procedure_summary TEXT,
    conclusion TEXT,
    status VARCHAR(30) NOT NULL DEFAULT 'not_started',
    prepared_by BIGINT REFERENCES users(id),
    reviewed_by BIGINT REFERENCES users(id),
    approved_by BIGINT REFERENCES users(id),
    prepared_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    locked_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(engagement_id, index_code)
);
```

Indexes:

```sql
CREATE INDEX idx_working_papers_engagement_id ON working_papers(engagement_id);
CREATE INDEX idx_working_papers_status ON working_papers(status);
CREATE INDEX idx_working_papers_audit_area ON working_papers(audit_area);
```

---

## 5.9.8 working_paper_references

Stores cross references between working papers.

```sql
CREATE TABLE working_paper_references (
    id BIGSERIAL PRIMARY KEY,
    working_paper_id BIGINT NOT NULL REFERENCES working_papers(id) ON DELETE CASCADE,
    referenced_working_paper_id BIGINT NOT NULL REFERENCES working_papers(id) ON DELETE CASCADE,
    reference_note TEXT,
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(working_paper_id, referenced_working_paper_id)
);
```

---

## 5.10 Document Request & Evidence Tables

## 5.10.1 document_requests

Stores PBC/document requests.

```sql
CREATE TABLE document_requests (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT REFERENCES engagements(id) ON DELETE CASCADE,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    title VARCHAR(220) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    priority VARCHAR(30) NOT NULL DEFAULT 'medium',
    due_date DATE,
    assigned_to BIGINT REFERENCES users(id),
    status VARCHAR(30) NOT NULL DEFAULT 'draft',
    created_by BIGINT REFERENCES users(id),
    reviewed_by BIGINT REFERENCES users(id),
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_document_requests_company_id ON document_requests(company_id);
CREATE INDEX idx_document_requests_engagement_id ON document_requests(engagement_id);
CREATE INDEX idx_document_requests_assigned_to ON document_requests(assigned_to);
CREATE INDEX idx_document_requests_status ON document_requests(status);
CREATE INDEX idx_document_requests_due_date ON document_requests(due_date);
```

---

## 5.10.2 evidence_files

Stores evidence metadata.

```sql
CREATE TABLE evidence_files (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT REFERENCES engagements(id) ON DELETE CASCADE,
    document_request_id BIGINT REFERENCES document_requests(id) ON DELETE SET NULL,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    uploaded_by BIGINT REFERENCES users(id),
    reviewed_by BIGINT REFERENCES users(id),
    original_file_name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    storage_disk VARCHAR(80) NOT NULL DEFAULT 's3',
    mime_type VARCHAR(120),
    file_size BIGINT,
    checksum VARCHAR(128),
    version INTEGER NOT NULL DEFAULT 1,
    status VARCHAR(30) NOT NULL DEFAULT 'submitted',
    rejected_reason TEXT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

Indexes:

```sql
CREATE INDEX idx_evidence_company_id ON evidence_files(company_id);
CREATE INDEX idx_evidence_engagement_id ON evidence_files(engagement_id);
CREATE INDEX idx_evidence_request_id ON evidence_files(document_request_id);
CREATE INDEX idx_evidence_status ON evidence_files(status);
CREATE INDEX idx_evidence_checksum ON evidence_files(checksum);
```

---

## 5.10.3 evidence_links

Polymorphic links from evidence to other objects.

```sql
CREATE TABLE evidence_links (
    id BIGSERIAL PRIMARY KEY,
    evidence_file_id BIGINT NOT NULL REFERENCES evidence_files(id) ON DELETE CASCADE,
    linkable_type VARCHAR(120) NOT NULL,
    linkable_id BIGINT NOT NULL,
    created_by BIGINT REFERENCES users(id),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_evidence_links_file_id ON evidence_links(evidence_file_id);
CREATE INDEX idx_evidence_links_linkable ON evidence_links(linkable_type, linkable_id);
```

---

## 5.11 Review Notes & Findings Tables

## 5.11.1 review_notes

Stores review notes on different objects.

```sql
CREATE TABLE review_notes (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT REFERENCES engagements(id) ON DELETE CASCADE,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    noteable_type VARCHAR(120) NOT NULL,
    noteable_id BIGINT NOT NULL,
    note TEXT NOT NULL,
    assigned_to BIGINT REFERENCES users(id),
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    created_by BIGINT REFERENCES users(id),
    resolved_by BIGINT REFERENCES users(id),
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_review_notes_company_id ON review_notes(company_id);
CREATE INDEX idx_review_notes_engagement_id ON review_notes(engagement_id);
CREATE INDEX idx_review_notes_noteable ON review_notes(noteable_type, noteable_id);
CREATE INDEX idx_review_notes_status ON review_notes(status);
CREATE INDEX idx_review_notes_assigned_to ON review_notes(assigned_to);
```

---

## 5.11.2 review_note_replies

Stores replies to review notes.

```sql
CREATE TABLE review_note_replies (
    id BIGSERIAL PRIMARY KEY,
    review_note_id BIGINT NOT NULL REFERENCES review_notes(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id),
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.11.3 audit_findings

Stores audit findings.

```sql
CREATE TABLE audit_findings (
    id BIGSERIAL PRIMARY KEY,
    engagement_id BIGINT REFERENCES engagements(id) ON DELETE CASCADE,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    title VARCHAR(220) NOT NULL,
    category VARCHAR(100),
    severity VARCHAR(30) NOT NULL DEFAULT 'medium',
    description TEXT,
    root_cause TEXT,
    impact TEXT,
    recommendation TEXT,
    management_response TEXT,
    action_plan TEXT,
    responsible_person VARCHAR(180),
    due_date DATE,
    status VARCHAR(30) NOT NULL DEFAULT 'draft',
    repeat_finding BOOLEAN NOT NULL DEFAULT FALSE,
    created_by BIGINT REFERENCES users(id),
    approved_by BIGINT REFERENCES users(id),
    approved_at TIMESTAMP NULL,
    closed_by BIGINT REFERENCES users(id),
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_audit_findings_company_id ON audit_findings(company_id);
CREATE INDEX idx_audit_findings_engagement_id ON audit_findings(engagement_id);
CREATE INDEX idx_audit_findings_status ON audit_findings(status);
CREATE INDEX idx_audit_findings_severity ON audit_findings(severity);
CREATE INDEX idx_audit_findings_due_date ON audit_findings(due_date);
```

---

## 5.12 Reporting Tables

## 5.12.1 reports

Stores generated reports.

```sql
CREATE TABLE reports (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    engagement_id BIGINT REFERENCES engagements(id) ON DELETE CASCADE,
    accounting_period_id BIGINT REFERENCES accounting_periods(id),
    quarter_id BIGINT REFERENCES quarters(id),
    report_type VARCHAR(100) NOT NULL,
    report_title VARCHAR(220) NOT NULL,
    version_number INTEGER NOT NULL DEFAULT 1,
    status VARCHAR(30) NOT NULL DEFAULT 'generated',
    file_path VARCHAR(500),
    file_type VARCHAR(30),
    file_size BIGINT,
    checksum VARCHAR(128),
    generated_by BIGINT REFERENCES users(id),
    approved_by BIGINT REFERENCES users(id),
    generated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    locked_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_reports_company_id ON reports(company_id);
CREATE INDEX idx_reports_engagement_id ON reports(engagement_id);
CREATE INDEX idx_reports_type ON reports(report_type);
CREATE INDEX idx_reports_status ON reports(status);
```

---

## 5.12.2 report_downloads

Stores report download history.

```sql
CREATE TABLE report_downloads (
    id BIGSERIAL PRIMARY KEY,
    report_id BIGINT NOT NULL REFERENCES reports(id) ON DELETE CASCADE,
    downloaded_by BIGINT REFERENCES users(id),
    ip_address VARCHAR(80),
    user_agent TEXT,
    downloaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.13 Notification Tables

## 5.13.1 notifications

Stores in-app notifications.

```sql
CREATE TABLE notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    company_id BIGINT REFERENCES companies(id) ON DELETE CASCADE,
    title VARCHAR(220) NOT NULL,
    message TEXT NOT NULL,
    notification_type VARCHAR(80) NOT NULL,
    data JSONB,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_read_at ON notifications(read_at);
CREATE INDEX idx_notifications_type ON notifications(notification_type);
```

---

## 5.14 Audit Trail Tables

## 5.14.1 audit_logs

Stores immutable audit events.

```sql
CREATE TABLE audit_logs (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    company_id BIGINT REFERENCES companies(id),
    engagement_id BIGINT REFERENCES engagements(id),
    action VARCHAR(120) NOT NULL,
    object_type VARCHAR(120),
    object_id BIGINT,
    before_value JSONB,
    after_value JSONB,
    ip_address VARCHAR(80),
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_company_id ON audit_logs(company_id);
CREATE INDEX idx_audit_logs_engagement_id ON audit_logs(engagement_id);
CREATE INDEX idx_audit_logs_action ON audit_logs(action);
CREATE INDEX idx_audit_logs_object ON audit_logs(object_type, object_id);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);
```

Rules:

```txt
audit_logs must be append-only.
Normal users cannot update audit_logs.
Normal users cannot delete audit_logs.
Only system-level retention job can archive old audit logs.
```

---

## 5.15 Analytics Tables

## 5.15.1 analytics_runs

Stores analytics execution history.

```sql
CREATE TABLE analytics_runs (
    id BIGSERIAL PRIMARY KEY,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    engagement_id BIGINT REFERENCES engagements(id) ON DELETE CASCADE,
    accounting_period_id BIGINT REFERENCES accounting_periods(id),
    quarter_id BIGINT REFERENCES quarters(id),
    analytics_type VARCHAR(100) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    started_by BIGINT REFERENCES users(id),
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 5.15.2 analytics_exceptions

Stores detected anomalies and exceptions.

```sql
CREATE TABLE analytics_exceptions (
    id BIGSERIAL PRIMARY KEY,
    analytics_run_id BIGINT NOT NULL REFERENCES analytics_runs(id) ON DELETE CASCADE,
    company_id BIGINT NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    exception_type VARCHAR(100) NOT NULL,
    severity VARCHAR(30) NOT NULL DEFAULT 'medium',
    source_type VARCHAR(120),
    source_id BIGINT,
    title VARCHAR(220) NOT NULL,
    description TEXT,
    risk_score DECIMAL(10,4),
    status VARCHAR(30) NOT NULL DEFAULT 'open',
    assigned_to BIGINT REFERENCES users(id),
    resolved_by BIGINT REFERENCES users(id),
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

Indexes:

```sql
CREATE INDEX idx_analytics_exceptions_company_id ON analytics_exceptions(company_id);
CREATE INDEX idx_analytics_exceptions_run_id ON analytics_exceptions(analytics_run_id);
CREATE INDEX idx_analytics_exceptions_type ON analytics_exceptions(exception_type);
CREATE INDEX idx_analytics_exceptions_status ON analytics_exceptions(status);
```

---

# 6. Critical Business Rules

## 6.1 Accounting Rules

```txt
A journal entry must have at least two lines.
Total debit must equal total credit.
Journal date must be inside an open accounting period.
Posted journal entries cannot be edited.
Reversal must create a new linked journal entry.
Locked periods cannot accept new journals.
Trial balance must only use posted journals.
Financial statements must be generated from trial balance.
```

---

## 6.2 Quarterly Closing Rules

```txt
Quarter can only be closed when all checklist items are completed.
Quarter can only be locked by authorized manager or partner.
Quarter unlock requires reason and approval.
Unlocking a quarter marks related reports as outdated.
Every lock and unlock action must be logged.
```

---

## 6.3 Audit Workflow Rules

```txt
Engagement must have assigned team members.
Audit planning must be approved before fieldwork completion.
Evidence must be reviewed before working paper approval.
Working paper must resolve all review notes before sign-off.
High and critical findings require manager approval.
Final report requires partner approval.
```

---

## 6.4 Evidence Rules

```txt
Evidence must be stored privately.
Evidence upload must be logged.
Evidence download must be logged.
Rejected evidence requires rejection reason.
Accepted evidence cannot be deleted by client.
New upload after rejection creates a new version.
```

---

# 7. Indexing Strategy

## 7.1 General Indexing

Add indexes to:

```txt
company_id
engagement_id
user_id
status
created_at
due_date
period_id
quarter_id
```

## 7.2 Composite Indexes

Recommended composite indexes:

```sql
CREATE INDEX idx_journals_company_period_status
ON journal_entries(company_id, accounting_period_id, status);

CREATE INDEX idx_document_requests_company_status_due
ON document_requests(company_id, status, due_date);

CREATE INDEX idx_evidence_company_engagement_status
ON evidence_files(company_id, engagement_id, status);

CREATE INDEX idx_working_papers_engagement_status
ON working_papers(engagement_id, status);

CREATE INDEX idx_findings_engagement_status_severity
ON audit_findings(engagement_id, status, severity);

CREATE INDEX idx_audit_logs_company_action_created
ON audit_logs(company_id, action, created_at);
```

---

# 8. Backup & Recovery

## 8.1 Backup Requirements

```txt
Daily PostgreSQL backup
Daily object storage metadata backup
Weekly full backup
Monthly archive backup
Point-in-time recovery if possible
Backup encryption
Backup restore testing
```

## 8.2 Retention Policy

```txt
Daily backups: 14 days
Weekly backups: 8 weeks
Monthly backups: 12 months
Audit logs: minimum 7 years if required by policy
Evidence files: based on engagement retention policy
```

---

# 9. Observability

## 9.1 Application Monitoring

Track:

```txt
HTTP error rate
Average response time
Slow queries
Failed jobs
Queue length
Report generation failures
Login failures
File upload failures
Storage usage
Database size
```

## 9.2 Health Check Endpoint

Recommended endpoint:

```txt
GET /health
```

Response:

```json
{
  "status": "ok",
  "database": "ok",
  "redis": "ok",
  "queue": "ok",
  "storage": "ok",
  "timestamp": "2026-01-01T00:00:00Z"
}
```

---

# 10. Environment Configuration

```env
APP_NAME=LedgerScope
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ledgerscope.example.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=ledgerscope
DB_USERNAME=ledgerscope_user
DB_PASSWORD=change_me

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=ledgerscope-private
AWS_USE_PATH_STYLE_ENDPOINT=false

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@ledgerscope.example.com
MAIL_FROM_NAME=LedgerScope

LOG_CHANNEL=stack
LOG_LEVEL=warning
```

---

# 11. Final Infrastructure Recommendation

For MVP, use:

```txt
Laravel 13
PHP 8.4
Inertia.js
Vue 3
TypeScript
Tailwind CSS
PostgreSQL
Redis
S3-compatible private storage
Laravel Horizon
GitHub Actions
Docker
Nginx
PHP-FPM
```

For production readiness, prioritize:

```txt
Role-based access control
Company-level data isolation
Private evidence storage
Immutable audit trail
Queue-based report generation
Quarterly closing lock system
Financial report versioning
Database constraints and indexes
Automated backup
Monitoring and error tracking
Security hardening based on OWASP ASVS
```

The system must ensure every financial number, document, approval, journal entry, report, and audit conclusion is traceable from source data to final output.
