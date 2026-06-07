---
trigger: always_on
---

# GEMINI.md — AntigravityKit Rules Engine

## CRITICAL: Read → Understand → Apply

Before ANY action: read `.agent/ARCHITECTURE.md` for the full system map (agents, skills, workflows).

---

## REQUEST CLASSIFIER

Route every request through this decision tree:

- **Simple question / concept** → answer directly, no agents needed
- **Single file / focused feature** → route to one specialist agent
- **Multi-component / cross-cutting task** → use `/orchestrate` workflow
- **Slash command** → load matching file from `.agent/workflows/`
- **Research needed** → route to `researcher` agent

---

## INTELLIGENT AGENT ROUTING

| Task Keywords | Route To |
|---------------|----------|
| plan, implement, feature, build, create | `project-planner` |
| bug, error, fix, crash, debug, broken | `debugger` |
| research, find, investigate, compare | `researcher` |
| review, quality, audit, check code | `code-reviewer` |
| test, coverage, spec, e2e, unit | `test-engineer` |
| docs, documentation, readme, guide | `docs-manager` |
| git, commit, push, branch, merge | `git-manager` |
| frontend, UI, component, page, React | `fullstack-developer` |
| design, UX, interface, wireframe | `ui-ux-designer` |
| brainstorm, ideas, explore, concept | `brainstormer` |
| multi-agent, complex, parallel, orchestrate | `orchestrator` |
| codebase, search, explore, map, find files | `explorer-agent` |

---

## TIER 0 — Universal Rules (Always Active)

- Respond in the user's language at all times
- Follow YAGNI / KISS / DRY principles in every decision
- Use kebab-case for all file names (no exceptions)
- Keep all code files under 200 lines — split if larger
- Report path pattern: `plans/reports/{type}-YYMMDD-HHMM-{slug}.md`
- Plan path pattern: `plans/YYMMDD-HHMM-{slug}/`
- ALWAYS read `ARCHITECTURE.md` before starting any task
- Sacrifice grammar for concision in all reports
- List unresolved questions at end of every report
- Never commit `.env`, API keys, or credentials

---

## TIER 1 — Code Rules (When Writing Code)

- **Socratic Gate**: for any complex task, ask 3+ clarifying questions BEFORE writing code
- Project type routing:
  - Web app → `fullstack-developer` agent
  - API / backend only → `backend-development` skill
  - Mobile → `mobile-development` skill
- After every implementation: run `.agent/scripts/checklist.py`
- Handle all edge cases and error scenarios
- Use try/catch error handling throughout
- Security: validate all inputs, no hardcoded secrets, no sensitive data in logs
- Do not create new "enhanced" files — update existing files directly
- Real implementations only — no mocks, no simulations, no temporary hacks

---

## TIER 2 — Specialist Rules (Per Agent)

- Defined in individual agent `.md` files under `.agent/agents/`
- Tier 2 rules **override** Tier 1 for that agent's specific domain
- Example: `ui-ux-designer` has design quality gates; `test-engineer` owns all test files exclusively
- Never modify files outside an agent's declared file ownership

---

## ORCHESTRATION PROTOCOL (No Hooks)

AntigravityKit has no hooks — ALL context must be passed explicitly in every subagent prompt.

**Mandatory subagent prompt template:**
```
You are [agent-name]. Your task: [specific task].

Context:
- Project: [project name]
- Active plan: [path to plan file, if any]
- Previous work: [summary of what other agents completed]
- File ownership: [glob patterns this agent owns]
- Report output: plans/reports/{type}-YYMMDD-HHMM-{slug}.md

Load these skills: [skill-name-1], [skill-name-2]

Instructions:
[Detailed task instructions]
```

**Two-phase execution (mandatory for /orchestrate):**
1. Plan phase → present plan to user → wait for approval
2. Execute phase → parallel agents with explicit file ownership

**Minimum 3 agents** for true orchestration.

---

## SCRIPTS

- `.agent/scripts/checklist.py` — run after every implementation (security + lint + schema + tests + UX)
- `.agent/scripts/verify_all.py` — full validation suite (Lighthouse + E2E + all checks)
