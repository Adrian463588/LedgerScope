---
name: plan
description: "Plan implementations, design architectures, create technical roadmaps with detailed phases. Use for feature planning, system design, solution architecture, implementation strategy, phase documentation."
argument-hint: "[task] OR archive|red-team|validate"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash, WebFetch
---

# Planning

Create detailed technical implementation plans through research, codebase analysis, solution design, and comprehensive documentation.

## When to Use

- Planning new feature implementations
- Architecting system designs
- Evaluating technical approaches
- Creating implementation roadmaps
- Breaking down complex requirements

## Operations

| Operation | Description |
|-----------|-------------|
| `(default)` | Create implementation plan for a task |
| `archive` | Write journal entry & archive plans |
| `red-team` | Adversarial plan review |
| `validate` | Critical questions interview |

## Workflow Modes

Default: `--auto` (analyze task complexity and auto-pick mode).

| Flag | Mode | Research | Red Team | Validation |
|------|------|----------|----------|------------|
| `--auto` | Auto-detect | Follows mode | Follows mode | Follows mode |
| `--fast` | Fast | Skip | Skip | Skip |
| `--hard` | Hard | 2 researchers | Yes | Optional |
| `--parallel` | Parallel | 2 researchers | Yes | Optional |
| `--two` | Two approaches | 2+ researchers | After selection | After selection |

## Process

1. **Pre-Creation Check** — Check for existing active plans
2. **Mode Detection** — Auto-detect or use explicit flag
3. **Research Phase** — Run researchers (skip in fast mode)
4. **Codebase Analysis** — Read docs, scout if needed
5. **Plan Documentation** — Write comprehensive plan
6. **Red Team Review** — Adversarial review (hard/parallel/two modes)
7. **Post-Plan Validation** — Critical interview (hard/parallel/two modes)

## Plan File Structure

```
plans/
└── YYMMDD-HHMM-[slug]/
    ├── plan.md              # Overview, status, phases
    ├── phase-01-*.md        # Detailed phase files
    └── reports/             # Research & scout reports
```

## Core Rules

- YAGNI, KISS, DRY always
- Be honest, brutal, concise
- Each phase file: context, overview, requirements, architecture, steps, todo, success criteria
- Plan.md under 80 lines

## References

- `references/workflow-modes.md` — Auto-detection logic, per-mode workflows
- `references/research-phase.md` — Research methodology
- `references/solution-design.md` — Design patterns
- `references/plan-organization.md` — File structure standards
- `references/output-standards.md` — Task breakdown format
