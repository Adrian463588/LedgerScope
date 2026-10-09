# Changelog

All notable changes to the LedgerScope project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [v1.0.2] - 2026-10-09

### Added

- **BDD / SDD Unit Test Suite (`Sidebar.spec.ts`)**: Added comprehensive behavior-driven tests validating:
  - Desktop brand display and routing definitions.
  - Responsive mobile drawer slide-over toggling and backdrop dismiss mechanism.
  - Dynamic active route styling via standalone router without external context dependency.
  - Multi-company tenant switching via Pinia store.
- **Specification Documentation**: Added specification-driven development (SDD) guidelines for client-side navigation.

### Fixed

- **Strict Typing Compliance**: Eliminated all `@typescript-eslint/no-explicit-any` instances in frontend test suites, adhering to strict TypeScript contracts.
- **Code Style & Formatting**: Formatted test suites with Prettier and verified against ESLint with zero warnings.

### Download Links

- **Source Code (ZIP)**: [LedgerScope-v1.0.2.zip](https://github.com/Adrian463588/LedgerScope/archive/refs/tags/v1.0.2.zip)
- **Source Code (TAR.GZ)**: [LedgerScope-v1.0.2.tar.gz](https://github.com/Adrian463588/LedgerScope/archive/refs/tags/v1.0.2.tar.gz)

---

## [v1.0.1] - 2026-10-09

### Fixed

- **Blank Screen Crash on Dashboard**: Resolved fatal Vue mounting error caused by invoking `@inertiajs/vue3` hooks (`usePage`, `<Link>`) inside a standalone SPA shell without `createInertiaApp` context.
- **Router Isolation**: Re-established clean client-side routing through `@/router` to prevent full page reloads and retain Sanctum CSRF cookies and session persistence.
- **Company Switcher Reactivity**: Connected `<select>` elements to Pinia `useCompanyStore` for reliable tenant context switching.

### Download Links

- **Source Code (ZIP)**: [LedgerScope-v1.0.1.zip](https://github.com/Adrian463588/LedgerScope/archive/refs/tags/v1.0.1.zip)

---

## [v1.0.0] - 2026-08-15

### Added

- Initial production-ready release of LedgerScope.
- Double-entry bookkeeping engine with BCMath decimal precision.
- Quarterly closing checklists and period locking.
- Financial statement builder and ratio analysis dashboard.
- Audit fieldwork workspace: risk matrix, working papers, review notes, and findings.
- Evidence vault with MinIO integration.
