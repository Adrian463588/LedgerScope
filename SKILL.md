---
name: rtk-ai-caveman-laravel
description: >
  RTK AI + Caveman development discipline for LedgerScope (Laravel 13 backend).
  Use this skill to enforce Red-Ticket-Keep TDD workflow and Caveman debugging protocol
  on every development session. Must be applied consistently across all phases.
---

# RTK AI + Caveman — LedgerScope Development Skill

> Compatibility reference: the canonical Antigravity workspace copy lives at
> `.agents/skills/rtk-ai-caveman-laravel/SKILL.md`. Keep this root file only as
> a human-readable fallback for agents/tools that scan root-level `SKILL.md`.

## What Is This?

This skill defines the **RTK AI + Caveman** development discipline for the LedgerScope
backend project. It is mandatory on every coding session, every feature, every bug fix.

## 0. RTK CLI Command Discipline

Always prefix shell commands with `rtk`.

On Windows PowerShell, built-in cmdlets such as `Get-ChildItem` and `Get-Content` must be
wrapped through PowerShell:

```powershell
rtk powershell -NoProfile -Command "Get-ChildItem -Force"
rtk powershell -NoProfile -Command "php artisan test --filter LoginTest"
```

Use `rtk gain` or `rtk gain --history` to inspect token savings.

---

## 1. RTK — Red-Ticket-Keep

RTK is the TDD (Test-Driven Development) cycle adapted for this project:

### 1.1 RED — Write a Failing Test First

Before writing ANY implementation code, you MUST write a failing test.

**Examples:**

```php
// tests/Feature/Auth/LoginTest.php
it('rejects login with invalid credentials', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'nobody@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
             ->assertJson(['success' => false]);
});
```

```php
// tests/Unit/ValueObjects/MoneyTest.php
it('throws when adding different currencies', function () {
    $idr = new Money('100.00', 'IDR');
    $usd = new Money('100.00', 'USD');

    expect(fn () => $idr->add($usd))->toThrow(InvalidArgumentException::class);
});
```

**Rules:**
- Run `php artisan test` → test MUST fail (red) before you write implementation.
- If a test passes before implementation, the test is wrong — rewrite it.

### 1.2 GREEN — Implement the Minimum to Pass

Write the **simplest possible implementation** to make the test pass. No over-engineering.

**Rules:**
- Do NOT implement features not covered by a test.
- Do NOT add abstractions not yet needed.
- Keep it dumb and simple first.

```bash
php artisan test --filter "rejects login with invalid credentials"
# Must output: PASSED (green)
```

### 1.3 KEEP — Refactor Clean

Once green, refactor the implementation and test to meet:
- SOLID principles
- DRY (no code duplication)
- AGENTS.md coding conventions
- PSR-12 via Pint
- PHPStan level 8 compliance

```bash
./vendor/bin/pint                   # auto-format
./vendor/bin/phpstan analyse        # must pass level 8
php artisan test --parallel         # must still be green after refactor
```

**The cycle:** RED → GREEN → KEEP → (repeat for next feature)

---

## 2. AI-Assisted RTK Workflow

When using AI assistance (Antigravity/Gemini), follow this order:

### Step 1 — Ask AI to Write the Test First
```
"Write a Pest test for [feature]. The test should cover: [list edge cases].
Do NOT write the implementation yet."
```

### Step 2 — Review the Test
- Check the test actually tests behavior, not implementation details.
- Add missing edge cases.
- Run → confirm it's RED.

### Step 3 — Ask AI to Implement
```
"Now implement [class/method] to make this test pass.
Follow AGENTS.md rules: no business logic in controllers, wrap DB mutations in DB::transaction()."
```

### Step 4 — Refactor Together
```
"Refactor this implementation to be SOLID and match AGENTS.md conventions.
Run Pint and PHPStan mentally and fix any issues."
```

---

## 3. Caveman Debugging Protocol

When something is broken, follow this **escalation ladder** strictly in order.
Do NOT jump to step 4 without trying steps 1-3 first.

### Level 1 — Caveman (dd / dump)

```php
// In a controller or service, temporarily:
dd($someVariable);
dump($request->all());
dd($journal->toArray());
```

Use when:
- You don't know what data is actually in a variable.
- A service is receiving unexpected input.
- A model has unexpected attribute values.

**Remove all `dd()` and `dump()` before committing.**

### Level 2 — Log Debug

```php
use Illuminate\Support\Facades\Log;

Log::debug('JournalService::post called', [
    'journal_id' => $journal->id,
    'user_id'    => $user->id,
    'lines_count'=> $journal->lines->count(),
    'debit_total'=> $journal->lines->sum('debit'),
    'credit_total'=> $journal->lines->sum('credit'),
]);
```

