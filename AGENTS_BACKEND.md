@C:\Users\HP OMEN\.codex\RTK.md

# AGENTS.md — LedgerScope Backend Architecture

> **Scope:** Backend only. Frontend (Inertia.js + Vue 3) is out of scope for this agent run.
> **Goal:** Build a clean, modular, production-ready Laravel 13 backend for LedgerScope.

---

## Mandatory Agent Workflow

These workflow rules apply before all project-specific backend rules below:

1. **Always run shell commands through RTK.** Prefix commands with `rtk` to reduce token output. On Windows PowerShell cmdlets, wrap the cmdlet explicitly:
   ```powershell
   rtk powershell -NoProfile -Command "Get-ChildItem -Force"
   rtk powershell -NoProfile -Command "php artisan test --filter LoginTest"
   ```
2. **Use RTK AI / Red-Ticket-Keep for backend changes.** Write a failing Pest test first, implement the minimum production code to pass, then keep/refactor only after the test is green.
3. **Run the KEEP gate after implementation.** Use Pint, Larastan, and Pest before considering a phase or task complete:
   ```powershell
   rtk powershell -NoProfile -Command ".\vendor\bin\pint --test"
   rtk powershell -NoProfile -Command ".\vendor\bin\phpstan analyse"
   rtk powershell -NoProfile -Command "php artisan test --parallel"
   ```
4. **Use the Caveman debugging ladder before heavier tooling.** Start with temporary `dd()` / `dump()` for local request state, then `Log::debug()` for jobs or sequences, then Telescope, then Xdebug only if the earlier steps are insufficient.
5. **Never leave debug artifacts behind.** Remove `dd()`, `dump()`, `var_dump()`, temporary debug logs, throwaway scripts, and debug-only code before finishing.
6. **Use Antigravity skills for repeated workflows.** Canonical project skills live in `.agents/skills/<skill-name>/SKILL.md`; the root `SKILL.md` is compatibility/reference only.

---

## 1. Project Identity

| Key              | Value                                                            |
| ---------------- | ---------------------------------------------------------------- |
| Product          | LedgerScope                                                      |
| Type             | Accounting, Financial Analysis & Audit Management Platform       |
| Backend Stack    | PHP 8.4 + Laravel 13                                             |
| Database         | PostgreSQL 17+                                                   |
| Cache / Queue    | Redis                                                            |
| Queue Monitor    | Laravel Horizon                                                  |
| Storage          | S3-compatible private object storage                             |
| Auth             | Laravel Sanctum (session-based SPA)                              |
| Primary Key Type | BIGINT (BIGSERIAL) for MVP; UUID/ULID can be swapped in Phase 8  |

---

## 2. Absolute Rules (Never Violate)

These rules apply to every file, every migration, every service, every job:

1. **Never use `float` or `double` for money.** Always use `DECIMAL(20,2)`. Use `DECIMAL(20,4)` only when multi-currency precision is needed.
2. **Never put business logic in Controllers.** Controllers call Actions or Services only.
3. **Never put business logic in Models.** Models are Eloquent definitions + scopes only.
4. **Never expose stack traces or raw errors in production responses.** Always use structured JSON error responses.
5. **Never allow direct public file access.** All files must be stored in private storage. Downloads must use signed temporary URLs.
6. **Never allow editing a Posted journal entry.** Reversal must create a new linked journal entry.
7. **Never allow writes to a locked accounting period.** Enforce this at Service level AND at DB constraint level where feasible.
8. **Audit logs must be append-only.** No `update` or `delete` allowed on `audit_logs` from application code.
9. **Every sensitive action must dispatch an `AuditLogListener` event.** Use Laravel Events, not inline logging.
10. **Every database transaction that mutates financial state must be wrapped in `DB::transaction()`.** This includes journal posting, period locking, quarter closing, and report approval.

---

## 3. Tech Stack (Exact Versions)

```
PHP              8.4
Laravel          13.x
PostgreSQL       17+
Redis            7.x
Laravel Horizon  latest compatible
Laravel Sanctum  latest compatible
Laravel Scout    optional (Meilisearch or PG full-text)
Pest PHP         3.x          (testing)
Larastan         2.x          (static analysis, level 8 target)
Laravel Pint     latest       (code style, PSR-12 + Laravel preset)
Laravel Excel    3.x          (Maatwebsite)
DomPDF / Browsershot          (PDF generation)
Supervisor                    (queue worker process management)
Docker + GitHub Actions       (CI/CD)
```

Do NOT add packages outside this list unless a task explicitly requires it and there is no Laravel-native solution.

---

## 4. Folder & Module Structure

Strictly follow this structure. Do not create folders outside this layout without justification.

