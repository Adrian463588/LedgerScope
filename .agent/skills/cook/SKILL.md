---
name: cook
description: "ALWAYS activate before implementing ANY feature, plan, or fix. End-to-end implementation with automatic workflow detection."
argument-hint: "[task|plan-path] [--interactive|--fast|--parallel|--auto|--no-test]"
version: 2.1
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Cook - Smart Feature Implementation

End-to-end implementation with automatic workflow detection.

**Principles:** YAGNI, KISS, DRY | Token efficiency | Concise reports

## Usage

```
@[skills/cook] <natural language task OR plan path> [--flag]
```

## Flags

| Flag | Behavior |
|------|----------|
| `--interactive` | Full workflow with user approval gates (default) |
| `--fast` | Skip research, scout→plan→code |
| `--parallel` | Multi-agent parallel execution |
| `--auto` | Auto-approve all steps |
| `--no-test` | Skip testing step |

## Smart Intent Detection

| Input Pattern | Mode |
|---------------|------|
| Path to `plan.md` or `phase-*.md` | code — execute existing plan |
| "fast", "quick" | fast |
| "trust me", "auto" | auto |
| 3+ features or "parallel" | parallel |
| Default | interactive |

## Workflow

```
[Intent Detection] → [Research?] → [Review] → [Plan] → [Review] → [Implement] → [Review] → [Test?] → [Finalize]
```

| Mode | Research | Testing | Review Gates |
|------|----------|---------|--------------|
| interactive | Yes | Yes | User approval each step |
| auto | Yes | Yes | Auto if score ≥ 9.5 |
| fast | No | Yes | User approval each step |
| parallel | Optional | Yes | User approval each step |
| no-test | Yes | No | User approval each step |
| code | No | Yes | User approval per plan |

## Finalize (MANDATORY — never skip)

1. Sync back all completed tasks/steps to phase files and update `plan.md`
2. Update `./docs` if changes warrant
3. Offer to commit changes via git

## Required Subagents

| Phase | Subagent |
|-------|----------|
| Research | researcher |
| Scout | `@[skills/scout]` |
| Plan | planner |
| Testing | tester, debugger |
| Review | code-reviewer |

## References

- `references/intent-detection.md` — Detection rules and routing
- `references/workflow-steps.md` — Detailed step definitions
- `references/review-cycle.md` — Interactive and auto review
