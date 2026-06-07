# AntigravityKit — Agents and Skills
**Reference this when implementing Phase 3 (agents) and Phase 4 (skills).**

---

## Agent Format

Every agent file follows this pattern:

```yaml
---
name: agent-name
description: One-liner — when to use this agent, trigger keywords
tools: Read, Grep, Glob, Edit, Write, Bash
model: inherit
skills: skill1, skill2, skill3
---

# Agent Title — Role Summary

## Philosophy
[Core mindset and approach]

## Responsibilities
[What this agent does]

## Process
[Step-by-step workflow]

## Anti-Patterns
[What this agent must NOT do]

## File Ownership
[Which file types/paths this agent owns exclusively]
```

---

## Agent Adaptation Table

| ClaudeKit Agent | AntigravityKit Agent | Key Changes |
|-----------------|---------------------|-------------|
| `planner.md` | `project-planner.md` | Remove `set-active-plan.cjs` script ref; remove hook-injected plan path; use explicit plan dir convention |
| `researcher.md` | `researcher.md` | Keep Gemini CLI toggle as optional; remove Claude-specific Task refs |
| `debugger.md` | `debugger.md` | Remove `TaskCreate`/`TaskUpdate`; keep `psql`, `gh`, `repomix` tool refs |
| `code-reviewer.md` | `code-reviewer.md` | Replace `/scout` skill call with direct Grep/Glob instructions |
| `tester.md` | `test-engineer.md` | Rename; remove Claude Native Task management |
| `docs-manager.md` | `docs-manager.md` | Minimal — update path refs |
| `git-manager.md` | `git-manager.md` | Remove Claude Code CLI assumptions |
| `fullstack-developer.md` | `fullstack-developer.md` | Remove Task orchestration wiring |
| `brainstormer.md` | `brainstormer.md` | Minimal changes |
| `code-simplifier.md` | `code-simplifier.md` | Minimal changes |
| `journal-writer.md` | `journal-writer.md` | Minimal changes |
| `project-manager.md` | `project-manager.md` | Remove `TaskCreate`/`TaskUpdate`/`TaskList` |
| `ui-ux-designer.md` | `ui-ux-designer.md` | Minimal changes |
| `mcp-manager.md` | **DROP** | Claude Code MCP-specific — no equivalent |
| *(new)* | `orchestrator.md` | Port from `G:\My Drive\KitBuild\ClaudeKit\.agent\agents\orchestrator.md` |
| *(new)* | `explorer-agent.md` | Port from old transformation |

**Source agents:** `G:\My Drive\KitBuild\Biz\General\.claude\agents\`
**Reference agents:** `G:\My Drive\KitBuild\ClaudeKit\.agent\agents\`

---

## What to Remove from Every ClaudeKit Agent

- References to `TaskCreate`, `TaskUpdate`, `TaskList`, `TaskGet`
- References to `SendMessage` (team coordination — Claude-specific)
- References to hooks (`@@PRIVACY_PROMPT@@`, hook injection)
- References to `.claude/scripts/set-active-plan.cjs`
- Slash command pattern `ck:skill-name` → change to `skill-name`
- References to `statusline` scripts

---

## Skill Format

Every skill directory follows this pattern:

```
skills/[skill-name]/
├── SKILL.md          # Index + content map (always read first)
├── sections/         # Detailed reference files (read selectively)
├── examples/         # Code examples
└── scripts/          # Optional Python validation scripts
```

**SKILL.md header:**
```yaml
---
name: skill-name
description: When to use this skill
allowed-tools: Read, Write, Edit, Glob, Grep
version: 2.0
priority: HIGH
---

# Skill Title

## Content Map
| File | Description | When to Read |
|------|-------------|--------------|
| sections/overview.md | Overview | Always |
| sections/patterns.md | Code patterns | When implementing |

## Related Skills
@[skills/related-skill]

## Decision Checklist
## Anti-Patterns
```

---

## Skill Curation: Keep ~30 from 70+

### Core Workflow (keep all)
`plan`, `research`, `scout`, `debug`, `cook`, `fix`, `ask`, `brainstorm`

### Code Quality (keep all)
`code-review`, `simplify`, `sequential-thinking`, `problem-solving`

### Development (keep)
`frontend-development`, `backend-development`, `databases`, `mobile-development`, `devops`

### UI/UX (keep)
`ui-styling`, `ui-ux-pro-max`, `frontend-design`

### Testing (keep)
`test`, `web-testing`

### Documentation (keep)
`docs`, `docs-seeker`, `mermaidjs-v11`, `preview`

### AI/ML (keep, rename)
`google-adk-python`, `claude-api` → rename to `ai-api`, `ai-multimodal`

### Domain-specific (keep if user uses them)
`payment-integration`, `better-auth`, `shopify`, `threejs`, `shader`, `remotion`

### Utilities (keep)
`media-processing`, `repomix`, `git`, `kanban`

### DROP (Claude/Grab-specific)
- `cc-configure-model-provider` — Claude Code model switching
- `context-engineering` — Claude context limits
- `grabgpt:*` — Grab internal
- `llm-kit:*` — Grab internal
- `watzup` — Claude session wrap-up
- `team` — Uses Claude native TaskCreate/SendMessage
- `mcp-builder`, `mcp-management` — MCP is Claude Code-specific
- `claude-code-guide` — Claude Code documentation skill
- `cc-configure-model-provider` — Claude-specific

---

## Skill Adaptation: What to Change

1. Remove `ck:` prefix from all cross-skill references
2. Remove `TaskCreate`/`TaskUpdate` from skill scripts
3. Update report paths to use static naming convention (no hook injection)
4. Replace `/ck:skill` invocation pattern with direct `@[skills/skill-name]` reference
5. Remove `.claude/` path assumptions — use `.agent/` equivalents
