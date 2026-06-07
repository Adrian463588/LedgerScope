# AntigravityKit — Extension Guide
**How to add new agents, skills, and workflows after initial build.**

---

## Adding a New Agent

1. **Create** `.agent/agents/[agent-name].md`
2. **Use the standard format:**
   ```yaml
   ---
   name: agent-name
   description: When to use — include trigger keywords
   tools: Read, Grep, Glob, Edit, Write, Bash
   model: inherit
   skills: skill1, skill2
   ---
   ```
3. **Body sections** (in order):
   - Philosophy / Mindset
   - Core Responsibilities
   - Process (numbered steps)
   - File Ownership (glob patterns)
   - Anti-Patterns
   - Quality Checklist
4. **Update** `.agent/ARCHITECTURE.md` — add one-line entry to agents table
5. **Update** `GEMINI.md` REQUEST CLASSIFIER — add routing rule if needed
6. **Update** `02-Agents-and-Skills.md` in this handover folder

**Naming:** kebab-case, descriptive (`security-auditor`, `mobile-developer`, `data-engineer`)

---

## Adding a New Skill

1. **Create** directory `.agent/skills/[skill-name]/`
2. **Create** `SKILL.md` (required — this is the index):
   ```yaml
   ---
   name: skill-name
   description: When to use this skill (trigger keywords)
   allowed-tools: Read, Write, Edit, Glob, Grep
   version: 1.0
   priority: NORMAL
   ---

   # Skill Title

   ## Content Map
   | File | Description | When to Read |
   |------|-------------|--------------|
   | sections/overview.md | Core concepts | Always |

   ## Related Skills
   @[skills/other-skill]
   ```
3. **Add** `sections/` files for detailed content (keep each under 200 lines)
4. **Optionally add** `examples/` and `scripts/` directories
5. **Update** `.agent/ARCHITECTURE.md` — add to skills table
6. **Update** `02-Agents-and-Skills.md` in this handover folder

**Key rule:** SKILL.md is always read first. Other files loaded selectively per Content Map.

---

## Adding a New Workflow

1. **Create** `.agent/workflows/[command-name].md`
   - Filename = slash command: `deploy-staging.md` → `/deploy-staging`
2. **Use the standard format:**
   ```markdown
   ---
   description: One-line description
   ---

   # Workflow Title

   ## Task
   $ARGUMENTS

   ## Phase 1: [Name] (Sequential/Parallel)
   ...

   ## Output
   ...
   ```
3. **Add `// turbo`** at top if workflow should auto-execute (no confirmation prompt)
4. **Update** `.agent/ARCHITECTURE.md` — add to workflows table
5. **Update** `03-Workflows-and-Orchestration.md` in this handover folder

**Important:** Slash commands register automatically — no config file to update.

---

## Porting a Skill from ClaudeKit

When adapting a skill from `.claude/skills/[name]/`:

1. Copy `SKILL.md` and `sections/` to `.agent/skills/[name]/`
2. **Remove** these patterns:
   - `ck:` prefix → just `skill-name`
   - `TaskCreate`, `TaskUpdate`, `TaskList` references
   - `SendMessage` references
   - Hook injection references (`## Naming` section dependency)
   - `.claude/` path references → update to `.agent/`
3. **Update** report path pattern to static: `plans/reports/{type}-YYMMDD-HHMM-{slug}.md`
4. **Update** SKILL.md Content Map if files were reorganized
5. Test that selective loading still works (agent should not need to read entire skill folder)

---

## Porting an Agent from ClaudeKit

When adapting an agent from `.claude/agents/[name].md`:

1. Copy to `.agent/agents/[new-name].md`
2. **Remove** these patterns:
   - `TaskCreate`, `TaskUpdate`, `TaskList`, `TaskGet` tool calls
   - `SendMessage` tool calls
   - References to `.claude/scripts/set-active-plan.cjs`
   - Hook-based context injection expectations
   - `statusline` script references
3. **Replace** orchestration: instead of `TaskCreate`, instruct agent to "delegate by describing the task to [agent-name] with full context"
4. **Update** skills list in frontmatter to use new skill names (without `ck:` prefix)
5. Test that agent works without any auto-injected context

---

## Updating ARCHITECTURE.md

Keep this file under 100 lines. Structure:

```markdown
# AntigravityKit Architecture
[N] agents | [N] skills | [N] workflows

## Agents
| Agent | Description |
|-------|-------------|
| project-planner | Research and create phased implementation plans |
...

## Skills
| Skill | Category | Description |
|-------|----------|-------------|
| plan | Core | Create implementation plans |
...

## Workflows
| Command | File | Description |
|---------|------|-------------|
| /plan | plan.md | Create implementation plan |
...

## Scripts
| Script | Purpose |
|--------|---------|
| checklist.py | Security + lint + schema + tests |
```

---

## Quality Checklist Before Adding Anything

- [ ] File is under 200 lines (split if larger)
- [ ] Kebab-case filename
- [ ] ARCHITECTURE.md updated
- [ ] No ClaudeKit-specific patterns (hooks, TaskCreate, ck: prefix)
- [ ] No references to `.claude/` paths
- [ ] Handover docs updated (this folder)
