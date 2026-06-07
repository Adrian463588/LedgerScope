---
name: debug
description: "Debug systematically with root cause analysis before fixes. Use for bugs, test failures, unexpected behavior, performance issues, CI/CD failures, database diagnostics."
argument-hint: "[error or issue description]"
version: 4.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Debugging & System Investigation

## Core Principle

**NO FIXES WITHOUT ROOT CAUSE INVESTIGATION FIRST**

Random fixes waste time and create new bugs. Find root cause, fix at source, validate at every layer, verify before claiming success.

## When to Use

- **Code-level:** Test failures, bugs, unexpected behavior, build failures
- **System-level:** Server errors, CI/CD pipeline failures, performance degradation, database issues
- **Always:** Before claiming work complete

## Techniques

| Technique | When to Load | Reference |
|-----------|-------------|-----------|
| Systematic Debugging | Any bug requiring investigation | `references/systematic-debugging.md` |
| Root Cause Tracing | Error deep in call stack | `references/root-cause-tracing.md` |
| Defense-in-Depth | After finding root cause | `references/defense-in-depth.md` |
| Verification | Before claiming complete | `references/verification.md` |
| Investigation Methodology | System-level issues | `references/investigation-methodology.md` |
| Log & CI/CD Analysis | CI/CD failures, server errors | `references/log-and-ci-analysis.md` |
| Performance Diagnostics | Slow queries, high latency | `references/performance-diagnostics.md` |

## Quick Decision

```
Code bug       → systematic-debugging.md (Phase 1-4)
  Deep in stack  → root-cause-tracing.md
  Found cause    → defense-in-depth.md
  Claiming done  → verification.md

System issue   → investigation-methodology.md
  CI/CD failure  → log-and-ci-analysis.md
  Slow system    → performance-diagnostics.md
```

## Tools

- **Database:** `psql` for PostgreSQL diagnostics
- **CI/CD:** `gh` CLI for GitHub Actions logs
- **Codebase:** `@[skills/docs-seeker]` for package docs
- **Stuck:** Activate `@[skills/problem-solving]`

## Red Flags (Stop and Investigate)

- "Quick fix for now, investigate later"
- "It's probably X, let me fix that"
- "Should work now" / "Seems fixed"
- "Tests pass, we're done"
