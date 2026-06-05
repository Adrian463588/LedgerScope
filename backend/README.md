# LedgerScope Backend

**Accounting, Financial Analysis & Audit Management Platform**

Laravel 13 · PostgreSQL 17 · Redis 7 · PHP 8.4

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 |
| Language | PHP 8.4 |
| Database | PostgreSQL 17 |
| Cache / Queue | Redis 7 |
| Queue Monitor | Laravel Horizon (Linux / Docker) |
| Auth | Laravel Sanctum (SPA session) |
| Testing | Pest PHP 3 |
| Static Analysis | Larastan 2 (level 8) |
| Code Style | Laravel Pint (PSR-12) |

---

## Local Development (Docker)

```bash
# 1. Clone and enter backend directory
git clone <repo>
cd LedgerScope/backend

# 2. Copy env
cp .env.example .env

# 3. Start services
docker compose up -d

# 4. Install dependencies (inside container or locally with PHP 8.4)
composer install

# 5. Generate app key
php artisan key:generate

# 6. Run migrations + seeders
php artisan migrate --seed

# 7. Start dev server
php artisan serve
```

> Test DB runs on port **5433** — matches `phpunit.xml` config.

---

## Development Workflow (RTK AI + Caveman)

Follow the **Red → Ticket → Keep** cycle:

1. **RED** — Write a failing Pest test first
2. **GREEN** — Implement minimum code to pass
3. **KEEP** — Run gate before committing:

```bash
# Code style
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse

# Tests
php artisan test --parallel
```

---

## Architecture

```
app/
├── Actions/          # Single-purpose action classes
├── Enums/            # Backed PHP enums (Accounting, Audit, Common, Reporting)
├── Events/           # Domain events → WriteAuditLog listener
├── Http/
│   ├── Controllers/Api/V1/   # Thin controllers — call Services only
│   ├── Middleware/
│   └── Requests/             # Form Request validation
├── Models/           # Eloquent models (no business logic)
├── Services/
│   ├── Accounting/   # JournalService, TrialBalanceService, StatementBuilderService
│   ├── Audit/        # EngagementService, WorkingPaperService, FindingService
│   └── Reporting/    # ReportGeneratorService
├── Support/
└── ValueObjects/     # Money (bcmath — never float)
```

### Absolute Rules (from AGENTS.md)
- **Never use `float` for money** — use `Money` ValueObject with bcmath
- **No business logic in Controllers** — delegate to Services/Actions
- **Every financial mutation** → `DB::transaction()`
- **Posted journals are immutable** — reversal only
- **Audit logs are append-only** — no update/delete

---

## API Endpoints (v1)

| Domain | Prefix |
|--------|--------|
| Auth | `POST /api/v1/auth/{login,logout,me,...}` |
| Company | `GET/POST/PUT/DELETE /api/v1/companies` |
| Fiscal Year | `/api/v1/companies/{id}/fiscal-years` |
| Accounts (COA) | `/api/v1/companies/{id}/accounts` |
| Journals | `/api/v1/companies/{id}/journals` |
| Trial Balance | `/api/v1/companies/{id}/trial-balances` |
| Financial Statements | `/api/v1/companies/{id}/financial-statements` |
| Engagements | `/api/v1/engagements` |
| Reports | `/api/v1/companies/{id}/reports` |

---

## Queue Segments (Horizon)

| Queue | Purpose | Timeout |
|-------|---------|---------|
| `imports` | COA & Journal import jobs | 300s |
| `reports` | PDF/XLSX report generation | 600s |
| `notifications` | Email / push notifications | 30s |
| `default` | General tasks | 60s |

---

## Production Deployment

```bash
# Build and push image (automated via GitHub Actions on main)
docker compose -f docker-compose.prod.yml up -d

# Run migrations in production
docker exec ledgerscope_app php artisan migrate --force

# Run seeders (first deploy only)
docker exec ledgerscope_app php artisan db:seed
```

### Required GitHub Secrets

```
DB_DATABASE
DB_USERNAME
DB_PASSWORD
APP_KEY
```

---

## Testing

```bash
# Run all tests
php artisan test --parallel

# Run specific feature group
php artisan test tests/Feature/Accounting/
php artisan test tests/Feature/Audit/
php artisan test tests/Feature/Reporting/

# Run with filter
php artisan test --filter JournalServiceTest
```

Current test coverage: **57 tests / 57 passed**

---

## Build Phases

| Phase | Status | Description |
|-------|--------|-------------|
| 1 Auth & RBAC | ✅ Done | Login, roles, permissions, audit log |
| 2 Company | ✅ Done | Multi-company workspace, user assignment |
| 3 Fiscal Year | ✅ Done | Periods, quarters, checklists, period lock |
| 4 Chart of Accounts | ✅ Done | Hierarchical COA, import |
| 5 Journal Engine | ✅ Done | Double-entry, lifecycle, reversal, Money VO |
| 6 Trial Balance | ✅ Done | Posted-line aggregation, balanced check |
| 7 Financial Statements | ✅ Done | Income statement, balance sheet |
| 8 Audit & Engagement | ✅ Done | Engagement lifecycle, working papers, findings |
| 9 Reporting | ✅ Done | Report queue, status tracking |
| 10 Horizon / Infra | ✅ Done | Queue config, supervisor, nginx, opcache |
| 11 CI/CD | ✅ Done | GitHub Actions — lint → test → Docker build |
