---
name: backend-development
description: "Build backend APIs, services, and infrastructure. Use for REST/GraphQL APIs, database models, authentication, middleware, background jobs, caching."
argument-hint: "[service or feature]"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Backend Development Guidelines

## When to Use

- Building REST or GraphQL API endpoints
- Designing database models and migrations
- Implementing authentication and authorization
- Adding middleware (logging, rate limiting, validation)
- Background jobs and queues
- Caching strategies
- Service integrations

## Architecture Principles

- **Layered architecture:** routes → controllers → services → repositories → database
- **Single responsibility:** each file/class owns one concern
- **Dependency injection:** pass dependencies rather than hardcoding
- **Error handling:** use try/catch at service boundaries, return typed errors
- **Validation:** validate at API boundary, trust validated data downstream

## API Design Checklist

- [ ] RESTful resource naming (`/users/:id`, not `/getUser`)
- [ ] Consistent error response shape `{ error, message, code }`
- [ ] HTTP status codes used correctly (200/201/400/401/403/404/500)
- [ ] Input validation before business logic
- [ ] Authentication middleware on protected routes
- [ ] Rate limiting on public endpoints
- [ ] Pagination for list endpoints

## File Structure

```
src/
├── routes/          # Route definitions and HTTP layer
├── controllers/     # Request handling, response formatting
├── services/        # Business logic
├── repositories/    # Database access layer
├── models/          # Data models and types
├── middleware/      # Auth, logging, validation
├── jobs/            # Background jobs
└── utils/           # Shared utilities
```

## Database Best Practices

- Use migrations for all schema changes
- Index foreign keys and frequently queried columns
- Avoid N+1 queries — use joins or batch fetching
- Transactions for multi-step operations
- Never store plain-text passwords (bcrypt/argon2)

## Security Checklist

- [ ] Parameterized queries (no string concatenation in SQL)
- [ ] Input sanitization
- [ ] JWT expiry and refresh rotation
- [ ] CORS configured for known origins only
- [ ] Secrets in environment variables, not code
- [ ] Dependency audit (`npm audit`)

## File Size Rule

Keep files under 200 lines. Extract:
- Complex business logic → dedicated service
- Reusable queries → repository methods
- Shared validation → validator modules

## References

- `references/api-design.md` — REST/GraphQL patterns
- `references/database.md` — ORM usage, migrations, queries
- `references/auth.md` — Authentication patterns
- `references/error-handling.md` — Error boundaries and typed errors
- `references/security.md` — Security hardening checklist