```
app/
├── Actions/
│   ├── Accounting/
│   ├── Audit/
│   ├── Company/
│   ├── Engagement/
│   ├── Evidence/
│   ├── Reporting/
│   └── Security/
│
├── Enums/
│   ├── Accounting/
│   │   ├── JournalStatus.php
│   │   ├── PeriodStatus.php
│   │   └── PeriodType.php
│   ├── Audit/
│   │   ├── EngagementStatus.php
│   │   ├── FindingSeverity.php
│   │   ├── FindingStatus.php
│   │   ├── WorkingPaperStatus.php
│   │   └── ReviewNoteStatus.php
│   ├── Common/
│   │   ├── UserStatus.php
│   │   └── EvidenceStatus.php
│   └── Reporting/
│       └── ReportStatus.php
│
├── Events/
│   ├── Accounting/
│   ├── Audit/
│   ├── Evidence/
│   └── Reporting/
│
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/
│   │   │   ├── Auth/
│   │   │   ├── Company/
│   │   │   ├── Accounting/
│   │   │   ├── Engagement/
│   │   │   ├── Evidence/
│   │   │   ├── Audit/
│   │   │   ├── Reporting/
│   │   │   └── Admin/
│   │   └── Web/            (Inertia controllers)
│   ├── Middleware/
│   ├── Requests/
│   │   ├── Accounting/
│   │   ├── Audit/
│   │   ├── Company/
│   │   └── Evidence/
│   └── Resources/
│       ├── Accounting/
│       ├── Audit/
│       └── Company/
│
├── Jobs/
│   ├── Imports/
│   ├── Reports/
│   ├── Notifications/
│   └── Analytics/
│
├── Listeners/
│   ├── AuditTrail/
│   │   └── WriteAuditLog.php
│   └── Notifications/
│
├── Models/
│   (all Eloquent models flat in this directory)
│
├── Policies/
│
├── Services/
│   ├── Accounting/
│   │   ├── JournalService.php
│   │   ├── TrialBalanceService.php
│   │   ├── PeriodLockService.php
│   │   └── QuarterClosingService.php
│   ├── Audit/
│   │   ├── EngagementService.php
│   │   ├── WorkingPaperService.php
│   │   └── FindingService.php
│   ├── Company/
│   ├── Evidence/
│   │   └── EvidenceService.php
│   ├── FinancialStatement/
│   │   └── StatementBuilderService.php
│   ├── Reporting/
│   │   └── ReportGeneratorService.php
│   └── Risk/
│
├── Support/
│   ├── Money/
│   ├── Period/
│   └── QueryFilters/
│
└── ValueObjects/
    ├── Money.php
    ├── PeriodRange.php
    └── FiscalQuarter.php

database/
├── migrations/
├── seeders/
└── factories/

routes/
├── web.php
├── api.php
└── console.php

config/
└── ledgerscope.php    (app-level config: file size limits, retention policy, etc.)
```

---

## 5. Build Phases

Execute phases in strict order. Do not start Phase N+1 until Phase N is complete and all tests pass.

---

### Phase 1 — Foundation (Identity & Access)

**Goal:** Working auth, roles, permissions, audit log foundation.

#### 1.1 Laravel Project Setup
- Fresh Laravel 13 install.
- Configure PostgreSQL connection.
- Configure Redis (cache, queue, session).
- Install and configure Laravel Sanctum.
- Install Laravel Horizon.
- Install Pest PHP.
- Install Larastan (target level 8).
- Install Laravel Pint.
- Create `config/ledgerscope.php` with: `max_file_size_mb`, `allowed_mime_types[]`, `audit_log_retention_years`.

#### 1.2 Migrations — Identity Domain
Create migrations in this order:

```
users
roles
permissions
role_permissions
user_roles
user_invitations
```

Refer to Section 5.3 of Infrastructure.md for exact column definitions.

Key constraints:
- `users.email` must be `UNIQUE`.
- `user_roles(user_id, role_id)` must be `UNIQUE`.
- `role_permissions(role_id, permission_id)` must be `UNIQUE`.
- Add `deleted_at` to `users` only (soft delete).

#### 1.3 Models
Create Eloquent models:
- `User` — HasMany roles via `user_roles`, SoftDeletes.
- `Role` — BelongsToMany `Permission` via `role_permissions`.
- `Permission` — BelongsToMany `Role` via `role_permissions`.
- `UserInvitation` — BelongsTo `Role`, BelongsTo `User` (invited_by).

#### 1.4 Enums
```php
// app/Enums/Common/UserStatus.php
enum UserStatus: string {
    case Active   = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';
}

// app/Enums/Common/InvitationStatus.php
enum InvitationStatus: string {
    case Pending  = 'pending';
    case Accepted = 'accepted';
    case Expired  = 'expired';
}
```

#### 1.5 Seeders
- `RoleSeeder` — seed all 9 roles from PRD Section 3.1.
- `PermissionSeeder` — seed all permissions from Infrastructure Section 5.3.3.
- `RolePermissionSeeder` — map default permissions to roles.
- `SuperAdminSeeder` — create one default super admin user.

#### 1.6 Auth Endpoints
Implement under `routes/api.php` → `Api/V1/Auth/`:

```
POST   /api/v1/auth/login
POST   /api/v1/auth/logout
POST   /api/v1/auth/forgot-password
POST   /api/v1/auth/reset-password
POST   /api/v1/auth/verify-email/{token}
POST   /api/v1/auth/mfa/verify
GET    /api/v1/auth/me
```

Rules:
- Use `LoginRequest` Form Request with rate limiting (max 5 attempts / 60 seconds per IP).
- On successful login: create session, load roles/permissions, write `login` event to audit log.
- On failed login: increment counter, write `failed_login` event to audit log.
- On logout: invalidate session, write `logout` event to audit log.

#### 1.7 User Invitation Flow
```
POST   /api/v1/admin/users/invite
POST   /api/v1/invitations/{token}/accept
```

- Invitation token must be a random 64-char hex string.
- Invitation expires in 72 hours.
- On acceptance: activate user, assign role, log `user_activated` event.

#### 1.8 Audit Log Foundation
Create `audit_logs` migration and `AuditLog` model.

```php
// app/Events/Accounting/JournalPosted.php  (example — create equivalents for each logged action)
// app/Listeners/AuditTrail/WriteAuditLog.php

class WriteAuditLog implements ShouldQueue
{
    public function handle(object $event): void
    {
        AuditLog::create([
            'user_id'     => $event->userId,
            'company_id'  => $event->companyId ?? null,
            'action'      => $event->action,
            'object_type' => $event->objectType ?? null,
            'object_id'   => $event->objectId ?? null,
            'before_value'=> $event->before ?? null,
            'after_value' => $event->after ?? null,
            'ip_address'  => $event->ipAddress,
            'user_agent'  => $event->userAgent,
        ]);
    }
}
```

Register listener in `EventServiceProvider`. `AuditLog` must have NO `updated_at` column. The model must override `save()` and `update()` to throw `\LogicException('Audit logs are immutable.')`.

#### 1.9 Authorization Layer
- Create `HasPermissions` trait on `User` model: `hasPermission(string $permission): bool`.
- Create `AuthorizationServiceProvider` registering all Gates and Policies.
- Create `EnsureCompanyAccess` middleware that validates the user belongs to `company_id` in the route.

