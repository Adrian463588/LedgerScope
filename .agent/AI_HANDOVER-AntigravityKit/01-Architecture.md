# AntigravityKit — Architecture
**Read this before implementing anything.**

---

## Target Folder Structure

```
.agent/                              # Antigravity's native folder (equivalent to .claude/)
├── ARCHITECTURE.md                  # Central registry — lists all agents, skills, workflows
├── rules/
│   └── GEMINI.md                   # Master rules engine (trigger: always_on)
├── AI_HANDOVER-AntigravityKit/     # Onboarding docs for AI agents
│   ├── 00-README.md
│   ├── 01-Architecture.md
│   ├── 02-Agents-and-Skills.md
│   ├── 03-Workflows-and-Orchestration.md
│   └── 04-Extension-Guide.md
├── agents/                         # ~16 specialist agent .md files
│   ├── project-planner.md
│   ├── researcher.md
│   ├── debugger.md
│   ├── code-reviewer.md
│   ├── test-engineer.md
│   ├── docs-manager.md
│   ├── git-manager.md
│   ├── fullstack-developer.md
│   ├── brainstormer.md
│   ├── code-simplifier.md
│   ├── journal-writer.md
│   ├── project-manager.md
│   ├── ui-ux-designer.md
│   ├── orchestrator.md             # NEW — multi-agent coordinator
│   └── explorer-agent.md           # NEW — codebase exploration
├── skills/                         # ~30 skill directories
│   └── [skill-name]/
│       ├── SKILL.md                # Index + content map
│       ├── sections/               # Detailed guides
│       ├── examples/               # Code examples
│       └── scripts/                # Optional Python scripts
├── workflows/                      # ~10 slash commands (filename = command)
│   ├── plan.md                    → /plan
│   ├── research.md                → /research
│   ├── orchestrate.md             → /orchestrate
│   ├── debug.md                   → /debug
│   ├── review.md                  → /review
│   ├── test.md                    → /test
│   ├── deploy.md                  → /deploy
│   ├── docs.md                    → /docs
│   ├── brainstorm.md              → /brainstorm
│   └── commit.md                  → /commit
└── scripts/                        # Validation scripts
    ├── checklist.py               # Security + lint + schema + tests + UX
    └── verify_all.py              # Full suite + Lighthouse + E2E
```

---

## Component Mapping: ClaudeKit → AntigravityKit

| ClaudeKit | AntigravityKit | Action |
|-----------|----------------|--------|
| `.claude/` | `.agent/` | Rename root |
| `CLAUDE.md` | `rules/GEMINI.md` | Rewrite with tier system |
| `.claude/agents/` | `.agent/agents/` | Adapt (remove hook refs) |
| `.claude/skills/` | `.agent/skills/` | Prune to ~30 |
| `.claude/rules/*.md` | Embedded in `GEMINI.md` | Flatten into Tier 0/1/2 |
| `.claude/hooks/` | **DROP** | No equivalent |
| `.claude/scripts/` | `.agent/scripts/` | Port validation only |
| `metadata.json` (358KB) | `ARCHITECTURE.md` | Lightweight registry |
| command-archive | `workflows/` | Rename + adapt |
| AI_HANDOVER (old) | `AI_HANDOVER-AntigravityKit/` | Update + expand |

---

## GEMINI.md Structure (Entry Point)

```markdown
---
trigger: always_on
---

# GEMINI.md — AntigravityKit Rules

## CRITICAL: Read → Understand → Apply
Before ANY action: read ARCHITECTURE.md for system map.

## REQUEST CLASSIFIER
- Simple question → answer directly
- Code task → route to specialist agent
- Multi-component task → /orchestrate workflow
- Slash command → load matching workflow file

## INTELLIGENT AGENT ROUTING
[task keyword → agent mapping table]

## TIER 0 — Universal (Always Active)
- Respond in user's language
- YAGNI / KISS / DRY principles
- Kebab-case file naming
- 200-line file size limit
- Report naming: plans/reports/{type}-YYMMDD-HHMM-{slug}.md
- Plan naming: plans/YYMMDD-HHMM-{slug}/

## TIER 1 — Code Rules (When Coding)
- Socratic Gate: ask 3+ questions before complex tasks
- Project routing: Web→frontend, API→backend, Mobile→mobile
- Run checklist.py after implementation

## TIER 2 — Specialist Rules
- Defined per-agent in individual .md files
```

---

## ARCHITECTURE.md Structure (Registry)

A lightweight markdown file (~80 lines) listing:
- All agents with one-line descriptions
- All skills with categories and one-line descriptions
- All workflows with slash command names
- Scripts inventory

---

## Key Design Principles

1. **Context-rich prompts** — no hook injection means orchestrator must pass all context explicitly
2. **Selective skill loading** — agents load only relevant skill sections (Content Map pattern)
3. **Tier-based rules** — Tier 0 always runs; Tier 1 on code; Tier 2 in specialist agents
4. **Slash command auto-registration** — filename = command name (no config needed)
5. **Agent boundary enforcement** — each agent owns specific file types; violating = re-route
