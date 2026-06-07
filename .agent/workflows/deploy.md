---
description: Deployment to staging or production with pre-flight validation and rollback support.
---

# /deploy — Deployment

## Task
$ARGUMENTS

## Critical Rules

1. **Pre-flight mandatory** — checklist.py must pass before any deployment
2. **Clean git state** — No uncommitted changes allowed; branch must be current
3. **Checkpoint before production** — Always confirm with user before deploying to production
4. **Rollback documented** — Every deployment must have a documented rollback path

## Phase 1: Pre-flight Validation

**Run checklist script:**
```bash
python .agent/scripts/checklist.py . [--url URL]
```

If any check fails: STOP. Report failures. Do not proceed to deployment.

## Phase 2: Git State Verification

**git-manager**
- Confirm working tree is clean: `git status`
- Confirm branch is up to date with remote: `git fetch && git status`
- Confirm no merge conflicts
- Tag the release commit: `git tag v{version} -m "release: {description}"`

## Checkpoint (Production Only)

If target environment is **production**:

```
Pre-flight: PASSED
Git state: clean
Branch: [branch] — [N] commits ahead of main

Target: PRODUCTION

Are you sure you want to deploy to production? (Y/N)
- Y: deployment proceeds
- N: cancelled
```

**Do not deploy to production without explicit Y confirmation.**

## Phase 3: Deployment

Provider-agnostic — detect from project config or `$ARGUMENTS`:

| Platform | Deploy Command |
|----------|---------------|
| Vercel | `vercel --prod` |
| Railway | `railway up` |
| Fly.io | `fly deploy` |
| Docker | `docker compose up -d --build` |
| Custom | Per `deploy` script in `package.json` or `Makefile` |

Steps:
1. Build application: `npm run build` / `python -m build` / equivalent
2. Run deploy command
3. Wait for deployment confirmation from platform

## Phase 4: Post-Deploy Verification

- Health check: `curl -f {url}/health` or equivalent
- Smoke test: verify 2–3 critical routes respond correctly
- Monitor: check platform dashboard for errors in first 2 minutes

### On failure:
```
Deployment failed at: [step]
Error: [message]

Rollback options:
- Vercel: vercel rollback
- Railway: railway rollback
- Docker: docker compose down && git checkout {prev-tag} && docker compose up -d
```

## Output

```markdown
## Deployment Report

### Environment: [staging/production]
### Version: [tag or commit]
### Platform: [provider]
### Duration: [seconds]

### Checks Passed
- [x] Pre-flight checklist
- [x] Git state clean
- [x] Build successful
- [x] Health check

### URLs
- Live: [url]
- Dashboard: [platform url]

### Rollback Command
[exact command to rollback]
```
