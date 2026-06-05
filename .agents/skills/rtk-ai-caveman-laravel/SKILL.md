---
name: rtk-ai-caveman-laravel
description: Enforces RTK AI Red-Ticket-Keep TDD and Caveman debugging for LedgerScope Laravel backend work. Use for any feature, bug fix, refactor, test, debugging session, or agent-assisted development in this project.
---

# RTK AI + Caveman Laravel

Use this skill for every LedgerScope backend coding session.

## Command Discipline

- Prefix shell commands with `rtk`.
- For PowerShell cmdlets, invoke PowerShell through RTK:
  ```powershell
  rtk powershell -NoProfile -Command "Get-ChildItem -Force"
  rtk powershell -NoProfile -Command "php artisan test --filter LoginTest"
  ```
- Use `rtk gain` and `rtk gain --history` when checking token savings.

## RTK AI Cycle

1. RED: write or update a Pest test that fails for the intended behavior.
2. GREEN: implement the smallest backend change that passes the test.
3. KEEP: refactor to match `AGENTS.md`, then verify with Pint, Larastan, and Pest.

Do not implement behavior that is not covered by the RED test. Controllers stay thin, services/actions own business logic, and financial mutations use `DB::transaction()`.

## Caveman Debugging Ladder

Use the lowest useful level first:

1. Temporary `dd()` / `dump()` for unknown local state.
2. `Log::debug()` for jobs, event/listener flow, queues, and intermittent behavior.
3. Telescope for request, query, event, and job inspection in local development.
4. Xdebug only after the earlier levels do not expose the issue.

Remove all `dd()`, `dump()`, `var_dump()`, temporary logs, and debug-only code before finishing.

## KEEP Gate

After implementation, run the relevant narrow test first, then the phase gate when practical:

```powershell
rtk powershell -NoProfile -Command ".\vendor\bin\pint --test"
rtk powershell -NoProfile -Command ".\vendor\bin\phpstan analyse"
rtk powershell -NoProfile -Command "php artisan test --parallel"
```
