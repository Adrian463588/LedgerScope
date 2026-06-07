---
description: Bootstrap a new project from scratch — research, tech stack selection, plan, and scaffold.
---

# /bootstrap — New Project Bootstrap

## Task
$ARGUMENTS

## Critical Rules

1. **No scaffolding without approval** — Present tech stack and plan before writing any files
2. **Research first** — Understand the problem domain before choosing a stack (unless --fast)
3. **YAGNI** — Scaffold only what's needed to start; no speculative features
4. **Runnable output** — Project must run after scaffolding (install + start should work)

## Phase 1: Requirements (Sequential)

**brainstormer**
- Clarify project type, target users, key constraints (language, framework preferences, deployment target)
- Identify must-have features for v1
- Output: requirements summary (inline, no file)

## Phase 2: Research (Parallel, skip with --fast)

**Up to 2 researcher agents** investigating distinct sub-topics:
- Researcher 1 → tech stack options matching requirements (frameworks, libraries, tooling)
- Researcher 2 → project structure patterns, boilerplate conventions, similar open-source projects
- Each outputs findings to `plans/reports/researcher-YYMMDD-HHMM-{subtopic}.md`

## Phase 3: Plan (Sequential)

**project-planner**
- Select tech stack based on research + requirements
- Create `plans/YYMMDD-HHMM-{slug}/plan.md` (overview, under 80 lines)
- Create phase files covering: environment setup, core scaffold, configuration, initial feature

## Checkpoint

Present to user before scaffolding:

```
Tech Stack: [language / framework / tooling]
Plan: plans/YYMMDD-HHMM-{slug}/plan.md

Phases:
- Phase 01: [name] — [brief description]
- Phase 02: [name] — [brief description]
...

Proceed with scaffolding? (Y/N)
```

**DO NOT write any project files without explicit user approval.**

## Phase 4: Scaffold (Sequential)

**fullstack-developer**
- Implement Phase 01 (environment + scaffold) from the approved plan
- Ensure project installs and starts without errors

## Phase 5: Verify (Sequential)

**test-engineer**
- Run install and start commands
- Confirm the project boots without errors
- Report any issues back to fullstack-developer for immediate fix

## Output

| Deliverable | Location |
|-------------|----------|
| Plan overview | `plans/YYMMDD-HHMM-{slug}/plan.md` |
| Phase files | `plans/YYMMDD-HHMM-{slug}/phase-XX-*.md` |
| Research reports | `plans/reports/researcher-*.md` |
| Project scaffold | Project root directory |