#### 1.10 Phase 1 Tests
```
tests/Feature/Auth/LoginTest.php
tests/Feature/Auth/InvitationTest.php
tests/Feature/Auth/PermissionTest.php
tests/Unit/Models/UserTest.php
```

Minimum coverage: login success, login failure + rate limit, logout, invitation accept, role permission check, audit log is append-only.

---

### Phase 2 — Company & Client Domain

**Goal:** Multi-company workspace with user assignment.

#### 2.1 Migrations
```
companies
company_users
company_contacts
```

Key rules:
- `companies.currency` default `'IDR'`.
- `companies.fiscal_year_start_month` SMALLINT, default `1`, check `>= 1 AND <= 12`.
- `company_users(company_id, user_id)` must be `UNIQUE`.
- Add `deleted_at` to `companies`.

#### 2.2 Models
- `Company` — HasMany `CompanyUser`, HasMany `FiscalYear`, SoftDeletes.
- `CompanyUser` — BelongsTo `Company`, BelongsTo `User`.
- `CompanyContact` — BelongsTo `Company`.

#### 2.3 Query Scope — Company Access
Every query involving company data must go through a global scope or explicit scope check:

```php
// app/Support/QueryFilters/CompanyScope.php
// Applied as a service-level guard, not a global Eloquent scope
// to avoid accidental scope removal.
```

In every Service method that fetches company-scoped data:
```php
abort_unless(
    CompanyAccessGuard::check($user, $companyId),
    403,
    'Access denied to this company.'
);
```

#### 2.4 Endpoints
```
GET    /api/v1/companies
POST   /api/v1/companies
GET    /api/v1/companies/{id}
PUT    /api/v1/companies/{id}
DELETE /api/v1/companies/{id}         (soft delete)
POST   /api/v1/companies/{id}/users   (assign user)
DELETE /api/v1/companies/{id}/users/{userId}
GET    /api/v1/companies/{id}/contacts
POST   /api/v1/companies/{id}/contacts
```

#### 2.5 Policies
- `CompanyPolicy` — `view`, `create`, `update`, `delete`, `manageUsers`.
- Super Admin can do anything.
- Firm Admin can manage companies assigned to them.
- Other roles: view only for their assigned companies.

#### 2.6 Phase 2 Tests
```
tests/Feature/Company/CompanyCrudTest.php
tests/Feature/Company/CompanyUserAssignmentTest.php
tests/Feature/Company/CompanyAccessIsolationTest.php
```

Must include: Client user cannot see another company's data.

---

### Phase 3 — Fiscal Year, Period & Quarter

**Goal:** Structured accounting period infrastructure.

#### 3.1 Migrations
```
fiscal_years
quarters
accounting_periods
quarter_closing_checklists
```

Key constraints:
- `fiscal_years(company_id, year)` must be `UNIQUE`.
- `quarters(company_id, fiscal_year_id, quarter_code)` must be `UNIQUE`.
- `accounting_periods(company_id, period_name, period_type)` must be `UNIQUE`.
- `quarter_closing_checklists(quarter_id, checklist_key)` must be `UNIQUE`.

#### 3.2 Period Auto-Generation Service
```php
// app/Services/Accounting/FiscalYearGeneratorService.php

public function generate(Company $company, int $year): FiscalYear
{
    // Wrapped in DB::transaction()
    // 1. Create fiscal_year record
    // 2. Create 12 monthly accounting_periods
    // 3. Create 4 quarters (Q1–Q4)
    // 4. Map months to quarters
    // 5. Create default closing checklist items for each quarter
    // 6. Dispatch FiscalYearCreated event
}
```

Checklist keys to seed per quarter (from Infrastructure Section 3.8.2):
```
all_journals_posted
imported_data_validated
trial_balance_balanced
bank_reconciliation_completed
ar_reconciliation_completed
ap_reconciliation_completed
tax_account_reviewed
accrual_entries_posted
prepayment_entries_posted
depreciation_entries_posted
financial_statements_generated
manager_review_completed
quarter_approved
quarter_locked
```

#### 3.3 Period Lock Service
```php
// app/Services/Accounting/PeriodLockService.php

public function lock(AccountingPeriod $period, User $user): void
{
    // Validate user has quarter.lock permission
    // Validate period is not already locked
    // Wrapped in DB::transaction()
    // Set is_locked = true, locked_at, locked_by
    // Dispatch PeriodLocked event → WriteAuditLog
}

public function unlock(AccountingPeriod $period, User $user, string $reason): void
{
    // Validate user has quarter.unlock permission
    // Wrapped in DB::transaction()
    // Set is_locked = false, unlock_reason
    // Mark related reports as outdated
    // Dispatch PeriodUnlocked event → WriteAuditLog
}
```

#### 3.4 Endpoints
```
GET    /api/v1/companies/{companyId}/fiscal-years
POST   /api/v1/companies/{companyId}/fiscal-years
GET    /api/v1/companies/{companyId}/fiscal-years/{id}
GET    /api/v1/companies/{companyId}/fiscal-years/{id}/periods
GET    /api/v1/companies/{companyId}/fiscal-years/{id}/quarters
POST   /api/v1/companies/{companyId}/quarters/{id}/lock
POST   /api/v1/companies/{companyId}/quarters/{id}/unlock
GET    /api/v1/companies/{companyId}/quarters/{id}/checklist
PATCH  /api/v1/companies/{companyId}/quarters/{id}/checklist/{key}
```

#### 3.5 Enums
```php
enum PeriodType: string {
    case Monthly   = 'monthly';
    case Quarterly = 'quarterly';
    case Annual    = 'annual';
}

enum PeriodStatus: string {
    case Open   = 'open';
    case Closed = 'closed';
    case Locked = 'locked';
}

enum QuarterCode: string {
    case Q1 = 'Q1';
    case Q2 = 'Q2';
    case Q3 = 'Q3';
    case Q4 = 'Q4';
}
```

