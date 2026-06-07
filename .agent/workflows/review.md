---
description: Code quality review with severity ratings. Covers security, performance, maintainability, and edge cases.
---

# /review — Code Quality Review

## Task
$ARGUMENTS

## Critical Rules

1. **Read before judging** — Always read full file context, not just changed lines
2. **Severity ratings** — Every finding must have: critical / high / medium / low
3. **Actionable feedback** — Each issue must include what to fix and why
4. **No false positives** — Only flag real issues; do not nitpick style when it doesn't affect correctness

## Phase 1: Scope Identification (Sequential)

**explorer-agent**
- Identify changed files: `git diff --name-only HEAD~1` or per `$ARGUMENTS`
- Map dependencies of changed files (what calls them, what they call)
- Flag files with security surface area (auth, input handling, data access)

## Phase 2: Review (Sequential)

**code-reviewer** — full analysis across four dimensions:

### Security
- Hardcoded secrets, credentials, tokens
- SQL/command injection vectors
- Authentication and authorization gaps
- Insecure data exposure (logs, error messages, API responses)
- Dependency vulnerabilities

### Performance
- N+1 queries or redundant network calls
- Missing indexes or inefficient data access
- Unnecessary re-renders or blocking operations
- Bundle size regressions

### Maintainability
- Files exceeding 200 lines — flag for modularization
- DRY violations (duplicated logic)
- Unclear naming or missing comments on complex logic
- Dead code

### Edge Cases
- Null/undefined handling
- Empty array/string edge cases
- Concurrent access or race conditions
- Error handling gaps (uncaught exceptions, missing try/catch)

## Review Report Format

```markdown
## Code Review Report

### Summary
- Files reviewed: [N]
- Critical: [N] | High: [N] | Medium: [N] | Low: [N]

### Findings

#### [CRITICAL] [File:Line] — [Issue title]
**Problem:** [What is wrong]
**Risk:** [What could go wrong]
**Fix:** [Specific recommendation]

#### [HIGH] [File:Line] — [Issue title]
...

#### [MEDIUM] ...
...

#### [LOW] ...
...

### Approved Files (no issues)
- [file1]
- [file2]

### Recommendation
[Overall assessment: approve / approve with fixes / needs rework]
```

## Output

| Deliverable | Location |
|-------------|----------|
| Review report | `plans/reports/review-YYMMDD-HHMM-{slug}.md` |
