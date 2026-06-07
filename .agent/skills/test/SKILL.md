---
name: test
description: "Run unit, integration, e2e, and UI tests. Use for test execution, coverage analysis, build verification, and QA reports."
argument-hint: "[context] OR ui [url]"
version: 1.0
allowed-tools: Read, Write, Glob, Grep, Bash
---

# Testing & Quality Assurance

Comprehensive testing covering unit, integration, e2e, UI/visual, coverage analysis, and QA reporting.

## Core Principle

**NEVER IGNORE FAILING TESTS.** Fix root causes, not symptoms. No mocks/cheats/tricks to pass builds.

## When to Use

- After implementation: validate new features or bug fixes
- Coverage checks: ensure coverage meets project thresholds (80%+)
- UI verification: visual regression, responsive layout, accessibility
- Build validation: CI/CD compatibility
- Pre-commit/push: final quality gate

## Operations

| Operation | Description |
|-----------|-------------|
| `(default)` | Run unit/integration/e2e tests |
| `ui [url]` | Run UI tests on a website |

## Workflows

### 1. Code Testing
Execute test suites, analyze results, generate coverage.

Supported: JS/TS (Jest/Vitest/Mocha), Python (pytest), Go, Rust, Flutter.

```bash
# JavaScript/TypeScript
npm test
npm run test:coverage

# Python
pytest
pytest --cov=src --cov-report=html

# Go
go test ./...

# Rust
cargo test

# Flutter
flutter test
```

**Load:** `references/test-execution-workflow.md`

### 2. UI Testing
Browser-based visual testing via browser automation. Screenshots, responsive checks, accessibility audits, form automation, console error collection.

**Load:** `references/ui-testing-workflow.md`

## Quality Standards

- Coverage threshold: 80% minimum (statements, branches, functions)
- All tests must pass before claiming complete
- Fix flaky tests — never skip or suppress
- Test error paths, not just happy paths

## QA Report Format

```markdown
## Test Results
- Total: X | Passed: X | Failed: X | Skipped: X
- Coverage: X% (statements) / X% (branches)

## Failed Tests
- [test name]: [failure reason]

## Recommendations
- [actionable next steps]
```

**Load:** `references/report-format.md`

## Anti-Patterns

- `test.skip` or `xit` left in codebase
- Mocking entire modules to avoid testing real behavior
- `expect(true).toBe(true)` placeholder tests
- Not testing error/edge cases