#### 3.6 Phase 3 Tests
- Fiscal year generates exactly 12 periods and 4 quarters.
- Quarter months map correctly to their quarter.
- Cannot lock an already-locked period.
- Unlock requires reason.
- Unlock event is written to audit log.

---

### Phase 4 — Chart of Accounts

**Goal:** Hierarchical account structure per company.

#### 4.1 Migration
```
chart_of_accounts
```

Key constraints:
- `(company_id, account_code)` must be `UNIQUE`.
- `parent_id` self-references `chart_of_accounts(id)` ON DELETE SET NULL.
- Add `deleted_at` (soft delete).

#### 4.2 Import Action
```php
// app/Actions/Accounting/ImportChartOfAccountsAction.php
```

Steps:
1. Validate file (xlsx/csv, max 10 MB).
2. Store original file in private storage.
3. Create `import_batches` record with status `pending`.
4. Dispatch `ImportChartOfAccountsJob` to `imports` queue.
5. Job processes rows: validate code uniqueness, validate type, validate parent exists.
6. Write success rows, collect failed rows.
7. Update `import_batches` with `success_rows`, `failed_rows`, `status = completed`.
8. Store error report to private storage if failures exist.

#### 4.3 Validation Rules
- `account_code`: required, string, max 80, unique per company.
- `account_type`: must match `AccountType` enum values.
- `parent_id`: if provided, must exist in the same company.
- Accounts with posted journal entry lines cannot be deleted (only archived).

#### 4.4 Enums
```php
enum AccountType: string {
    case Asset        = 'asset';
    case Liability    = 'liability';
    case Equity       = 'equity';
    case Revenue      = 'revenue';
    case Cogs         = 'cost_of_goods_sold';
    case Expense      = 'expense';
    case OtherIncome  = 'other_income';
    case OtherExpense = 'other_expense';
}
```

#### 4.5 Endpoints
```
GET    /api/v1/companies/{companyId}/accounts
POST   /api/v1/companies/{companyId}/accounts
GET    /api/v1/companies/{companyId}/accounts/{id}
PUT    /api/v1/companies/{companyId}/accounts/{id}
DELETE /api/v1/companies/{companyId}/accounts/{id}   (soft delete)
POST   /api/v1/companies/{companyId}/accounts/import
GET    /api/v1/companies/{companyId}/accounts/import/{batchId}
```

---

### Phase 5 — Journal Entry Management

**Goal:** Core double-entry accounting engine.

#### 5.1 Migrations
```
journal_entries
journal_entry_lines
import_batches
```

Key constraints on `journal_entry_lines`:
```sql
CHECK (debit >= 0),
CHECK (credit >= 0),
CHECK (NOT (debit > 0 AND credit > 0))
```

#### 5.2 Journal Service
```php
// app/Services/Accounting/JournalService.php
```

Implement these methods, each wrapped in `DB::transaction()` where applicable:

```php
public function create(CreateJournalDTO $dto, User $user): JournalEntry;
public function submit(JournalEntry $journal, User $user): void;
public function approve(JournalEntry $journal, User $user): void;
public function post(JournalEntry $journal, User $user): void;
public function reverse(JournalEntry $journal, User $user, string $reason): JournalEntry;
public function reject(JournalEntry $journal, User $user, string $reason): void;
```

Business rules enforced inside the service:
- `create`: period must be open and not locked.
- `post`: total debit must equal total credit (assert with `Money` value object).
- `post`: journal date must be inside the period's date range.
- `post`: all accounts must be `is_active = true`.
- `post`: minimum 2 lines required.
- `post`: after posting, journal becomes immutable — any attempt to mutate a `posted` journal throws `\DomainException`.
- `reverse`: creates new journal with negated lines, sets `reversed_from_id`, posts immediately.

#### 5.3 ValueObject: Money
```php
// app/ValueObjects/Money.php
// Wraps bcmath operations — never use native float arithmetic for money.

class Money
{
    public function __construct(
        private readonly string $amount,   // stored as string to avoid float
        private readonly string $currency
    ) {}

    public function add(Money $other): Money;
    public function subtract(Money $other): Money;
    public function equals(Money $other): bool;
    public function isZero(): bool;
    public static function zero(string $currency): self;
}
```

#### 5.4 Journal Import
- Dispatch `ImportJournalEntriesJob` to `imports` queue.
- Validate: period open, accounts active, debit/credit balanced per row group.
- Create draft entries on success.
- Write error report for failed rows.

#### 5.5 Enums
```php
enum JournalStatus: string {
    case Draft     = 'draft';
    case Submitted = 'submitted';
    case Reviewed  = 'reviewed';
    case Approved  = 'approved';
    case Posted    = 'posted';
    case Rejected  = 'rejected';
    case Reversed  = 'reversed';
}

enum JournalSourceType: string {
    case Manual    = 'manual';
    case Import    = 'import';
    case Recurring = 'recurring';
    case Reversal  = 'reversal';
    case System    = 'system';
}
```

#### 5.6 Endpoints
```
GET    /api/v1/companies/{companyId}/journals
POST   /api/v1/companies/{companyId}/journals
GET    /api/v1/companies/{companyId}/journals/{id}
PUT    /api/v1/companies/{companyId}/journals/{id}      (draft only)
POST   /api/v1/companies/{companyId}/journals/{id}/submit
POST   /api/v1/companies/{companyId}/journals/{id}/approve
POST   /api/v1/companies/{companyId}/journals/{id}/post
POST   /api/v1/companies/{companyId}/journals/{id}/reverse
POST   /api/v1/companies/{companyId}/journals/{id}/reject
POST   /api/v1/companies/{companyId}/journals/import
```

#### 5.7 Audit Events to Dispatch
- `JournalCreated` → log `create_journal`
- `JournalPosted` → log `post_journal`
- `JournalReversed` → log `reverse_journal`

#### 5.8 Phase 5 Tests
- Unbalanced journal cannot be posted.
- Journal with locked period date cannot be posted.
- Posted journal cannot be edited.
- Reversal creates linked journal with opposite lines.
- Import batch tracks success and failed rows.

---

### Phase 6 — Trial Balance & Reconciliation

