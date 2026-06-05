---
name: ledger-db-migration-safety
description: Guards LedgerScope PostgreSQL and Laravel migration correctness for accounting, audit logs, financial state, constraints, and data integrity. Use when creating or reviewing migrations, models, seeders, database constraints, or financial persistence.
---

# Ledger DB Migration Safety

Use this skill before changing database structure or financial persistence.

## Non-Negotiables

- Money columns use `DECIMAL(20,2)`; use `DECIMAL(20,4)` only for explicit multi-currency precision.
- Never use `float` or `double` for money columns or calculations.
- Primary keys are BIGINT/BIGSERIAL `id` for MVP.
- Audit-only tables have `created_at` only; `audit_logs` has no `updated_at`, no soft deletes, and is append-only.
- Financial state mutations must be wrapped in `DB::transaction()`.

## Constraint Pattern

- Name indexes as `idx_{table}_{columns}`.
- Name unique constraints as `{table}_{col1}_{col2}_unique`.
- Add database checks where PostgreSQL can enforce core invariants, especially non-negative debit/credit and period/month bounds.
- Use explicit service-layer guards for company access; do not rely on removable global scopes for financial isolation.

## Migration Review

Before finishing, verify:

- Every status/type column has a PHP enum and model cast.
- Company-scoped tables have `company_id` indexes.
- Unique constraints match `AGENTS.md` and `Infrastructure.md`.
- Soft deletes appear only on approved business-critical tables.
- Private file metadata never implies public storage access.
