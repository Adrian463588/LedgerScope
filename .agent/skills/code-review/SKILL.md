---
name: code-review
description: "Review code quality with technical rigor. Use before PRs, after implementing features, when claiming task completion. Includes edge case detection."
argument-hint: "[context] OR codebase"
version: 1.0
allowed-tools: Read, Glob, Grep, Bash
---

# Code Review

Guide proper code review practices emphasizing technical rigor, evidence-based claims, and verification over performative responses.

## Core Principle

**YAGNI**, **KISS**, **DRY** always. Technical correctness over social comfort.
**Be honest, brutal, straight to the point, concise.**

Verify before claiming. Ask before assuming. Evidence before claims.

## Operations

| Operation | Description |
|-----------|-------------|
| `(default)` | Review recent changes/PR |
| `codebase` | Full codebase scan & analysis |

## Practices

| Practice | When |
|----------|------|
| Receiving feedback | Unclear feedback, external reviewers, needs prioritization |
| Requesting review | After tasks, before merge, stuck on problem |
| Verification gates | Before any completion claim, commit, PR |
| Edge case scouting | After implementation, before review |

## Quick Decision Tree

```
SITUATION?
├─ Received feedback → STOP if unclear, verify if external, implement if trusted
├─ Completed work → Scout edge cases → Request code-reviewer subagent
└─ About to claim status → RUN verification command FIRST
```

## Process

### Receiving Feedback
Pattern: READ → UNDERSTAND → VERIFY → EVALUATE → RESPOND → IMPLEMENT

Rules:
- No performative agreement ("Great point!", "You're absolutely right!")
- No implementation before verification
- Push back with reasoning if wrong

### Requesting Review
1. Scout edge cases first using `@[skills/scout]`
2. Get SHAs: `BASE_SHA=$(git rev-parse HEAD~1)` and `HEAD_SHA=$(git rev-parse HEAD)`
3. Dispatch code-reviewer subagent with: WHAT, PLAN, BASE_SHA, HEAD_SHA, DESCRIPTION
4. Fix Critical immediately; Important before proceeding

### Verification Gate (Iron Law)
**NO COMPLETION CLAIMS WITHOUT FRESH VERIFICATION EVIDENCE**

Gate: IDENTIFY command → RUN → READ output → VERIFY confirms → THEN claim

## Red Flags

- "should"/"probably"/"seems to" without running verification
- Satisfaction before running tests
- Trusting agent reports without re-running

## References

- `references/code-review-reception.md` — Receiving feedback protocol
- `references/requesting-code-review.md` — Review request process
- `references/verification-before-completion.md` — Verification gates
- `references/edge-case-scouting.md` — Edge case detection
- `references/codebase-scan-workflow.md` — Full codebase scan