**Goal:** Aggregated ledger state and period reconciliation.

#### 6.1 Trial Balance Service
```php
// app/Services/Accounting/TrialBalanceService.php

public function generate(
    Company $company,
    AccountingPeriod|Quarter $period,
    User $user
): Collection;   // Returns collection of TrialBalanceLine ValueObjects
```

Rules:
- Only aggregate `posted` journal entries.
- Calculate: opening balance, movement debit, movement credit, ending balance.
- Validate: sum(ending_debit) === sum(ending_credit).
- Snapshot result into `trial_balances` table.
- Dispatch `TrialBalanceGenerated` event.

#### 6.2 Reconciliation
Migrations: `reconciliations`, `reconciliation_items`.

```php
// app/Services/Accounting/ReconciliationService.php

public function create(CreateReconciliationDTO $dto): Reconciliation;
public function autoMatch(Reconciliation $reconciliation): void;
public function manualMatch(ReconciliationItem $a, ReconciliationItem $b, User $user): void;
public function approve(Reconciliation $reconciliation, User $user): void;
```

Matching strategies (implement as separate strategy classes):
- `ExactAmountMatcher`
- `DateAndAmountMatcher`
- `ReferenceNumberMatcher`

#### 6.3 Endpoints
```
POST   /api/v1/companies/{companyId}/trial-balance/generate
GET    /api/v1/companies/{companyId}/trial-balance

GET    /api/v1/companies/{companyId}/reconciliations
POST   /api/v1/companies/{companyId}/reconciliations
POST   /api/v1/companies/{companyId}/reconciliations/{id}/auto-match
POST   /api/v1/companies/{companyId}/reconciliations/{id}/match
POST   /api/v1/companies/{companyId}/reconciliations/{id}/approve
```

---

### Phase 7 — Financial Statements

**Goal:** Template-driven financial statement generation with versioning.

#### 7.1 Migrations
```
financial_statement_templates
financial_statement_lines
account_statement_mappings
financial_statement_versions
financial_statement_values
```

#### 7.2 Statement Builder Service
```php
// app/Services/FinancialStatement/StatementBuilderService.php

public function generateDraft(
    Company $company,
    FiscalYear|Quarter|AccountingPeriod $scope,
    User $user
): FinancialStatementVersion;

public function approve(FinancialStatementVersion $version, User $user): void;
public function lock(FinancialStatementVersion $version, User $user): void;
```

Rules:
- Must be generated from a completed, balanced trial balance snapshot.
- All accounts must be mapped; unmapped accounts must be returned as a warning list, not silently skipped.
- Approved version is immutable; any new change creates a new version number.
- `approve` dispatches `StatementApproved` event → audit log.
- `lock` sets `locked_at`, prevents further edits.

#### 7.3 Statement Types to Support
```php
enum StatementType: string {
    case BalanceSheet      = 'balance_sheet';
    case ProfitAndLoss     = 'profit_and_loss';
    case CashFlow          = 'cash_flow';
    case EquityChanges     = 'equity_changes';
    case Notes             = 'notes';
    case ManagementReport  = 'management_report';
}
```

#### 7.4 Endpoints
```
GET    /api/v1/companies/{companyId}/statement-templates
POST   /api/v1/companies/{companyId}/statement-templates
POST   /api/v1/companies/{companyId}/statement-templates/{id}/lines
POST   /api/v1/companies/{companyId}/account-mappings
GET    /api/v1/companies/{companyId}/financial-statements
POST   /api/v1/companies/{companyId}/financial-statements/generate
GET    /api/v1/companies/{companyId}/financial-statements/{id}
POST   /api/v1/companies/{companyId}/financial-statements/{id}/approve
POST   /api/v1/companies/{companyId}/financial-statements/{id}/lock
```

---

### Phase 8 — Engagement & Audit Domain

**Goal:** Full audit engagement lifecycle.

#### 8.1 Migrations (in order)
```
engagements
engagement_members
audit_plans
risks
controls
audit_procedures
working_papers
working_paper_references
document_requests
evidence_files
evidence_links
review_notes
review_note_replies
audit_findings
```

#### 8.2 Engagement Service
```php
// app/Services/Audit/EngagementService.php

public function create(CreateEngagementDTO $dto, User $user): Engagement;
public function assignMember(Engagement $engagement, User $member, string $role): void;
public function updateStatus(Engagement $engagement, EngagementStatus $status, User $user): void;
public function complete(Engagement $engagement, User $partner): void;
public function archive(Engagement $engagement, User $user): void;
```

Status transition rules (enforce in service, not in model):
```
draft → planning → data_collection → fieldwork → review → reporting → completed → archived
```
No skipping statuses. Invalid transitions throw `\DomainException`.

#### 8.3 Evidence Service
```php
// app/Services/Evidence/EvidenceService.php

public function upload(UploadEvidenceDTO $dto, User $uploader): EvidenceFile;
public function accept(EvidenceFile $file, User $reviewer): void;
public function reject(EvidenceFile $file, User $reviewer, string $reason): void;
public function getSignedDownloadUrl(EvidenceFile $file, User $user): string;
```

Rules:
- Validate MIME type against `config('ledgerscope.allowed_mime_types')`.
- Validate file size against `config('ledgerscope.max_file_size_mb')`.
- Store to private disk under `/companies/{companyId}/engagements/{engagementId}/evidence/`.
- Calculate SHA-256 checksum and store in `evidence_files.checksum`.
- Every upload dispatches `EvidenceUploaded` → audit log `upload_file`.
- Every download dispatches `EvidenceDownloaded` → audit log `download_file`.
- Accepted evidence: client cannot delete.
- Rejected evidence + new upload: increments `version`.

#### 8.4 Working Paper Service
```php
// app/Services/Audit/WorkingPaperService.php

public function create(CreateWorkingPaperDTO $dto, User $preparer): WorkingPaper;
public function markPrepared(WorkingPaper $wp, User $preparer): void;
public function markReviewed(WorkingPaper $wp, User $reviewer): void;
public function approve(WorkingPaper $wp, User $manager): void;
public function lock(WorkingPaper $wp): void;
```

