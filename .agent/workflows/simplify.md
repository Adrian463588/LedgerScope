---
description: Reduce complexity in recently changed code. Applies DRY/KISS, removes redundancy, improves readability without changing behavior.
turbo: true
---

# /simplify — Code Simplification

## Task
$ARGUMENTS

## Critical Rules

1. **No behavior changes** — Simplification only; zero functional modifications
2. **Focused scope** — Target recently changed files unless specific files are provided
3. **Reviewer validates** — code-reviewer confirms no regressions introduced
4. **DRY/KISS/YAGNI** — Remove duplication, reduce complexity, delete unused code

## Phase 1: Simplify (Sequential)

**code-simplifier**
- Identify recently modified files (via `git diff --name-only HEAD~5`) or use files from task arguments
- Apply simplifications:
  - Extract duplicated logic into shared utilities
  - Remove dead code, unused variables, redundant comments
  - Flatten unnecessary nesting
  - Replace verbose patterns with idiomatic equivalents
- Keep each change minimal and targeted

## Phase 2: Review (Sequential)

**code-reviewer**
- Verify all simplifications preserve original behavior
- Check for any inadvertent logic changes
- Confirm code is more readable than before
- Flag any concerns back to code-simplifier for revision

## Output

| Deliverable | Location |
|-------------|----------|
| Simplified code | Affected source files (in-place) |
