---
description: Targeted bug fix with minimal side effects. Finds root cause, applies focused fix, validates with tests.
turbo: true
---

# /fix — Bug Fix

## Task
$ARGUMENTS

## Critical Rules

1. **Root cause first** — Identify why before fixing what; no patching symptoms
2. **Minimal change** — Smallest possible diff that fixes the issue
3. **No regressions** — test-engineer must confirm fix before done
4. **No mocks to pass tests** — real validation only

## Phase 1: Discovery (Sequential)

**explorer-agent**
- Locate the affected file(s), function(s), and call paths
- Check recent git changes in the area (`git log --oneline -10 -- <file>`)
- Identify the root cause from the task description and code context

## Phase 2: Fix (Sequential)

**fullstack-developer**
- Implement the minimal fix targeting the root cause
- Add inline comment explaining why the fix is correct if logic is non-obvious
- Do NOT refactor surrounding code — fix only what's broken

## Phase 3: Validation (Sequential)

**test-engineer**
- Run existing tests in the affected area
- Write a regression test if one doesn't exist
- Confirm fix resolves the original issue

## Output

| Deliverable | Location |
|-------------|----------|
| Fix applied | Affected source file(s) |
| Regression test | Appropriate test file in codebase |