Rules:
- Cannot mark reviewed if there are open `review_notes` linked to this working paper.
- Cannot approve if not yet reviewed.
- Lock sets `locked_at` and prevents all further mutations.
- Sign-off events dispatched and logged.

#### 8.5 Review Note Service
```php
// app/Services/Audit/ReviewNoteService.php

public function create(CreateReviewNoteDTO $dto, User $reviewer): ReviewNote;
public function reply(ReviewNote $note, User $user, string $message): ReviewNoteReply;
public function resolve(ReviewNote $note, User $resolver): void;
public function reopen(ReviewNote $note, User $user): void;
```

Supported `noteable_type` values:
```
journal_entry, quarter_closing, financial_statement_version,
evidence_file, working_paper, audit_procedure, audit_finding, report
```

#### 8.6 Finding Service
```php
// app/Services/Audit/FindingService.php

public function create(CreateFindingDTO $dto, User $creator): AuditFinding;
public function approve(AuditFinding $finding, User $manager): void;    // required for high/critical
public function addManagementResponse(AuditFinding $finding, User $client, string $response): void;
public function close(AuditFinding $finding, User $manager): void;
```

Severity rule: `high` or `critical` severity requires manager approval before status can move past `open`.

#### 8.7 Enums
```php
enum EngagementStatus: string { ... }
enum EngagementType: string { ... }
enum WorkingPaperStatus: string { ... }
enum ReviewNoteStatus: string { case Open = 'open'; case Resolved = 'resolved'; }
enum FindingSeverity: string { case Low = 'low'; case Medium = 'medium'; case High = 'high'; case Critical = 'critical'; }
enum FindingStatus: string { ... }
enum EvidenceStatus: string { case Submitted = 'submitted'; case UnderReview = 'under_review'; case Accepted = 'accepted'; case Rejected = 'rejected'; }
```

#### 8.8 Endpoints
```
# Engagements
GET    /api/v1/companies/{companyId}/engagements
POST   /api/v1/companies/{companyId}/engagements
GET    /api/v1/engagements/{id}
PUT    /api/v1/engagements/{id}
POST   /api/v1/engagements/{id}/members
DELETE /api/v1/engagements/{id}/members/{userId}

# Audit Plans
POST   /api/v1/engagements/{id}/audit-plan
GET    /api/v1/engagements/{id}/audit-plan

# Risks
GET    /api/v1/engagements/{id}/risks
POST   /api/v1/engagements/{id}/risks
PUT    /api/v1/engagements/{id}/risks/{riskId}

# Controls
GET    /api/v1/engagements/{id}/controls
POST   /api/v1/engagements/{id}/controls

# Procedures
GET    /api/v1/engagements/{id}/procedures
POST   /api/v1/engagements/{id}/procedures
PATCH  /api/v1/engagements/{id}/procedures/{procedureId}/complete

# Document Requests
GET    /api/v1/engagements/{id}/document-requests
POST   /api/v1/engagements/{id}/document-requests
GET    /api/v1/document-requests/{id}
PATCH  /api/v1/document-requests/{id}

# Evidence
POST   /api/v1/document-requests/{requestId}/evidence
POST   /api/v1/evidence/{id}/accept
POST   /api/v1/evidence/{id}/reject
GET    /api/v1/evidence/{id}/download

# Working Papers
GET    /api/v1/engagements/{id}/working-papers
POST   /api/v1/engagements/{id}/working-papers
GET    /api/v1/working-papers/{id}
POST   /api/v1/working-papers/{id}/prepared
POST   /api/v1/working-papers/{id}/reviewed
POST   /api/v1/working-papers/{id}/approve

# Review Notes
GET    /api/v1/working-papers/{id}/review-notes
POST   /api/v1/working-papers/{id}/review-notes
POST   /api/v1/review-notes/{id}/reply
POST   /api/v1/review-notes/{id}/resolve

# Findings
GET    /api/v1/engagements/{id}/findings
POST   /api/v1/engagements/{id}/findings
PUT    /api/v1/findings/{id}
POST   /api/v1/findings/{id}/approve
POST   /api/v1/findings/{id}/management-response
POST   /api/v1/findings/{id}/close
```

---

### Phase 9 — Reporting & Export

**Goal:** Queue-based report generation with versioning and secure downloads.

#### 9.1 Migration
```
reports
report_downloads
```

#### 9.2 Report Generator Service
```php
// app/Services/Reporting/ReportGeneratorService.php

public function dispatch(GenerateReportDTO $dto, User $user): Report;
```

Rules:
- Small reports (< configurable row threshold): generate synchronously.
- Large reports: dispatch `GenerateReportJob` to `reports` queue.
- Store generated file to private storage.
- Calculate SHA-256 checksum.
- Notify user when complete via `ReportReadyNotification`.
- Download via signed URL (1-hour expiry).
- Log every download in `report_downloads`.

#### 9.3 Report Types to Implement
```php
enum ReportType: string {
    case QuarterlyFinancial  = 'quarterly_financial';
    case AnnualFinancial     = 'annual_financial';
    case TrialBalance        = 'trial_balance';
    case GeneralLedger       = 'general_ledger';
    case FinancialAnalysis   = 'financial_analysis';
    case AuditPlanningMemo   = 'audit_planning_memo';
    case RiskAssessment      = 'risk_assessment';
    case AuditFindings       = 'audit_findings';
    case InternalControl     = 'internal_control';
    case ManagementLetter    = 'management_letter';
    case WorkingPaperIndex   = 'working_paper_index';
    case EvidenceList        = 'evidence_list';
}
```

#### 9.4 Endpoints
```
POST   /api/v1/companies/{companyId}/reports/generate
GET    /api/v1/companies/{companyId}/reports
GET    /api/v1/reports/{id}
GET    /api/v1/reports/{id}/download
POST   /api/v1/reports/{id}/approve
```

---

### Phase 10 — Notifications, Analytics & Scheduler

