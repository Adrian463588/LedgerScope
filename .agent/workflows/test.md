---
description: Run tests and generate coverage report. Auto-executes on trigger.
turbo: true
---

// turbo

# /test — Test Runner

## Task
$ARGUMENTS

## Critical Rules

1. **No mocks to pass** — Never fake results or skip tests to make the build green
2. **Fix failures** — If tests fail, investigate root cause and fix; do not suppress
3. **Coverage threshold** — Report coverage; flag if below 80% on changed files
4. **Real data** — Use actual test fixtures, not hardcoded magic values

## Execution

**test-engineer**

### Step 1: Run full test suite
```bash
# Detect test framework and run appropriate command
npm test -- --coverage --passWithNoTests   # Jest/Vitest
python -m pytest --tb=short -q             # pytest
```

### Step 2: Analyze results
- Count: passed / failed / skipped
- Identify failing tests with full error messages
- Check coverage report — highlight files under 80%

### Step 3: Handle failures (if any)

**debugger** investigates failing tests:
- Determine if test is wrong or implementation is wrong
- Fix the correct side (prefer fixing implementation; update test only if test is incorrect)
- Re-run after fix

### Step 4: Report

```markdown
## Test Report

### Summary
- Total: [N] | Passed: [N] | Failed: [N] | Skipped: [N]
- Coverage: [N]% overall

### Failed Tests
- `[test name]` in `[file]`
  Error: [message]
  Fix applied: [description]

### Coverage Gaps (below 80%)
- `[file]`: [N]%

### Status
[PASS / FAIL — action required]
```

## Output

| Deliverable | Location |
|-------------|----------|
| Test report | `plans/reports/test-YYMMDD-HHMM.md` |
| Coverage HTML | `coverage/index.html` (if framework supports) |
