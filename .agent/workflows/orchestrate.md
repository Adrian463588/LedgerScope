---
description: Parallel multi-agent execution for complex tasks. Minimum 3 specialist agents required.
---

# /orchestrate — Multi-Agent Orchestration

## Task
$ARGUMENTS

## Critical Rules

1. **Minimum 3 agents** — Fewer than 3 agents is delegation, not orchestration
2. **Plan before code** — Phase 1 planning must complete before Phase 2 implementation
3. **User approval gate** — Do NOT start Phase 2 without explicit user confirmation
4. **File ownership** — Each agent owns distinct files; no overlap allowed
5. **Context passing** — Every subagent receives full context: original request, decisions, prior agent work

## File Ownership Boundaries

| Domain | Agent | Owns |
|--------|-------|------|
| Frontend | frontend-specialist | `src/components/**`, `src/pages/**`, `src/styles/**` |
| Backend | backend-specialist | `src/api/**`, `src/services/**`, `src/middleware/**` |
| Database | database-architect | `src/models/**`, `migrations/**`, `seeds/**` |
| Tests | test-engineer | `**/*.test.*`, `**/*.spec.*`, `tests/**` |
| Docs | docs-manager | `docs/**`, `README.md` |

## Phase 1: Planning (Sequential — NO parallel agents)

**Step 1 — explorer-agent**
- Map codebase, identify affected areas, detect tech stack

**Step 2 — project-planner**
- Create `plans/YYMMDD-HHMM-{slug}/plan.md`
- Define phase breakdown and agent assignments
- Specify file ownership per agent

## Checkpoint

```
Plan ready: plans/YYMMDD-HHMM-{slug}/plan.md

Agents assigned:
- [agent-1]: [domain] — owns [files]
- [agent-2]: [domain] — owns [files]
- [agent-3]: [domain] — owns [files]

Approve to begin implementation? (Y/N)
```

**STOP here. Do not proceed until user approves.**

## Phase 2: Implementation (Parallel — after approval)

Spawn specialist agents by domain simultaneously. Each agent receives:
- Original user request (full text)
- Plan file contents
- Their specific file ownership boundary
- Summary of what Phase 1 discovered

Example parallel invocations:
- `frontend-specialist` → implements UI components per plan
- `backend-specialist` → implements API endpoints per plan
- `database-architect` → implements schema/migrations per plan

## Phase 3: Quality (Sequential)

**Step 1 — test-engineer**
- Run full test suite
- Write missing tests for implemented code
- Report pass/fail with coverage

**Step 2 — code-reviewer**
- Security, performance, maintainability review
- Flag issues by severity (critical/high/medium/low)
- Confirm no file ownership violations

## Exit Gate

Before marking orchestration complete, verify:
- [ ] Agent count >= 3
- [ ] All file ownership boundaries respected
- [ ] Tests passing
- [ ] Code review completed

## Output

| Deliverable | Location |
|-------------|----------|
| Plan | `plans/YYMMDD-HHMM-{slug}/plan.md` |
| Implementation | Per file ownership above |
| Test results | `plans/reports/test-report.md` |
| Review report | `plans/reports/review-report.md` |

## Agent Selection Matrix

| Task Type | Required Agents (minimum) |
|-----------|--------------------------|
| Web App | frontend-specialist, backend-specialist, test-engineer |
| API only | backend-specialist, database-architect, test-engineer |
| Full Stack | project-planner, frontend-specialist, backend-specialist, database-architect |
| Debug | debugger, explorer-agent, test-engineer |
| Security | security-auditor, backend-specialist, test-engineer |
