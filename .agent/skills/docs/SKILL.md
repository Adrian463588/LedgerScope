---
name: docs
description: "Analyze codebase and manage project documentation — init, update, summarize. Use for creating or updating docs/ directory."
argument-hint: "init|update|summarize"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Documentation Management

Analyze codebase and manage project documentation through scouting, analysis, and structured generation.

## Operations

| Operation | Description |
|-----------|-------------|
| `init` | Analyze codebase and create initial docs |
| `update` | Analyze recent changes and update existing docs |
| `summarize` | Quick codebase summary update |

## Routing

- `init` → `references/init-workflow.md`
- `update` → `references/update-workflow.md`
- `summarize` → `references/summarize-workflow.md`
- empty/unclear → ask user which operation

## Documentation Structure

```
./docs
├── project-overview-pdr.md    # Project purpose, goals, stakeholders
├── code-standards.md          # Coding conventions and patterns
├── codebase-summary.md        # High-level codebase map
├── design-guidelines.md       # UI/UX design decisions
├── deployment-guide.md        # How to deploy
├── system-architecture.md     # Architecture diagrams and decisions
└── project-roadmap.md         # Phases, milestones, progress
```

## When to Update

- After implementing a new feature
- After changing architecture or patterns
- After adding new dependencies
- When roadmap phase status changes
- After security updates

## Update Protocol

1. Read current doc content before editing
2. Make targeted updates — don't rewrite sections that are still accurate
3. Keep dates/version references current
4. Verify cross-references and links are still valid
5. Keep `codebase-summary.md` under 150 lines

## Quality Standards

- Docs should be accurate and reflect current state
- Write for a developer joining the project today
- Prefer concrete examples over abstract descriptions
- Link to code files where helpful

## Important

- Do NOT start implementing code from this skill
- Source of truth: `./docs` directory
