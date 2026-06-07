---
description: Create implementation plan with multi-agent research. No code writing — planning only.
---

# /plan — Project Planning Mode

## Task
$ARGUMENTS

## Critical Rules

1. **NO CODE WRITING** — This command produces plan files only, zero implementation
2. **Socratic Gate** — Gather clarifying context before planning begins
3. **Multi-agent research** — Up to 3 parallel researcher agents investigate sub-topics
4. **Plan naming** — Use `plans/YYMMDD-HHMM-{slug}/` format from hook injection
5. **User approval required** — Present plan and wait for approval before any implementation

## Phase 1: Discovery (Sequential)

**Step 1 — explorer-agent**
- Map codebase structure: entry points, tech stack, existing patterns
- Identify affected files, modules, and dependencies
- Report: file tree summary, tech stack detected, relevant existing code

**Step 2 — Parallel researcher agents** (up to 3, based on task complexity)
- Assign each agent a distinct sub-topic derived from the task
- Examples: `researcher-1` → architecture approach, `researcher-2` → library options, `researcher-3` → security/performance considerations
- Each outputs a concise findings report to `plans/reports/researcher-YYMMDD-HHMM-{subtopic}.md`

**Step 3 — project-planner** (synthesizes all research)
- Read explorer-agent map and all researcher reports
- Create `plans/YYMMDD-HHMM-{slug}/plan.md` (overview, under 80 lines)
- Create phase files: `phase-01-*.md`, `phase-02-*.md`, etc.
- Each phase file: context links, overview, requirements, implementation steps, todo list, success criteria, file ownership

## Checkpoint

Present plan summary to user:

```
Plan created: plans/YYMMDD-HHMM-{slug}/plan.md

Phases:
- Phase 01: [name] — [brief description]
- Phase 02: [name] — [brief description]
...

Approve plan to begin implementation? (Y/N)
- Y: implementation can proceed
- N: specify changes and plan will be revised
```

**DO NOT proceed to any implementation without explicit user approval.**

## Output

| Deliverable | Location |
|-------------|----------|
| Plan overview | `plans/YYMMDD-HHMM-{slug}/plan.md` |
| Phase files | `plans/YYMMDD-HHMM-{slug}/phase-XX-*.md` |
| Research reports | `plans/reports/researcher-*.md` |
| Explorer map | `plans/reports/scout-report.md` |
