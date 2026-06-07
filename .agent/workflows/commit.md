---
description: Conventional git commit — stage, message, and push. Auto-executes on trigger.
turbo: true
---

// turbo

# /commit — Git Commit

## Task
$ARGUMENTS

## Critical Rules

1. **Never commit secrets** — Block `.env`, `*.key`, `credentials.*`, `secrets.*`
2. **Conventional format** — `type(scope): description` — no exceptions
3. **Focused commits** — One logical change per commit; split if needed
4. **No AI references** — Commit messages must read as human-authored

## Execution

**git-manager**

### Step 1: Review working tree
```bash
git status
git diff --staged
git diff
```

### Step 2: Security check — abort if any of these are staged:
- `.env` or `.env.*`
- Files matching `*secret*`, `*credential*`, `*private_key*`
- `node_modules/`
- Files over 10MB

If blocked file detected: **STOP** and report to user. Do not commit.

### Step 3: Stage relevant files

Stage files related to `$ARGUMENTS` or the most recent logical change.
Do NOT use `git add -A` blindly — review each file before staging.

```bash
git add [specific files or patterns]
```

### Step 4: Craft commit message

**Format:** `type(scope): concise description`

| Type | Use When |
|------|----------|
| `feat` | New feature or capability added |
| `fix` | Bug fix |
| `refactor` | Code restructure without behavior change |
| `test` | Tests added or updated |
| `docs` | Documentation only |
| `chore` | Build, deps, config, tooling |
| `perf` | Performance improvement |
| `style` | Formatting, whitespace (no logic change) |

Rules:
- Description: present tense, lowercase, no period, under 72 chars
- Scope: affected module/area in parentheses (optional but preferred)
- Body (if needed): blank line after subject, explain WHY not WHAT

### Step 5: Commit and push
```bash
git commit -m "type(scope): description"
git push
```

### Step 6: Confirm
```
Committed: [hash] type(scope): description
Pushed to: [remote/branch]
Files: [N files changed, +N -N]
```

## Examples

```
feat(auth): add OAuth2 login with Google
fix(api): handle null response from payment gateway
refactor(cart): extract price calculation into service
test(user): add edge cases for email validation
docs(readme): update setup instructions for Windows
chore(deps): upgrade react to 18.3
```
