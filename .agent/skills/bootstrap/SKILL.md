---
name: bootstrap
description: "Bootstrap new projects with research, tech stack selection, design, and scaffolding. Modes: full (interactive), auto (default), fast (skip research)."
argument-hint: "<project-description> [--full|--auto|--fast]"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Bootstrap - New Project Scaffolding

End-to-end project setup from idea to running codebase.

**Principles:** YAGNI, KISS, DRY | Token efficiency | Concise reports

## Usage

```
@[skills/bootstrap] <project description> [--flag]
```

## Flags

| Flag | Behavior |
|------|----------|
| `--full` | Full interactive workflow with approval gates at each step |
| `--auto` | Research + scaffold with minimal interruption (default) |
| `--fast` | Skip research, jump straight to scaffolding |

## Workflow

```
[Requirements] → [Research?] → [Tech Stack] → [Plan] → [Approval] → [Scaffold] → [Verify]
```

| Mode | Research | Approval Gate | Output |
|------|----------|---------------|--------|
| full | Yes | Before scaffold | Full plan + scaffold |
| auto | Yes | Tech stack only | Full plan + scaffold |
| fast | No | Before scaffold | Scaffold only |

## Required Subagents

| Phase | Subagent |
|-------|----------|
| Research | researcher (up to 2 parallel) |
| Planning | project-planner |
| Scaffolding | fullstack-developer |
| Verification | test-engineer |
