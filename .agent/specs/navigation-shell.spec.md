# OpenSpec: Navigation Shell & Tenant Switching Specification

## 1. Context & Architecture Kernel
- **Runtime Mode**: Vue 3 Standalone Single Page Application (SPA).
- **Core Design Rule**: The primary shell (`frontend/src/`) must strictly remain decoupled from Inertia.js lifecycle context (`createInertiaApp`) unless explicitly refactored. No components mounted under `App.vue` may call `@inertiajs/vue3` hooks (`usePage`, `useForm`, `<Link>`) directly.

---

## 2. Behavioral Specifications (BDD Scenarios)

### Feature: Dynamic Navigation & Route Isolation
```gherkin
Scenario: Desktop Navigation Display
  Given the application is running in desktop viewport (>= 768px)
  When the user views the Sidebar
  Then the brand title "LedgerScope" and version tag are rendered
  And grouped navigation links are populated from the application route definitions.

Scenario: Dynamic Active Route Indicator
  Given the user is on the "/dashboard" route
  When NavLink renders with href="/dashboard"
  Then the component has the "active" CSS class applied
  And clicking the link executes internal SPA navigation without causing a browser-level page reload.
```

### Feature: Responsive Mobile Drawer
```gherkin
Scenario: Mobile Sidebar Drawer Toggle & Backdrop Dismiss
  Given the application is running in mobile viewport (< 768px)
  And the Sidebar drawer is currently hidden with "-translate-x-full"
  When the user taps the mobile menu toggle button
  Then the drawer slides in with "translate-x-0"
  And a high-contrast backdrop overlay is displayed at z-40.
  When the user taps the backdrop overlay or a navigation link
  Then the drawer slides out and the backdrop is dismissed.
```

### Feature: Multi-Company Tenant Switching
```gherkin
Scenario: Changing Active Tenant
  Given the user is authenticated and has access to multiple companies
  When the user selects a new company from the CompanySwitcher dropdown
  Then the "switchCompany" action is invoked on the Pinia company store
  And the activeCompanyId is reactively updated across all dependent views without hard-reloading the app.
```

---

## 3. Quality & Regression Gate
- **Lint**: ESLint passing with `--max-warnings 0`. Zero `any` types permitted.
- **Format**: Prettier passing with `--check`.
- **Typecheck**: `vue-tsc --noEmit` exit code 0.
- **Unit Testing**: Vitest suite covering all layout shell edge cases.
