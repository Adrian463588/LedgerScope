---
name: simplify
description: "Simplify overly complex code, reduce cognitive load, apply KISS/YAGNI/DRY. Use when code has grown too large, has excessive abstraction, or is hard to reason about."
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
version: 1.0
priority: NORMAL
---

# Simplify

Reduce complexity, cognitive load, and unnecessary abstractions in code.

## When to Use

- Code file exceeds 200 lines
- Functions doing more than one thing
- Excessive abstraction layers
- Repeated patterns that could be unified
- Hard-to-follow control flow
- Over-engineered solutions for simple problems

## Process

1. **Audit** — identify complexity hotspots (long files, deep nesting, duplication)
2. **Classify** — categorize each issue: DRY violation, YAGNI excess, KISS failure
3. **Plan** — list changes with rationale; ensure no behavior change
4. **Refactor** — apply changes incrementally, one concern at a time
5. **Verify** — run tests to confirm behavior unchanged

## Rules

- Never change behavior while simplifying
- Run tests before and after each simplification
- If no tests exist, write them first
- Prefer deletion over abstraction
- Prefer flat over nested
- Prefer explicit over clever

## Simplification Patterns

| Problem | Solution |
|---------|----------|
| File > 200 lines | Split by concern into focused modules |
| Function > 20 lines | Extract sub-functions with clear names |
| Nested if/else > 3 levels | Early returns, guard clauses |
| Duplicate logic | Extract shared utility |
| Unused code | Delete it (YAGNI) |
| Premature abstraction | Inline until pattern is clear |

## Anti-Patterns

- Simplifying into a different abstraction that's equally complex
- Removing code that handles real edge cases
- Merging concerns that should stay separate
- Renaming things without fixing the underlying complexity

## Related Skills

- `@[skills/code-review]` — review after simplification
- `@[skills/debug]` — if simplification reveals hidden bugs
