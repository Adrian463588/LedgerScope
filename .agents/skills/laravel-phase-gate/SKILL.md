---
name: laravel-phase-gate
description: Enforces LedgerScope Laravel backend phase order, phase completion checks, and test gates. Use when implementing or reviewing any build phase, moving between phases, or validating backend readiness.
---

# Laravel Phase Gate

Use `AGENTS.md` as the source of truth for phase order and backend scope.

## Phase Rules

- Complete phases strictly in order.
- Do not start Phase N+1 until Phase N tests and gates pass.
- Keep frontend/Inertia work out of scope unless a task explicitly changes the scope.
- Prefer narrow, behavior-level implementation inside the required module layout.

## Required Gate

Before marking a phase complete, verify:

```powershell
rtk powershell -NoProfile -Command ".\vendor\bin\pint --test"
rtk powershell -NoProfile -Command ".\vendor\bin\phpstan analyse"
rtk powershell -NoProfile -Command "php artisan test --parallel"
rtk powershell -NoProfile -Command "php artisan migrate:fresh --seed"
```

If the full gate is too expensive during iteration, run the narrow Pest filter first and record that the full gate remains.

## Review Checklist

- Tests exist for the phase-specific success path and required failure paths.
- Controllers call actions/services only.
- Models contain Eloquent definitions, relationships, casts, and scopes only.
- Policies or gates protect every model action.
- API responses use the project response contract.
- No debug artifacts remain.