**Goal:** Background automation and in-app notifications.

#### 10.1 Migration
```
notifications
analytics_runs
analytics_exceptions
```

#### 10.2 Notification Channels
- In-app: stored in `notifications` table, returned via `/api/v1/notifications`.
- Email: Laravel Notifications + SMTP.
- Queue: all notifications dispatched to `notifications` queue.

#### 10.3 Notification Triggers (wire these up as Listeners)
```
UserInvited                → InvitationEmailNotification
EvidenceUploaded           → EvidenceSubmittedNotification (to reviewer)
EvidenceRejected           → EvidenceRejectedNotification (to client)
ReviewNoteAssigned         → ReviewNoteNotification (to preparer)
ReviewNoteResolved         → ReviewNoteResolvedNotification (to reviewer)
FindingDueSoon             → FindingOverdueNotification
DocumentRequestOverdue     → DocumentRequestOverdueNotification
QuarterApproved            → QuarterApprovedNotification
ReportReady                → ReportReadyNotification
```

#### 10.4 Scheduler (routes/console.php)
```php
Schedule::job(new MarkOverdueDocumentRequestsJob)->hourly();
Schedule::job(new MarkOverdueFindingsJob)->hourly();
Schedule::job(new SendDueSoonDocumentRequestRemindersJob)->everyFiveMinutes();
Schedule::job(new SendDailyDigestJob)->dailyAt('07:00');
Schedule::job(new CleanTemporaryUploadsJob)->daily();
Schedule::job(new BackupDatabaseJob)->daily();
Schedule::job(new SendWeeklyEngagementDigestJob)->weekly();
Schedule::job(new GeneratePeriodStatusReportJob)->monthly();
```

#### 10.5 Journal Analytics Service
```php
// app/Services/Accounting/JournalAnalyticsService.php

public function detectDuplicates(Company $company, Quarter $quarter): Collection;
public function detectWeekendPostings(Company $company, Quarter $quarter): Collection;
public function detectRoundAmounts(Company $company, Quarter $quarter, int $threshold): Collection;
public function detectHighValueTransactions(Company $company, Quarter $quarter, string $threshold): Collection;
public function detectMissingDescriptions(Company $company, Quarter $quarter): Collection;
public function scoreJournalEntry(JournalEntry $entry): float;
```

All analytics results stored in `analytics_runs` + `analytics_exceptions`.

---

### Phase 11 — Security Hardening & Production Readiness

**Goal:** Harden the application for production deployment.

#### 11.1 Rate Limiting
```php
// app/Http/Middleware/

RateLimitLogin::class          // 5 attempts / 60s per IP
RateLimitApi::class            // 120 requests / minute per user
RateLimitFileUpload::class     // 20 uploads / minute per user
```

#### 11.2 Security Headers Middleware
```php
// app/Http/Middleware/SecurityHeaders.php
// Add: X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, HSTS
```

#### 11.3 File Validation
Strict MIME validation using both file extension AND `finfo` PHP extension — never trust only the extension.

```php
// app/Support/FileValidator.php
public static function validate(UploadedFile $file): void;
```

#### 11.4 Global Exception Handler
- Map domain exceptions (`\DomainException`, `\LogicException`) to clean 422/403 JSON responses.
- Never expose stack traces outside `APP_DEBUG=true`.
- Log all 5xx errors to error monitoring.

#### 11.5 Health Check Endpoint
```php
// routes/api.php
Route::get('/health', HealthCheckController::class);

// Returns:
{
  "status": "ok",
  "database": "ok",
  "redis": "ok",
  "queue": "ok",
  "storage": "ok",
  "timestamp": "ISO-8601"
}
```

#### 11.6 Composite Indexes
Add all composite indexes from Infrastructure.md Section 7.2:
```sql
idx_journals_company_period_status
idx_document_requests_company_status_due
idx_evidence_company_engagement_status
idx_working_papers_engagement_status
idx_findings_engagement_status_severity
idx_audit_logs_company_action_created
```

---

## 6. API Response Contract

All API responses must follow this exact format:

### Success (200 / 201)
```json
{
    "success": true,
    "message": "Resource loaded successfully.",
    "data": { },
    "meta": { }
}
```

### Paginated
```json
{
    "success": true,
    "message": "Resources loaded.",
    "data": [ ],
    "meta": {
        "current_page": 1,
        "last_page": 10,
        "per_page": 25,
        "total": 250
    }
}
```

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {
        "field_name": ["Error message."]
    }
}
```

### Authorization Error (403)
```json
{
    "success": false,
    "message": "You do not have permission to perform this action."
}
```

### Domain Error (422)
```json
{
    "success": false,
    "message": "Cannot post journal: total debit does not equal total credit."
}
```

Use a single `ApiResponse` helper class or trait for consistency.

---

## 7. Database Conventions

| Convention                   | Rule                                                                    |
| ----------------------------- | ----------------------------------------------------------------------- |
| Primary key                   | `BIGSERIAL` named `id`                                                   |
| Foreign keys                  | `{table_singular}_id` e.g. `company_id`, `user_id`                      |
| Timestamps                    | Always `created_at`, `updated_at` (NOT NULL)                            |
| Soft delete                   | `deleted_at TIMESTAMP NULL` — only on business-critical tables          |
| Money columns                 | `DECIMAL(20,2)` — never `float`                                         |
| Boolean columns               | `BOOLEAN NOT NULL DEFAULT FALSE`                                        |
| Status columns                | `VARCHAR(30) NOT NULL DEFAULT 'draft'` — backed by PHP Enum             |
| Audit-only tables             | Only `created_at` — no `updated_at`                                     |
| Index naming                  | `idx_{table}_{column(s)}` e.g. `idx_journal_entries_company_id`         |
| Unique constraint naming      | `{table}_{col1}_{col2}_unique`                                           |
| Migration naming              | `YYYY_MM_DD_HHMMSS_create_{table}_table.php`                            |

---

## 8. Coding Conventions

### Controller Rules
```php
// CORRECT: Thin controller
class PostJournalController
{
    public function __invoke(PostJournalRequest $request, JournalEntry $journal): JsonResponse
    {
        $result = $this->journalService->post($journal, $request->user());
        return ApiResponse::success($result, 'Journal posted successfully.');
    }
}

