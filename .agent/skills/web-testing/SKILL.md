---
name: web-testing
description: "Web testing with Playwright, Vitest, k6. E2E, unit, integration, load, security, visual regression, accessibility. Use for test automation, Core Web Vitals, cross-browser."
argument-hint: "[test-type] [target]"
version: 3.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Web Testing

Comprehensive web testing: unit, integration, E2E, load, security, visual regression, accessibility.

## Quick Start

```bash
npx vitest run                         # Unit tests
npx playwright test                    # E2E tests
npx playwright test --ui               # E2E with UI mode
k6 run load-test.js                    # Load tests
npx @axe-core/cli https://example.com  # Accessibility audit
npx lighthouse https://example.com     # Performance audit
```

## Testing Strategy

| Model | Structure | Best For |
|-------|-----------|----------|
| Pyramid | Unit 70% > Integration 20% > E2E 10% | Monoliths |
| Trophy | Integration-heavy | Modern SPAs |
| Honeycomb | Contract-centric | Microservices |

## Reference Map

### Core Testing
- `references/unit-integration-testing.md` — Vitest, browser mode, AAA pattern
- `references/e2e-testing-playwright.md` — Fixtures, sharding, selectors
- `references/component-testing.md` — React/Vue/Angular component tests

### Infrastructure
- `references/test-data-management.md` — Factories, fixtures, seeding
- `references/database-testing.md` — Testcontainers, transactions
- `references/ci-cd-testing-workflows.md` — GitHub Actions, sharding
- `references/contract-testing.md` — Pact, MSW patterns

### Quality & Performance
- `references/performance-core-web-vitals.md` — LCP/CLS/INP, Lighthouse CI
- `references/visual-regression.md` — Screenshot comparison
- `references/test-flakiness-mitigation.md` — Stability strategies
- `references/accessibility-testing.md` — WCAG, axe-core

### Security
- `references/security-testing-overview.md` — OWASP Top 10
- `references/security-checklists.md` — Auth, API, headers

### API & Load
- `references/api-testing.md` — Supertest, GraphQL
- `references/load-testing-k6.md` — k6 patterns and scenarios

## CI/CD Integration

```yaml
jobs:
  test:
    steps:
      - run: npm run test:unit      # Gate 1: fast fail
      - run: npm run test:e2e       # Gate 2: after unit pass
      - run: npm run test:a11y      # Accessibility
      - run: npx lhci autorun       # Performance
```

## Scripts

```bash
# Initialize Playwright project
node ./scripts/init-playwright.js [--ct] [--dir <path>]

# Analyze test results
node ./scripts/analyze-test-results.js \
  --playwright test-results/results.json \
  --vitest coverage/vitest.json \
  --output markdown
```

## Anti-Patterns

- E2E tests for logic that belongs in unit tests
- Hardcoded `sleep()` instead of proper waits
- Tests that depend on execution order
- No assertions after async operations
