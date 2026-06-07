---
description: Systematic root cause analysis and fix. Traces errors through logs, stack traces, and code paths.
---

# /debug — Systematic Debugging

## Task
$ARGUMENTS

## Critical Rules

1. **Root cause first** — Do not patch symptoms; find why before fixing what
2. **Hypotheses** — Form ranked hypotheses before investigating any single cause
3. **Reproduce first** — Confirm the bug is reproducible before touching code
4. **Test after fix** — Every fix must be validated by test-engineer

## Phase 1: Discovery (Sequential)

**explorer-agent**
- Map the affected area: files, modules, call chains
- Identify entry points, data flow, recent changes (git log)
- Collect: error message, stack trace, affected routes/functions

## Phase 2: Analysis (Sequential)

**debugger**

1. **Gather context**
   - Full error message and stack trace
   - Steps to reproduce
   - Expected vs actual behavior
   - Recent code changes in affected area (`git log --oneline -20`)

2. **Form hypotheses** (ranked by likelihood)
   - List 3–5 possible root causes
   - Order from most to least likely

3. **Investigate systematically**
   - Test each hypothesis with evidence (logs, code inspection, data traces)
   - Eliminate causes with proof
   - Identify confirmed root cause

4. **Implement fix**
   - Fix the root cause, not the symptom
   - Keep change minimal and focused
   - Add inline comment explaining why the fix is correct

## Phase 3: Validation (Sequential)

**test-engineer**
- Run full test suite in affected area
- Write regression test to prevent recurrence
- Confirm fix resolves the original reproduction case

## Debug Report Format

```markdown
## Debug Report: [Issue]

### Symptom
[What was observed]

### Reproduction Steps
1. [Step 1]
2. [Step 2]

### Information Gathered
- Error: `[message]`
- File: `[path:line]`
- Recent changes: [commit hashes]

### Hypotheses
1. [Most likely] — [evidence for/against]
2. [Second] — [evidence for/against]
3. [Third] — [evidence for/against]

### Root Cause
[Confirmed explanation of why this happened]

### Fix Applied
- File: `[path]`
- Change: [description]

### Prevention
- Regression test added: `[test file]`
- [Any structural recommendations]
```

## Output

| Deliverable | Location |
|-------------|----------|
| Debug report | `plans/reports/debug-YYMMDD-HHMM-{slug}.md` |
| Regression test | Appropriate test file in codebase |
