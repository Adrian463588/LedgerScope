---
name: databases
description: "Design schemas, write queries, optimize performance, manage migrations. Use for PostgreSQL, MySQL, SQLite, MongoDB, Redis, schema design, query optimization, indexing."
argument-hint: "[database or query task]"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Databases

Schema design, query optimization, migrations, and database operations across SQL and NoSQL systems.

## When to Use

- Designing or modifying database schemas
- Writing or optimizing queries
- Adding indexes for performance
- Managing migrations
- Debugging slow queries
- Choosing between SQL and NoSQL
- Setting up caching with Redis

## Schema Design Principles

- Use surrogate primary keys (UUID or auto-increment)
- Normalize to 3NF, denormalize only with profiling evidence
- Index foreign keys and columns used in WHERE/ORDER BY
- Use appropriate column types (avoid TEXT for structured data)
- Soft deletes with `deleted_at` instead of hard deletes when audit needed

## Query Optimization Checklist

- [ ] Use `EXPLAIN ANALYZE` to understand query plan
- [ ] Avoid `SELECT *` — select only needed columns
- [ ] Avoid N+1 — use JOINs or batch queries
- [ ] Use parameterized queries (prevent SQL injection)
- [ ] Paginate large result sets (`LIMIT`/`OFFSET` or cursor-based)
- [ ] Consider read replicas for heavy read workloads

## Index Strategy

```sql
-- Single column index
CREATE INDEX idx_users_email ON users(email);

-- Composite index (order matters — most selective first)
CREATE INDEX idx_orders_user_status ON orders(user_id, status);

-- Partial index for filtered queries
CREATE INDEX idx_active_users ON users(email) WHERE deleted_at IS NULL;
```

## Migration Rules

- Every schema change via migration file (never manual ALTER)
- Migrations must be reversible (include down migration)
- Test migrations on copy of production data before deploying
- Add indexes concurrently in PostgreSQL: `CREATE INDEX CONCURRENTLY`

## Common Pitfalls

- Missing indexes on foreign keys → slow JOINs
- Storing serialized JSON blobs instead of normalized columns
- Using OFFSET pagination at large offsets (use cursor-based instead)
- Transactions too broad → long lock hold times
- Not using connection pooling in production

## Redis Patterns

- Cache expiry: always set TTL
- Cache invalidation: key by resource ID + version
- Rate limiting: sliding window with sorted sets
- Session storage: `SET session:{id} {data} EX 3600`

## Debugging with psql

```bash
psql $DATABASE_URL -c "EXPLAIN ANALYZE SELECT ..."
psql $DATABASE_URL -c "\d+ tablename"   # table info
psql $DATABASE_URL -c "SELECT * FROM pg_stat_activity WHERE state = 'active';"
```

## References

- `references/postgresql.md` — PostgreSQL-specific patterns
- `references/migrations.md` — Migration tooling and patterns
- `references/query-optimization.md` — Indexing and query tuning
- `references/redis.md` — Caching and Redis patterns
