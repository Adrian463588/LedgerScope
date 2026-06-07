---
description: End-to-end feature implementation with automatic workflow detection. Use for any feature, fix, or task from natural language or plan path.
---

# /cook — Smart Feature Implementation

**Principles:** YAGNI, KISS, DRY | Token efficiency | No hooks — explicit context

## Task
$ARGUMENTS

## Usage
```
/cook <natural language task OR plan path> [--interactive|--fast|--parallel|--auto|--no-test]
```

## Flags
| Flag | Behavior |
|------|----------|
| *(none)* | `--interactive` default |
| `--interactive` | Full workflow with user approval at each gate |
| `--fast` | Skip research → scout→plan→code |
| `--parallel` | Multi-agent parallel execution |
| `--no-test` | Skip testing step |
| `--auto` | Auto-approve all steps (no stops) |

## Smart Intent Detection
| Input Pattern | Mode |
|---------------|------|
| Path to `plan.md` or `phase-*.md` | Execute existing plan |
| "fast", "quick" in args | `--fast` |
| "auto", "trust me" in args | `--auto` |
| 3+ features listed OR "parallel" | `--parallel` |
| "no test", "skip test" | `--no-test` |
| Default | `--interactive` |

## Workflow

```
[Scout] → [Research?] → [Plan] → [Gate] → [Implement] → [Gate] → [Test?] → [Gate] → [Review] → [Finalize]
```

## Phase 1: Scout (always)
- explorer-agent maps relevant codebase area
- Identify existing patterns to reuse

## Phase 2: Research (skip with --fast)
- researcher agent: investigate technical approach
- Max 3 research queries

## Phase 3: Plan
- project-planner creates implementation plan
- Output: `plans/YYMMDD-HHMM-{slug}/plan.md`
- GATE (--interactive): present plan, wait for approval

## Phase 4: Implement
- Assign to relevant specialist agent(s) based on task type:
  - Web/UI → fullstack-developer
  - API/Backend → fullstack-developer
  - Design → ui-ux-designer
  - Multi-component → orchestrator
- Pass: active plan path, file ownership, full context in every agent prompt

## Phase 5: Test (skip with --no-test)
- test-engineer: run tests, report failures
- If failures → debugger investigates → fix → re-run
- 100% pass required before continuing

## Phase 6: Review
- code-reviewer: quality gate
- Auto-approve if no critical issues
- GATE (--interactive): present findings

## Phase 7: Finalize (MANDATORY)
- project-manager: update plan.md status
- docs-manager: update docs/ if needed
- Run `.agent/scripts/checklist.py`

## Critical Rules
- NO implementation during Plan phase
- Always pass full context to subagents (no hook injection available)
- Never skip Finalize phase
- Report output: `plans/reports/{type}-YYMMDD-HHMM-{slug}.md`