// WRONG: Fat controller with business logic — never do this
```

### Service Rules
- One service per domain concept.
- Services receive models and DTOs, not raw `Request` objects.
- Services are injected via constructor (use Laravel DI container).
- Services must not directly access `Auth::user()` — pass the user as a parameter.

### Action Rules
- Single-purpose: one `Action` class = one atomic operation.
- Actions are fine for complex multi-step operations that don't fit a service method.
- Name: `{Verb}{Noun}Action` e.g. `ImportChartOfAccountsAction`.

### Enum Rules
- Every `status` or `type` column must have a corresponding PHP Enum.
- Cast the column in the Eloquent model: `protected $casts = ['status' => JournalStatus::class]`.
- Never compare status as raw strings in service or controller code.

### DTO Rules
- Use readonly classes or final classes.
- Create `{CreateNoun}DTO`, `{UpdateNoun}DTO` as needed.
- Construct from Form Request in the controller via a static factory or `from(Request)` method.

### Event & Listener Rules
- Events are named in past tense: `JournalPosted`, `EvidenceAccepted`, `PeriodLocked`.
- Listeners that write to `audit_logs` must implement `ShouldQueue` (async, `audit-trail` queue).
- Listeners that send notifications must implement `ShouldQueue` (async, `notifications` queue).

---

## 9. Queue Configuration

```php
// config/queue.php — queue names to configure

'connections' => [
    'redis' => [
        'queues' => [
            'critical',
            'imports',
            'reports',
            'notifications',
            'audit-trail',
            'analytics',
            'emails',
            'default',
        ]
    ]
]
```

Job-to-queue mapping:

| Job Class                       | Queue          |
| -------------------------------- | -------------- |
| `ImportJournalEntriesJob`        | `imports`      |
| `ImportChartOfAccountsJob`       | `imports`      |
| `GenerateReportJob`              | `reports`      |
| `WriteAuditLog` (listener)       | `audit-trail`  |
| `Send*Notification` (listeners)  | `notifications`|
| `RunJournalAnalyticsJob`         | `analytics`    |
| `SendDailyDigestJob`             | `emails`       |

---

## 10. Testing Checklist

Every Phase must have tests passing before the next Phase begins.

### Minimum Test Coverage by Domain

| Domain              | Required Tests                                                              |
| ------------------- | --------------------------------------------------------------------------- |
| Auth                | Login, logout, failed login, rate limit, MFA, invitation lifecycle          |
| Company             | CRUD, user assignment, data isolation between companies                     |
| Period              | Fiscal year generation, period lock, period unlock, audit log written       |
| Journal             | Balanced post, unbalanced rejected, locked period rejected, reversal flow   |
| Trial Balance       | Correct aggregation from posted journals only                               |
| Financial Statement | Template mapping, generation, versioning, lock after approval               |
| Evidence            | Upload, MIME validation, accept, reject, versioning, signed URL             |
| Working Paper       | Create, prepared, reviewed (blocks on open notes), approve, lock            |
| Findings            | Create, high severity approval gate, management response, close             |
| Audit Log           | Append-only, immutability enforced, login/logout/post events written        |
| Permissions         | Client cannot access internal objects; auditor cannot access other engagements |

### Test File Naming
```
tests/
├── Unit/
│   ├── ValueObjects/MoneyTest.php
│   ├── Services/Accounting/JournalServiceTest.php
│   └── ...
└── Feature/
    ├── Auth/LoginTest.php
    ├── Company/CompanyAccessTest.php
    ├── Accounting/JournalPostingTest.php
    ├── Accounting/PeriodLockTest.php
    ├── Audit/WorkingPaperSignOffTest.php
    ├── Evidence/EvidenceUploadTest.php
    └── ...
```

---

## 11. What NOT to Do

- Do NOT use `float` or `double` for any money column or calculation.
- Do NOT put SQL queries directly in Controllers or routes.
- Do NOT skip `DB::transaction()` for financial mutations.
- Do NOT use Laravel's global scopes on financial tables (use explicit service-layer guards).
- Do NOT allow public file storage for any evidence, working paper, or report.
- Do NOT soft-delete `audit_logs` — they must be permanently retained per retention policy.
- Do NOT return raw Eloquent models from API endpoints — always use API Resources.
- Do NOT hardcode role names or permission strings as raw strings in service/controller code — use Enums or constants.
- Do NOT dispatch notifications synchronously from within a database transaction.
- Do NOT skip policies — every controller action involving a model must call `$this->authorize()` or `Gate::authorize()`.

---

## 12. Environment Setup Reference

```env
APP_NAME=LedgerScope
APP_ENV=local
APP_KEY=               # Generate with php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ledgerscope
DB_USERNAME=ledgerscope_user
DB_PASSWORD=secret

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=ledgerscope-private
AWS_USE_PATH_STYLE_ENDPOINT=true    # true for MinIO in local dev

MAIL_MAILER=log    # Use log driver in local/test

LOG_CHANNEL=stack
LOG_LEVEL=debug
```

---

## 13. CI/CD Pipeline Steps (Reference)

```yaml
# .github/workflows/ci.yml (reference)
steps:
  - composer install --no-dev --optimize-autoloader
  - php artisan pint --test          # code style check
  - php artisan analyse              # Larastan level 8
  - php artisan test --parallel      # Pest
  - npm ci && npm run build
  - php artisan migrate --force
  - php artisan config:cache
  - php artisan route:cache
  - php artisan view:cache
  - php artisan event:cache
  - php artisan queue:restart
  - curl /health → assert status ok
```

---

*This AGENTS.md is the single source of truth for backend build order and conventions. If a PRD requirement conflicts with a rule in this document, the rule in this document takes precedence for backend implementation. Update this document when architecture decisions change.*
