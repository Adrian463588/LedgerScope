---
name: fix
description: "ALWAYS activate before fixing ANY bug, error, test failure, CI/CD issue, type error, lint, log error, UI issue."
argument-hint: "[issue] --auto|--review|--quick|--parallel"
version: 1.2
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Fixing

Unified skill for fixing issues of any complexity with intelligent routing.

## Flags

| Flag | When | Behavior |
|------|------|----------|
| `--auto` | Simple/moderate issues (default) | Auto-approve if score ≥ 9.5 |
| `--review` | Critical/production code | Pause for approval each step |
| `--quick` | Type errors, lint, trivial bugs | Fast debug → fix → review |
| `--parallel` | 2+ independent issues | Parallel agents per issue |

## Workflow

### Step 1: Mode Selection
If no `--auto` flag present, ask user to select mode.

### Step 2: Debug
- Activate `@[skills/debug]`
- Guess all possible root causes
- Run parallel searches to verify each hypothesis
- Create findings report

### Step 3: Complexity Assessment & Routing

| Level | Indicators | Workflow |
|-------|------------|----------|
| Simple | Single file, clear error, type/lint | `references/workflow-quick.md` |
| Moderate | Multi-file, root cause unclear | `references/workflow-standard.md` |
| Complex | System-wide, architecture impact | `references/workflow-deep.md` |
| Parallel | 2+ independent issues | Parallel agents per issue |

### Step 4: Fix Implementation & Verification
- Implement fix per selected workflow
- Run parallel verification to check no regressions
- Add validation to prevent future occurrences

### Step 5: Finalize (MANDATORY)
1. Report: confidence score, changes, files affected
2. Update `./docs` if changes warrant
3. Offer to commit via git

## Skill Activation Matrix

- **Always:** `@[skills/debug]`
- **When stuck:** `@[skills/problem-solving]`, `@[skills/sequential-thinking]`
- **Complex design:** `@[skills/brainstorm]`

## Output Format

```
Step 0: [Mode] selected — [Complexity] detected
Step 1: Root cause identified — [summary]
Step 2: Fix implemented — [N] files changed
Step 3: Tests [X/X passed]
Step 4: Review [score]/10 — [status]
Step 5: Complete
```

## References

- `references/complexity-assessment.md` — Classification criteria
- `references/workflow-quick.md` / `workflow-standard.md` / `workflow-deep.md`
- `references/workflow-ci.md` — GitHub Actions failures
- `references/workflow-test.md` — Test suite failures
- `references/workflow-types.md` — TypeScript type errors
