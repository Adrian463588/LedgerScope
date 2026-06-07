# AntigravityKit — AI Handover Guide
**Project:** ClaudeKit → AntigravityKit Transformation
**Date:** 2026-03-07
**Status:** Research complete, implementation pending

---

## What Is This?

This project transforms **ClaudeKit** (a Claude Code CLI agent toolkit) into **AntigravityKit** — an equivalent agent toolkit for the **Antigravity AI coding assistant**.

Think of it as: `ClaudeKit : Claude Code = AntigravityKit : Antigravity`

| Claude Code | Antigravity |
|-------------|-------------|
| `.claude/` folder | `.agent/` folder |
| `CLAUDE.md` | `GEMINI.md` (or `ANTIGRAVITY.md`) |
| hooks system | no hooks — explicit orchestration |
| `/skill-name` commands | `.agent/workflows/` slash commands |

---

## Repository Map

```
Project_ AntiGravityKit/
├── plan.md                        # 7-phase implementation plan (START HERE)
├── 00-README.md                   # This file — overview & quick start
├── 01-Architecture.md             # Folder structure & component design
├── 02-Agents-and-Skills.md        # Agent list, skill list, adaptation guide
├── 03-Workflows-and-Orchestration.md  # Workflow patterns, hook replacement
└── 04-Extension-Guide.md          # How to add agents/skills/workflows
```

**Research report** (background): `G:\My Drive\KitBuild\Biz\General\plans\reports\researcher-260307-1238-claudekit-to-antagravity.md`

---

## Source Materials

| Material | Location |
|----------|----------|
| Current ClaudeKit (source) | `G:\My Drive\KitBuild\Biz\General\.claude\` |
| Old AntigravityKit transformation (reference) | `G:\My Drive\KitBuild\ClaudeKit\.agent\` |
| AntigravityKit open-source reference | https://antigravity-kit.unikorn.vn/docs |
| AntigravityKit GitHub | https://github.com/vudovn/antigravity-kit |

---

## Quick Start for Implementers

1. **Read this file** — understand the scope
2. **Read `01-Architecture.md`** — understand the target folder structure
3. **Read `02-Agents-and-Skills.md`** — understand what to build
4. **Read `plan.md`** — follow the 7-phase implementation plan
5. **Reference** `G:\My Drive\KitBuild\ClaudeKit\.agent\` constantly — proven patterns already exist

---

## Critical Facts

- `.agent/` = Antigravity's native folder (auto-detected by IDE)
- **Do NOT** add `.agent/` to `.gitignore` — breaks slash command detection
- YAML frontmatter in agent files is Antigravity-native (no extra setup)
- Workflow files auto-register as slash commands from their filename
- `// turbo` annotation in workflow = auto-execute without user confirmation
- **No hooks** — all context must be passed explicitly in orchestration prompts

---

## Implementation Status

| Phase | Description | Status |
|-------|-------------|--------|
| Research | ClaudeKit analysis + AntigravityKit conventions | DONE |
| Phase 1 | `.agent/` folder structure + ARCHITECTURE.md | Pending |
| Phase 2 | Entry point (GEMINI.md + AI_HANDOVER docs) | Pending |
| Phase 3 | Agent adaptation (14 ClaudeKit → ~16 agents) | Pending |
| Phase 4 | Skill curation (~30 from 70+) | Pending |
| Phase 5 | Workflow slash commands (~10) | Pending |
| Phase 6 | Validation scripts | Pending |
| Phase 7 | Testing in Antigravity | Pending |
