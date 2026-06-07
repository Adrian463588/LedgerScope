---
description: Update project documentation after implementation changes. Auto-executes on trigger.
turbo: true
---

// turbo

# /docs — Documentation Update

## Task
$ARGUMENTS

## Critical Rules

1. **Analyze before writing** — Read recent changes first; only update docs that are actually affected
2. **No phantom updates** — Do not rewrite docs sections that have not changed
3. **Keep docs current** — Changelog, roadmap, architecture, code-standards must reflect actual state
4. **Docs impact statement** — Always end with: `Docs impact: none | minor | major`

## Execution

**docs-manager**

### Step 1: Identify what changed
```bash
git diff --name-only HEAD~1  # or per $ARGUMENTS
git log --oneline -5
```

### Step 2: Assess documentation impact

| Change Type | Docs to Update |
|-------------|---------------|
| New feature | `project-changelog.md`, `development-roadmap.md` |
| API change | `system-architecture.md`, `code-standards.md` |
| New component/module | `codebase-summary.md` |
| Security fix | `project-changelog.md` |
| Dependency update | `deployment-guide.md` |
| Breaking change | All affected docs + `README.md` |

### Step 3: Update affected docs

For each affected doc:
1. Read current content
2. Identify stale sections
3. Edit in place — do not rewrite sections that are still accurate
4. Update version numbers and dates where applicable

### Step 4: Changelog entry format

```markdown
## [YYYY-MM-DD]

### Added
- [Feature description]

### Changed
- [What changed and why]

### Fixed
- [Bug fixed]

### Security
- [Security improvement]
```

### Step 5: Report

```markdown
## Docs Update Report

### Files Updated
- `docs/[file]`: [what changed]

### Files Unchanged (reviewed, no update needed)
- `docs/[file]`: [reason]

### Docs impact: [none|minor|major]
```

## Output

| Deliverable | Location |
|-------------|----------|
| Updated docs | `docs/*.md` |
| Update report | `plans/reports/docs-YYMMDD-HHMM.md` |