Use when:
- The bug happens in a background job (you can't dd() it).
- You need to trace a sequence of events.
- The bug is intermittent.

```bash
tail -f storage/logs/laravel.log
```

### Level 3 — Laravel Telescope

Install Telescope in dev:
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Use when:
- You need to inspect HTTP requests, queries, jobs, events in a GUI.
- Debugging queue jobs or scheduled commands.

Access: `http://localhost/telescope`

### Level 4 — Xdebug / Breakpoints

Only escalate here if levels 1-3 failed.

```ini
; php.ini
zend_extension=xdebug
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_port=9003
```

---

## 4. LedgerScope-Specific Debug Patterns

### Debugging Journal Posting Failures

```php
// In JournalService::post(), add Level 2 debug:
Log::debug('JournalService::post validation', [
    'period_locked'   => $journal->period->is_locked,
    'period_date_range' => [$journal->period->start_date, $journal->period->end_date],
    'journal_date'    => $journal->journal_date,
    'debit_total'     => $debit->amount(),
    'credit_total'    => $credit->amount(),
    'is_balanced'     => $debit->equals($credit),
    'active_accounts' => $journal->lines->every(fn($l) => $l->account->is_active),
]);
```

### Debugging Audit Log Not Writing

```php
// Check queue is running:
php artisan horizon:status

// Check failed jobs:
php artisan queue:failed

// Check event is dispatched (Level 2):
Log::debug('Dispatching UserLoggedIn event', ['user_id' => $user->id]);
event(new UserLoggedIn($user->id, ...));
Log::debug('UserLoggedIn event dispatched');
```

### Debugging Permission Failures

```php
// In a controller, before policy check:
Log::debug('Permission check', [
    'user_id'     => $user->id,
    'roles'       => $user->roles->pluck('name'),
    'permissions' => $user->getAllPermissions(),
    'checking'    => 'journal.post',
    'has_it'      => $user->hasPermission('journal.post'),
]);
```

---

## 5. Mandatory Rules (Never Violate)

These apply to every session, every file:

1. **Always write the test FIRST.** If you skip RED, you violated RTK.
2. **Never commit `dd()`, `dump()`, or `var_dump()`.** These are debug-only.
3. **Never jump to Xdebug without trying dd/log first.** Escalate the ladder.
4. **Every failing test must map to a ticket/issue.** Even in local dev, name your test clearly.
5. **After KEEP phase:** run `./vendor/bin/pint && ./vendor/bin/phpstan analyse && php artisan test --parallel`. All three must pass.

---

## 6. Phase Gate Checklist

Before moving to the next phase, confirm:

```
[ ] All tests for this phase are GREEN
[ ] `./vendor/bin/pint --test` passes (no style violations)
[ ] `./vendor/bin/phpstan analyse` passes (level 8, zero errors)
[ ] `php artisan test --parallel` passes (all tests green)
[ ] No `dd()`, `dump()`, or `var_dump()` left in code
[ ] No TODO/FIXME comments that block the phase
[ ] Migration `php artisan migrate:fresh --seed` runs without error
```

---

## 7. Quick Reference

| Situation | Tool | Command |
|-----------|------|---------|
| Unknown variable value | Caveman | `dd($var)` |
| Background job debugging | Log | `Log::debug(...)` |
| Request/query/job trace | Telescope | `localhost/telescope` |
| Complex state inspection | Xdebug | IDE breakpoint |
| Test failing unexpectedly | Pest verbose | `php artisan test --filter "test name" -v` |
| Style violations | Pint | `./vendor/bin/pint` |
| Static analysis errors | PHPStan | `./vendor/bin/phpstan analyse` |
| Full phase gate check | All | see §6 above |

---

## 8. RTK Applied to This Project — Phase Summary

| Phase | RED (failing tests to write first) |
|-------|------------------------------------|
| 1 | Login rejected, audit log immutable, permission check |
| 2 | Company isolation, user cannot access other company |
| 3 | Period generates 12 months, cannot lock twice |
| 4 | Import validates account_code uniqueness |
| 5 | Unbalanced journal rejected, posted journal immutable |
| 6 | Trial balance sums correctly from posted entries only |
| 7 | Statement generation requires balanced trial balance |
| 8 | Working paper blocked by open review note |
| 9 | Report download returns signed URL |
| 10 | Notification dispatched on evidence uploaded |
| 11 | Rate limit triggers after 5 failed logins |
