# AntigravityKit — Workflows and Orchestration
**Reference this when implementing Phase 5 (workflows) and understanding hook replacement.**

---

## How Workflows Work in Antigravity

- Workflow files live in `.agent/workflows/`
- Filename (minus `.md`) **auto-registers as a slash command** — no config needed
- `// turbo` annotation at top = auto-execute without user confirmation prompt
- Workflows use `$ARGUMENTS` placeholder for user-provided input

**Example:** `.agent/workflows/plan.md` → `/plan [arguments]`

---

## Workflow File Format

```markdown
---
description: One-line description of what this workflow does
---

# Workflow Title

## Task
$ARGUMENTS

## Critical Checks
[Validation rules before starting]

## Phase 1: [Name] (Sequential)
- Step 1: ...
- Step 2: ...

## Checkpoint
[User approval required before Phase 2]

## Phase 2: [Name] (Parallel)
- Agent A: handles X
- Agent B: handles Y

## Output
[What this workflow produces]
```

---

## ~10 Workflows to Create

| File | Command | Description | Turbo? |
|------|---------|-------------|--------|
| `plan.md` | `/plan` | Create implementation plan (NO CODE during planning) | No |
| `research.md` | `/research` | Multi-source research + report | No |
| `orchestrate.md` | `/orchestrate` | Parallel multi-agent execution (min 3 agents) | No |
| `debug.md` | `/debug` | Systematic debugging + root cause analysis | No |
| `review.md` | `/review` | Code quality review + edge case detection | No |
| `test.md` | `/test` | Run tests + coverage report | Yes |
| `deploy.md` | `/deploy` | Deployment workflow | No |
| `docs.md` | `/docs` | Documentation update | No |
| `brainstorm.md` | `/brainstorm` | Socratic ideation (ask 3+ questions first) | No |
| `commit.md` | `/commit` | Conventional commit + push | Yes |

**Reference existing workflows:** `G:\My Drive\KitBuild\ClaudeKit\.agent\workflows\`

---

## /orchestrate Pattern (Most Important)

The orchestrate workflow enforces the 2-phase protocol:

```
PHASE 1: PLANNING (Sequential — 2 agents max)
  - project-planner: analyzes requirements, creates PLAN.md
  - explorer-agent: maps codebase structure

CHECKPOINT: Present plan to user → wait for approval

PHASE 2: IMPLEMENTATION (Parallel — 3+ agents)
  - backend-specialist: owns api/**, server/**
  - frontend-developer: owns components/**, pages/**
  - test-engineer: owns *.test.*, *.spec.*
  - docs-manager: owns docs/**

PHASE 3: VERIFICATION
  - test-engineer: runs test suite
  - code-reviewer: quality gate
```

---

## Hook Replacement Strategy

ClaudeKit hooks inject context automatically. In AntigravityKit, the **orchestrator must inject all context manually** in every subagent prompt.

### What hooks did → What to do instead

| ClaudeKit Hook | Manual Replacement |
|----------------|-------------------|
| Plan context injection (`## Plan Context`) | Orchestrator reads `plans/` dir, finds active plan, includes full path in every subagent prompt: `"Active plan: plans/YYMMDD-slug/plan.md"` |
| Session info (`DateTime`, `CWD`, `OS`) | GEMINI.md Tier 0 rule: "Always read ARCHITECTURE.md first" provides static system context |
| Report naming (`## Naming` section) | Static pattern in GEMINI.md Tier 0: `plans/reports/{type}-YYMMDD-HHMM-{slug}.md` |
| Skill discovery | REQUEST CLASSIFIER in GEMINI.md routes task → agent → relevant skills explicitly |
| Privacy-block (`@@PRIVACY_PROMPT@@`) | Not needed — no equivalent in Antigravity |
| UserPromptSubmit hooks | GEMINI.md `trigger: always_on` serves same purpose |

### Orchestrator prompt template (use in every subagent call)

```
You are [agent-name]. Your task: [specific task].

Context:
- Project: [project name]
- Active plan: [path to plan file, if any]
- Previous work: [summary of what other agents did]
- File ownership: [files this agent is responsible for]
- Report output: plans/reports/{type}-YYMMDD-HHMM-{slug}.md

Load these skills: [skill-name-1], [skill-name-2]

Instructions:
[Detailed task instructions]
```

---

## /plan Workflow Pattern

```
PHASE 1: RESEARCH (Parallel — up to 3 researcher agents)
  Each researcher gets specific topic to investigate

PHASE 2: PLANNING (Sequential)
  project-planner synthesizes research → creates phased plan files

OUTPUT:
  plans/YYMMDD-HHMM-{slug}/
  ├── plan.md          (overview, ~80 lines)
  ├── phase-01-*.md
  ├── phase-02-*.md
  └── ...

RULES:
  - NO CODE during /plan
  - User approves plan before implementation starts
  - Each phase file: context, requirements, steps, todos, risks
```

---

## Agent Boundary Enforcement

Define in each workflow which agent owns which files:

```
frontend-developer  → components/**, pages/**, styles/**
backend-specialist  → api/**, server/**, routes/**
database-architect  → prisma/**, migrations/**, schema/**
test-engineer       → **/*.test.*, **/*.spec.*, e2e/**
docs-manager        → docs/**, *.md (except code files)
```

**Violation rule:** If an agent attempts to write outside its domain → STOP → re-route to correct agent.
