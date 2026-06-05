# DESIGN.md — LedgerScope Frontend Design System

> **Stack:** Inertia.js + Vue 3 + TypeScript + Tailwind CSS v4
> **Target Tool:** Stitch / Component Implementation Guide
> **Aesthetic Direction:** Financial Precision — Editorial Minimalism meets Data Density
> **Palette Identity:** Authoritative Red × Deep Black × Clean White

---

## 1. Design Philosophy

### 1.1 Concept

LedgerScope's visual identity draws from **financial journalism and high-stakes reporting**. Think Bloomberg Terminal meets The Economist — information-dense, typographically authoritative, color-disciplined. Every element earns its place. Red is power, not decoration. Black communicates permanence. White creates breath.

The UI must feel like a **precision instrument** — trusted by accountants, auditors, and financial analysts who need clarity under pressure.

### 1.2 Core Design Principles

| Principle      | Application                                                                  |
| -------------- | ---------------------------------------------------------------------------- |
| **Precision**  | Pixel-perfect alignment. Numbers right-aligned. Labels left-aligned. Always. |
| **Hierarchy**  | One focal point per screen. Everything else recedes.                         |
| **Restraint**  | Red is used in ≤3 places per screen. Never decorative.                       |
| **Density**    | Financial data must breathe — but not waste space. 8px baseline grid.        |
| **Permanence** | Approved/locked states visually communicate immutability.                    |
| **Trust**      | No gradients on financial figures. No rounded corners on data tables.        |

### 1.3 Personality

```
Professional   ████████████░░  Not casual
Minimal        ███████████░░░  Not bare
Dense          ████████░░░░░░  Not cluttered
Authoritative  █████████████░  Not intimidating
```

---

## 2. Color System

### 2.1 Brand Palette

```css
/* ─── PRIMARY BRAND ─── */
--brand-red: #c0190a; /* Primary CTA, active nav, key actions */
--brand-red-hover: #a31508; /* Hover state for brand-red elements */
--brand-red-press: #8b1208; /* Active/pressed state */
--brand-red-muted: #f8e8e7; /* Light backgrounds, badges, tints */
--brand-red-border: #ebbab7; /* Red-tinted borders */

/* ─── DARK SHELL ─── */
--shell-bg: #0c0d10; /* App shell, sidebar background */
--shell-surface: #13141a; /* Sidebar hover, secondary shell surfaces */
--shell-elevated: #1a1c23; /* Sidebar active item background */
--shell-border: #252830; /* Dividers within dark shell */
--shell-border-soft: #1e2028; /* Very subtle shell dividers */

/* ─── CONTENT AREA ─── */
--page-bg: #f4f5f7; /* Main content background */
--surface: #ffffff; /* Cards, panels, modals */
--surface-alt: #f9fafb; /* Table row zebra, input backgrounds */
--surface-hover: #f1f3f5; /* Table row hover, interactive surfaces */
--surface-active: #eef0f3; /* Pressed/selected table row */

/* ─── BORDERS ─── */
--border: #e3e5e9; /* Default border */
--border-strong: #cdd0d6; /* Emphasized borders, table headers */
--border-focus: #c0190a; /* Focus ring color (brand red) */

/* ─── TEXT ─── */
--text-primary: #0f1114; /* Headings, primary labels */
--text-secondary: #4a5261; /* Body text, descriptions */
--text-muted: #8c93a0; /* Placeholder, disabled labels */
--text-inverse: #f5f6f8; /* Text on dark backgrounds */
--text-inverse-muted: #7b8190; /* Secondary text on dark backgrounds */

/* ─── FINANCIAL ─── */
--debit-color: #0d6b3e; /* Debit amounts (positive movement) */
--credit-color: #c0190a; /* Credit amounts (negative / reducing) */
--zero-color: #8c93a0; /* Zero balances */
--locked-color: #4a5261; /* Locked/immutable record indicator */

/* ─── STATUS SEMANTIC ─── */
--status-success: #059669;
--status-success-bg: #ecfdf5;
--status-success-border: #a7f3d0;

--status-warning: #b45309;
--status-warning-bg: #fffbeb;
--status-warning-border: #fde68a;

--status-danger: #dc2626;
--status-danger-bg: #fef2f2;
--status-danger-border: #fecaca;

--status-info: #1d4ed8;
--status-info-bg: #eff6ff;
--status-info-border: #bfdbfe;

--status-neutral: #4a5261;
--status-neutral-bg: #f4f5f7;
--status-neutral-border: #e3e5e9;

--status-locked: #374151;
--status-locked-bg: #f9fafb;
--status-locked-border: #d1d5db;
```

### 2.2 Tailwind CSS v4 Config Extension

```css
/* resources/css/app.css */
@import "tailwindcss";

@theme {
  /* Brand */
  --color-brand-50: #fef1f0;
  --color-brand-100: #fee2e0;
  --color-brand-200: #fdcac7;
  --color-brand-300: #fa9e99;
  --color-brand-400: #f56b63;
  --color-brand-500: #e53a30;
  --color-brand-600: #c0190a; /* PRIMARY */
  --color-brand-700: #a31508;
  --color-brand-800: #8b1208;
  --color-brand-900: #72100a;
  --color-brand-950: #3d0705;

  /* Shell (Dark) */
  --color-shell-50: #f0f1f4;
  --color-shell-100: #e3e5e9;
  --color-shell-200: #c6cad3;
  --color-shell-300: #9ea5b3;
  --color-shell-400: #717a8a;
  --color-shell-500: #4a5261;
  --color-shell-600: #363d4b;
  --color-shell-700: #252830;
  --color-shell-800: #1a1c23;
  --color-shell-900: #13141a;
  --color-shell-950: #0c0d10; /* SHELL BG */

  /* Spacing */
  --spacing-18: 4.5rem;
  --spacing-22: 5.5rem;
  --spacing-88: 22rem;
  --spacing-72: 18rem;
  --spacing-60: 15rem;

  /* Border Radius */
  --radius-none: 0px;
  --radius-sm: 2px;
  --radius-md: 4px;
  --radius-lg: 6px;
  --radius-xl: 8px;
  --radius-2xl: 12px;

  /* Shadows */
  --shadow-card:
    0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px -1px rgba(0, 0, 0, 0.06);
  --shadow-modal: 0 20px 48px -12px rgba(0, 0, 0, 0.2);
  --shadow-dropdown: 0 4px 16px -2px rgba(0, 0, 0, 0.14);
  --shadow-focus: 0 0 0 3px rgba(192, 25, 10, 0.2);
}
```

### 2.3 Color Usage Map

```
Shell Sidebar          → shell-950 background, shell-900 hover, shell-800 active
Top Header             → white surface with border-bottom
Page Background        → page-bg (#F4F5F7)
Cards / Panels         → surface (white)
Primary Button         → brand-600 fill, brand-700 hover
Destructive Button     → status-danger fill
Ghost Button           → transparent, brand-600 text
Nav Active Item        → shell-800 bg + LEFT BORDER brand-600 (3px)
Nav Hover Item         → shell-900 bg
Table Header           → surface-alt (#F9FAFB)
Table Row Hover        → surface-hover (#F1F3F5)
Status Badge: draft    → status-neutral
Status Badge: active   → status-success
Status Badge: locked   → status-locked
Status Badge: rejected → status-danger
Status Badge: review   → status-warning
Financial: debit amt   → debit-color (green)
Financial: credit amt  → credit-color (brand-red)
Focus Ring             → border-focus (brand-red), shadow-focus
```

---

## 3. Typography System

### 3.1 Font Stack

```css
/* Heading / Display — Editorial authority */
font-family: "DM Serif Display", "Georgia", serif;

/* UI / Body — Technical precision */
font-family: "IBM Plex Sans", "Helvetica Neue", sans-serif;

/* Numbers / Code / Monospace */
font-family: "IBM Plex Mono", "Menlo", monospace;
```

**Google Fonts Import:**

```html
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link
  href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@300;400;500;600&display=swap"
  rel="stylesheet"
/>
```

### 3.2 Type Scale

```css
/* Display — Page titles, hero numbers */
.type-display-lg {
  font: 600 2.25rem/2.5rem "DM Serif Display";
} /* 36px */
.type-display-md {
  font: 600 1.875rem/2.25rem "DM Serif Display";
} /* 30px */
.type-display-sm {
  font: 600 1.5rem/2rem "DM Serif Display";
} /* 24px */

/* Heading — Section titles */
.type-heading-lg {
  font: 600 1.25rem/1.75rem "IBM Plex Sans";
} /* 20px */
.type-heading-md {
  font: 600 1.125rem/1.5rem "IBM Plex Sans";
} /* 18px */
.type-heading-sm {
  font: 600 1rem/1.5rem "IBM Plex Sans";
} /* 16px */

/* Label — Form labels, table headers */
.type-label-lg {
  font: 500 0.875rem/1.25rem "IBM Plex Sans";
} /* 14px */
.type-label-sm {
  font: 500 0.75rem/1rem "IBM Plex Sans";
} /* 12px */

/* Body — Descriptions, paragraphs */
.type-body-lg {
  font: 400 0.9375rem/1.625rem "IBM Plex Sans";
} /* 15px */
.type-body-md {
  font: 400 0.875rem/1.5rem "IBM Plex Sans";
} /* 14px */
.type-body-sm {
  font: 400 0.8125rem/1.375rem "IBM Plex Sans";
} /* 13px */

/* Mono — Financial figures, codes, IDs */
.type-mono-lg {
  font: 500 0.9375rem/1.5rem "IBM Plex Mono";
} /* 15px */
.type-mono-md {
  font: 400 0.875rem/1.5rem "IBM Plex Mono";
} /* 14px */
.type-mono-sm {
  font: 400 0.75rem/1.25rem "IBM Plex Mono";
} /* 12px */
```

### 3.3 Financial Number Rules

```
- All currency amounts: IBM Plex Mono, right-aligned in table cells
- Positive (debit): color var(--debit-color), no sign
- Negative (credit): color var(--credit-color), no sign (context from column header)
- Zero: color var(--zero-color), display as "—"
- Thousands separator: use locale (IDR → 1.000.000,00)
- Column width for amounts: always fixed, never auto
- Decimal point: always 2 decimal places for IDR
```

---

## 4. Spacing & Layout Grid

### 4.1 Base Grid

```
Base Unit: 4px
Scale: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96
Page gutter: 32px (desktop), 24px (tablet)
Card padding: 24px
Table cell: 12px vertical, 16px horizontal
Form group gap: 20px
Section gap: 32px
```

### 4.2 Application Shell Grid

```
┌─────────────────────────────────────────────────────┐
│  TOPBAR (height: 56px, sticky, z-50)                │
├──────────────┬──────────────────────────────────────┤
│              │                                      │
│   SIDEBAR    │         MAIN CONTENT                 │
│  (240px)     │         (flex-1)                     │
│  Collapsed:  │                                      │
│  (64px)      │                                      │
│              │                                      │
└──────────────┴──────────────────────────────────────┘

Sidebar width:  240px expanded, 64px collapsed
Topbar height:  56px
Content padding: 32px
Max content width: 1440px (centered in ultra-wide)
```

### 4.3 Content Layout Variants

```
/* Full width — tables, large data grids */
.layout-full     { width: 100%; }

/* Standard — most pages */
.layout-standard { max-width: 1200px; }

/* Narrow — forms, detail views */
.layout-narrow   { max-width: 768px; }

/* Split — master-detail, 60/40 */
.layout-split    { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }

/* Dashboard — 3-column grid */
.layout-dashboard { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
```

---

## 5. Component Specifications

### 5.1 Sidebar Navigation

```
Structure:
┌────────────────────────┐
│  ● LEDGERSCOPE         │  ← Logo zone (h-14, px-5)
│    v2.0                │
├────────────────────────┤
│  Company: PT Maju ...  │  ← Company switcher pill
├────────────────────────┤
│                        │
│  ○ Dashboard           │  ← Nav group items
│                        │
│  ACCOUNTING            │  ← Section label (uppercase, muted, 11px)
│  ○ Bookkeeping         │
│  ○ Journal Entries     │
│  ○ Chart of Accounts   │
│  ○ Trial Balance       │
│                        │
│  FINANCIAL             │
│  ○ Statements          │
│  ○ Analysis            │
│                        │
│  AUDIT                 │
│  ○ Engagements         │
│  ○ Working Papers      │
│  ○ Evidence            │
│  ○ Findings            │
│                        │
├────────────────────────┤
│  Settings              │  ← Bottom section
│  ● Your Name           │  ← User profile
└────────────────────────┘
```

**Nav Item States:**

```css
/* Default */
.nav-item {
  padding: 8px 12px;
  border-radius: 4px;
  color: var(--text-inverse-muted); /* #7B8190 */
  font: 400 0.8125rem/1.375rem "IBM Plex Sans";
  border-left: 3px solid transparent;
  transition: all 140ms ease;
}

/* Hover */
.nav-item:hover {
  background: var(--shell-surface); /* #13141A */
  color: var(--text-inverse); /* #F5F6F8 */
}

/* Active */
.nav-item.active {
  background: var(--shell-elevated); /* #1A1C23 */
  color: #ffffff;
  font-weight: 500;
  border-left-color: var(--brand-red); /* #C0190A — THE RED ACCENT */
}

/* Section label */
.nav-section-label {
  font: 500 0.625rem/1rem "IBM Plex Sans";
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-inverse-muted);
  padding: 16px 12px 6px;
  opacity: 0.5;
}
```

**Sub-navigation (expanded):**

```
  ○ Bookkeeping          ← parent, has chevron
    ├ Fiscal Years        ← child item, indent 12px extra, dot bullet
    ├ Accounting Periods
    ├ Quarterly Closing
    └ Reconciliation
```

**Sidebar Logo Treatment:**

```
LedgerScope wordmark:
  - "Ledger"   → IBM Plex Sans 500, white
  - "Scope"    → IBM Plex Sans 300, brand-red
  - Separator: thin red vertical line (1px) between words, height 14px
  - Version tag: mono small, muted
```

---

### 5.2 Topbar

```
┌────────────────────────────────────────────────────────┐
│  [≡ toggle] [breadcrumb]              [🔔 3] [avatar] │
└────────────────────────────────────────────────────────┘

Height: 56px
Background: white (#FFFFFF)
Border-bottom: 1px solid var(--border)
Padding: 0 32px
Position: sticky top-0 z-50
```

**Breadcrumb:**

```
Companies  /  PT Maju Jaya  /  Engagements  /  Audit 2025

Style:
- Separator: "/" text-muted, mx-2
- Links: text-secondary, no underline, hover: text-primary
- Current: text-primary, font-weight 500, no link
- Font: IBM Plex Sans 14px
```

**Notification Bell:**

```css
.notification-bell {
  position: relative;
  width: 36px;
  height: 36px;
  border-radius: 4px;
  color: var(--text-secondary);
}
.notification-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 16px;
  height: 16px;
  background: var(--brand-red);
  border-radius: 50%;
  font: 500 0.625rem "IBM Plex Mono";
  color: white;
}
```

---

### 5.3 Buttons

```
Variants: primary | secondary | ghost | danger | locked

Heights: sm (28px) | md (36px) | lg (40px)
Border-radius: 4px (--radius-md)
Font: IBM Plex Sans 500 13px/14px
Padding: sm (6px 12px), md (8px 16px), lg (10px 20px)
```

```css
/* PRIMARY — Brand Red */
.btn-primary {
  background: var(--brand-red);
  color: white;
  border: 1px solid var(--brand-red);
}
.btn-primary:hover {
  background: var(--brand-red-hover);
}
.btn-primary:active {
  background: var(--brand-red-press);
}

/* SECONDARY — Bordered */
.btn-secondary {
  background: white;
  color: var(--text-primary);
  border: 1px solid var(--border-strong);
}
.btn-secondary:hover {
  background: var(--surface-hover);
}

/* GHOST — Text only */
.btn-ghost {
  background: transparent;
  color: var(--brand-red);
  border: 1px solid transparent;
}
.btn-ghost:hover {
  background: var(--brand-red-muted);
}

/* DANGER — Destructive actions */
.btn-danger {
  background: var(--status-danger);
  color: white;
  border: 1px solid var(--status-danger);
}

/* LOCKED — Immutable state indicator (not a real button) */
.btn-locked {
  background: var(--surface-alt);
  color: var(--text-muted);
  border: 1px solid var(--border);
  cursor: not-allowed;
  gap: 6px; /* + lock icon */
}

/* LOADING STATE */
.btn[data-loading] {
  opacity: 0.75;
  cursor: wait;
  /* animated red dot spinner replaces icon */
}
```

**Icon Buttons (square):**

```css
.btn-icon {
  width: 32px;
  height: 32px;
  padding: 0;
}
.btn-icon-lg {
  width: 36px;
  height: 36px;
}
```

---

### 5.4 Form Controls

```css
/* INPUT */
.input {
  height: 36px;
  padding: 0 12px;
  border: 1px solid var(--border);
  border-radius: 4px;
  background: white;
  font: 400 0.875rem "IBM Plex Sans";
  color: var(--text-primary);
  transition:
    border-color 120ms,
    box-shadow 120ms;
}
.input:focus {
  outline: none;
  border-color: var(--brand-red);
  box-shadow: var(--shadow-focus);
}
.input::placeholder {
  color: var(--text-muted);
}
.input:disabled {
  background: var(--surface-alt);
  color: var(--text-muted);
  cursor: not-allowed;
}
.input.error {
  border-color: var(--status-danger);
}

/* TEXTAREA */
.textarea {
  /* same as input but height: auto, min-height: 80px, padding: 10px 12px */
  resize: vertical;
}

/* SELECT */
.select {
  /* same as input + custom chevron icon (brand-red) */
  appearance: none;
  background-image: url("chevron-down-red.svg");
  background-position: right 10px center;
  background-repeat: no-repeat;
  padding-right: 32px;
}

/* FINANCIAL AMOUNT INPUT */
.input-amount {
  font: 500 0.9375rem "IBM Plex Mono";
  text-align: right;
  letter-spacing: 0.02em;
}

/* LABEL */
.label {
  font: 500 0.8125rem/1.25rem "IBM Plex Sans";
  color: var(--text-secondary);
  margin-bottom: 6px;
  display: block;
}
.label.required::after {
  content: " *";
  color: var(--brand-red);
}

/* FORM GROUP */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 20px;
}

/* ERROR MESSAGE */
.form-error {
  font: 400 0.75rem "IBM Plex Sans";
  color: var(--status-danger);
  margin-top: 4px;
}

/* HELPER TEXT */
.form-hint {
  font: 400 0.75rem "IBM Plex Sans";
  color: var(--text-muted);
  margin-top: 4px;
}
```

---

### 5.5 Data Tables

Tables are the heart of LedgerScope. They must be immaculate.

```css
/* TABLE WRAPPER — always use this */
.table-wrapper {
  border: 1px solid var(--border);
  border-radius: 6px;
  overflow: hidden;
  background: white;
}

/* TABLE */
.data-table {
  width: 100%;
  border-collapse: collapse;
  font: 400 0.8125rem "IBM Plex Sans";
}

/* THEAD */
.data-table thead th {
  height: 40px;
  padding: 0 16px;
  text-align: left;
  background: var(--surface-alt);
  font: 500 0.75rem "IBM Plex Sans";
  color: var(--text-muted);
  letter-spacing: 0.04em;
  text-transform: uppercase;
  border-bottom: 1px solid var(--border-strong);
  white-space: nowrap;
  user-select: none;
}
/* Sortable header */
.data-table thead th.sortable {
  cursor: pointer;
}
.data-table thead th.sortable:hover {
  color: var(--text-primary);
}
.data-table thead th.sorted {
  color: var(--brand-red);
}
/* Amount columns — right-aligned */
.data-table thead th.amount {
  text-align: right;
}

/* TBODY */
.data-table tbody td {
  height: 48px;
  padding: 0 16px;
  border-bottom: 1px solid var(--border);
  color: var(--text-primary);
  vertical-align: middle;
}
/* Hover */
.data-table tbody tr:hover td {
  background: var(--surface-hover);
}
/* Last row no border */
.data-table tbody tr:last-child td {
  border-bottom: none;
}

/* SELECTED ROW */
.data-table tbody tr.selected td {
  background: var(--brand-red-muted);
}
.data-table tbody tr.selected td:first-child {
  border-left: 3px solid var(--brand-red);
  padding-left: 13px;
}

/* LOCKED ROW — posted/immutable */
.data-table tbody tr.locked td {
  color: var(--text-muted);
  background: var(--surface-alt);
}
.data-table tbody tr.locked td:first-child::before {
  content: "🔒";
  font-size: 10px;
  margin-right: 6px;
  opacity: 0.4;
}

/* AMOUNT CELLS */
td.amount {
  font: 400 0.875rem "IBM Plex Mono";
  text-align: right;
  white-space: nowrap;
}
td.amount.debit {
  color: var(--debit-color);
}
td.amount.credit {
  color: var(--credit-color);
}
td.amount.zero {
  color: var(--zero-color);
}
td.amount.balance {
  font-weight: 500;
}

/* TOTAL ROW */
.data-table tfoot tr.total td {
  font: 600 0.875rem "IBM Plex Mono";
  background: var(--surface-alt);
  border-top: 2px solid var(--border-strong);
  height: 44px;
}
.data-table tfoot tr.grand-total td {
  font: 700 0.9375rem "IBM Plex Mono";
  background: var(--shell-950);
  color: var(--text-inverse);
  border-top: 2px solid var(--brand-red);
}

/* EMPTY STATE */
.table-empty {
  padding: 48px 16px;
  text-align: center;
  color: var(--text-muted);
  font: 400 0.875rem "IBM Plex Sans";
}
```

**Journal Entry Lines Table — special treatment:**

```
Columns: # | Account Code | Account Name | Description | Debit | Credit
Debit/Credit inputs: inline editable, IBM Plex Mono, right-aligned
Running balance indicator: thin red line at bottom of lines block
Balance status: "✓ Balanced" (green) | "⚠ Unbalanced –IDR 500,000" (red)
```

---

### 5.6 Status Badges

```css
.badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 8px;
  border-radius: 3px; /* Slightly sharp — financial feel */
  font: 500 0.6875rem "IBM Plex Sans";
  letter-spacing: 0.04em;
  text-transform: uppercase;
  white-space: nowrap;
  border: 1px solid transparent;
}

/* Dot indicator */
.badge::before {
  content: "";
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

.badge-draft {
  background: var(--status-neutral-bg);
  color: var(--status-neutral);
  border-color: var(--status-neutral-border);
}
.badge-active {
  background: var(--status-success-bg);
  color: var(--status-success);
  border-color: var(--status-success-border);
}
.badge-posted {
  background: var(--status-success-bg);
  color: var(--status-success);
  border-color: var(--status-success-border);
}
.badge-review {
  background: var(--status-warning-bg);
  color: var(--status-warning);
  border-color: var(--status-warning-border);
}
.badge-rejected {
  background: var(--status-danger-bg);
  color: var(--status-danger);
  border-color: var(--status-danger-border);
}
.badge-locked {
  background: var(--status-locked-bg);
  color: var(--status-locked);
  border-color: var(--status-locked-border);
}
.badge-critical {
  background: var(--status-danger-bg);
  color: var(--status-danger);
  border-color: var(--brand-red);
  font-weight: 700;
}
.badge-high {
  background: var(--brand-red-muted);
  color: var(--brand-red);
  border-color: var(--brand-red-border);
}
```

---

### 5.7 Cards & Panels

```css
/* BASE CARD */
.card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 24px;
  box-shadow: var(--shadow-card);
}

/* CARD HEADER */
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
}
.card-title {
  font: 600 1rem "IBM Plex Sans";
  color: var(--text-primary);
}
.card-subtitle {
  font: 400 0.8125rem "IBM Plex Sans";
  color: var(--text-muted);
  margin-top: 2px;
}

/* STAT CARD — Dashboard KPIs */
.stat-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 6px;
  padding: 20px 24px;
  position: relative;
  overflow: hidden;
}
.stat-card .stat-value {
  font: 600 1.875rem "DM Serif Display";
  color: var(--text-primary);
  letter-spacing: -0.02em;
  line-height: 1.1;
}
.stat-card .stat-label {
  font: 400 0.8125rem "IBM Plex Sans";
  color: var(--text-muted);
  margin-top: 4px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-size: 0.6875rem;
}
.stat-card .stat-change {
  font: 500 0.8125rem "IBM Plex Mono";
  margin-top: 12px;
}
.stat-change.up {
  color: var(--status-success);
}
.stat-change.down {
  color: var(--status-danger);
}

/* Red left-accent stripe on stat cards */
.stat-card.primary::before {
  content: "";
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: var(--brand-red);
  border-radius: 6px 0 0 6px;
}

/* ALERT / CALLOUT */
.alert {
  padding: 12px 16px;
  border-radius: 4px;
  border-left: 3px solid;
  font: 400 0.875rem "IBM Plex Sans";
  display: flex;
  gap: 10px;
  align-items: flex-start;
}
.alert-info {
  background: var(--status-info-bg);
  border-color: var(--status-info);
  color: var(--status-info);
}
.alert-warning {
  background: var(--status-warning-bg);
  border-color: var(--status-warning);
  color: var(--status-warning);
}
.alert-danger {
  background: var(--status-danger-bg);
  border-color: var(--status-danger);
  color: var(--status-danger);
}
.alert-success {
  background: var(--status-success-bg);
  border-color: var(--status-success);
  color: var(--status-success);
}
```

---

### 5.8 Modal / Dialog

```css
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(12, 13, 16, 0.72); /* shell-950 with opacity */
  backdrop-filter: blur(2px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.modal {
  background: white;
  border-radius: 8px;
  box-shadow: var(--shadow-modal);
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: modal-in 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modal-in {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(8px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Modal sizes */
.modal-sm {
  max-width: 440px;
}
.modal-md {
  max-width: 600px;
}
.modal-lg {
  max-width: 800px;
}
.modal-xl {
  max-width: 1100px;
}

/* Modal header */
.modal-header {
  padding: 20px 24px 16px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.modal-title {
  font: 600 1.0625rem "IBM Plex Sans";
  color: var(--text-primary);
}

/* Destructive modal header */
.modal-header.destructive {
  border-left: 4px solid var(--brand-red);
}

/* Modal body */
.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

/* Modal footer */
.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid var(--border);
  display: flex;
  gap: 8px;
  justify-content: flex-end;
  background: var(--surface-alt);
}
```

---

### 5.9 Dropdown Menu

```css
.dropdown {
  position: absolute;
  background: white;
  border: 1px solid var(--border);
  border-radius: 6px;
  box-shadow: var(--shadow-dropdown);
  min-width: 180px;
  padding: 4px;
  z-index: 50;
  animation: dropdown-in 120ms ease;
}
@keyframes dropdown-in {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-item {
  padding: 7px 12px;
  border-radius: 3px;
  font: 400 0.8125rem "IBM Plex Sans";
  color: var(--text-primary);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}
.dropdown-item:hover {
  background: var(--surface-hover);
}
.dropdown-item.danger {
  color: var(--status-danger);
}
.dropdown-item.danger:hover {
  background: var(--status-danger-bg);
}
.dropdown-separator {
  height: 1px;
  background: var(--border);
  margin: 4px;
}
```

---

### 5.10 Tabs

```css
/* UNDERLINE TABS — Default for module sub-sections */
.tabs-underline {
  display: flex;
  border-bottom: 1px solid var(--border);
  gap: 0;
}
.tab-item {
  padding: 10px 16px;
  font: 400 0.875rem "IBM Plex Sans";
  color: var(--text-muted);
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  cursor: pointer;
  transition: all 140ms;
}
.tab-item:hover {
  color: var(--text-primary);
}
.tab-item.active {
  color: var(--brand-red);
  border-bottom-color: var(--brand-red);
  font-weight: 500;
}

/* PILL TABS — Compact filter tabs */
.tabs-pill {
  display: flex;
  gap: 4px;
}
.tab-pill {
  padding: 6px 12px;
  border-radius: 4px;
  font: 400 0.8125rem "IBM Plex Sans";
  color: var(--text-secondary);
  cursor: pointer;
}
.tab-pill:hover {
  background: var(--surface-hover);
}
.tab-pill.active {
  background: var(--brand-red-muted);
  color: var(--brand-red);
  font-weight: 500;
}
```

---

### 5.11 Progress Tracker

For quarterly closing checklist and engagement progress:

```css
.progress-tracker {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.progress-step {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 4px;
  position: relative;
}

/* Connector line */
.progress-step:not(:last-child)::after {
  content: "";
  position: absolute;
  left: 21px;
  top: 36px;
  bottom: -10px;
  width: 1px;
  background: var(--border);
}

.step-indicator {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: 2px solid var(--border);
  background: white;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  font: 500 0.625rem "IBM Plex Mono";
  color: var(--text-muted);
  margin-top: 1px;
  z-index: 1;
}

/* Completed step */
.progress-step.completed .step-indicator {
  background: var(--status-success);
  border-color: var(--status-success);
  color: white;
}
.progress-step.completed .step-label {
  color: var(--text-muted);
  text-decoration: line-through;
}

/* Active step */
.progress-step.active {
  background: var(--brand-red-muted);
}
.progress-step.active .step-indicator {
  background: var(--brand-red);
  border-color: var(--brand-red);
  color: white;
}
.progress-step.active .step-label {
  font-weight: 500;
  color: var(--brand-red);
}
.progress-step.active::after {
  background: var(--brand-red);
  opacity: 0.3;
}

.step-label {
  font: 400 0.875rem "IBM Plex Sans";
  color: var(--text-primary);
}
.step-meta {
  font: 400 0.75rem "IBM Plex Sans";
  color: var(--text-muted);
  margin-top: 2px;
}
```

---

### 5.12 File Upload Zone

```css
.upload-zone {
  border: 2px dashed var(--border-strong);
  border-radius: 6px;
  padding: 32px;
  text-align: center;
  cursor: pointer;
  transition: all 160ms;
  background: var(--surface-alt);
}
.upload-zone:hover {
  border-color: var(--brand-red);
  background: var(--brand-red-muted);
}
.upload-zone.dragging {
  border-color: var(--brand-red);
  background: var(--brand-red-muted);
  box-shadow: inset 0 0 0 4px rgba(192, 25, 10, 0.08);
}
.upload-zone .upload-icon {
  color: var(--brand-red);
  font-size: 32px;
  margin-bottom: 12px;
}
.upload-zone .upload-text {
  font: 500 0.875rem "IBM Plex Sans";
  color: var(--text-primary);
}
.upload-zone .upload-hint {
  font: 400 0.75rem "IBM Plex Sans";
  color: var(--text-muted);
  margin-top: 4px;
}
```

---

### 5.13 Search & Filter Bar

```
┌──────────────────────────────────────────────────────────┐
│ 🔍 Search journals...  │ Period ▾ │ Status ▾ │ + Filter  │
└──────────────────────────────────────────────────────────┘

Height: 44px container
Search input: flex-1, border-right 1px
Filter pills: shown below bar when active
Active filter: badge-style with × remove
```

---

### 5.14 Pagination

```css
.pagination {
  display: flex;
  align-items: center;
  gap: 4px;
  font: 400 0.8125rem "IBM Plex Sans";
}
.page-info {
  color: var(--text-muted);
  margin-right: 8px;
}
.page-btn {
  min-width: 32px;
  height: 32px;
  border-radius: 4px;
  border: 1px solid var(--border);
  background: white;
  color: var(--text-secondary);
  display: grid;
  place-items: center;
  cursor: pointer;
  font: 400 0.8125rem "IBM Plex Mono";
}
.page-btn:hover {
  background: var(--surface-hover);
}
.page-btn.active {
  background: var(--brand-red);
  color: white;
  border-color: var(--brand-red);
  font-weight: 500;
}
.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
```

---

## 6. Page-Level Design Templates

### 6.1 Dashboard Page

```
┌─────────────────────────────────────────────────────────┐
│ Dashboard                        Period: Q1 2026 ▾      │
├─────────────────────────────────────────────────────────┤
│ ┌───────────┐ ┌───────────┐ ┌───────────┐ ┌──────────┐ │
│ │ Revenue   │ │ Expenses  │ │ Net Profit│ │ Cash Pos.│ │
│ │ IDR 2.4M  │ │ IDR 1.8M  │ │ IDR 600K  │ │ IDR 1.2M │ │
│ │ +12.4% ↑  │ │ +3.2% ↑   │ │ +24.1% ↑  │ │ Healthy  │ │
│ └───────────┘ └───────────┘ └───────────┘ └──────────┘ │
├───────────────────────────┬─────────────────────────────┤
│  Revenue vs Expenses      │  Quarterly Progress         │
│  [Line chart, 12 months]  │  Q1 ████████░░ 78%          │
│                           │  Q2 ░░░░░░░░░░              │
│                           │  Q3 ░░░░░░░░░░              │
│                           │  Q4 ░░░░░░░░░░              │
├───────────────────────────┴─────────────────────────────┤
│  Recent Activity                    Open Tasks (12)      │
│  [Timeline of recent actions]       [Task checklist]    │
└─────────────────────────────────────────────────────────┘
```

**KPI Card with Red accent stripe (left border):**

- Stat value: DM Serif Display 30px
- Period label: uppercase, 11px, muted
- Trend: IBM Plex Mono 13px, colored

---

### 6.2 Journal Entry List Page

```
┌─────────────────────────────────────────────────────────┐
│ Journal Entries           [+ New Journal] [↑ Import]   │
├─────────────────────────────────────────────────────────┤
│ [Search] [Period: Q1 2026 ▾] [Status: All ▾] [Filter]  │
├─────────────────────────────────────────────────────────┤
│ #       │ DATE      │ DESCRIPTION    │ DEBIT   │STATUS  │
│ JE-0042 │ 2026-01-15│ Accrued salary │2,500,000│POSTED  │
│ JE-0041 │ 2026-01-14│ Office supply  │  450,000│APPROVED│
│ JE-0040 │ 2026-01-10│ Depreciation   │1,200,000│POSTED  │
│ JE-0039 │ 2026-01-08│ Bank fee       │   25,000│DRAFT   │
├─────────────────────────────────────────────────────────┤
│ Showing 1–25 of 142          [< 1 2 3 ... 6 >]         │
└─────────────────────────────────────────────────────────┘
```

---

### 6.3 Journal Entry Detail / Form Page

```
┌─────────────────────────────────────────────────────────┐
│ ← Journal Entries    JE-0042         [POSTED] 🔒        │
├───────────────────────────┬─────────────────────────────┤
│ JOURNAL INFORMATION       │ WORKFLOW                    │
│                           │                             │
│ Number:  JE-0042          │ ✓ Prepared by Rina Sari    │
│ Date:    15 Jan 2026      │   Jan 15, 10:32 AM          │
│ Period:  January 2026     │ ✓ Reviewed by Ahmad D.      │
│ Status:  POSTED           │   Jan 15, 14:15 PM          │
│                           │ ✓ Posted by Ahmad D.        │
│ Description:              │   Jan 15, 14:16 PM          │
│ Accrued salary Q4...      │                             │
├───────────────────────────┴─────────────────────────────┤
│ JOURNAL LINES                                           │
│                                                         │
│ #  │ CODE    │ ACCOUNT            │ DEBIT      │ CREDIT │
│ 1  │ 5110    │ Salary Expense     │ 2,500,000  │        │
│ 2  │ 2120    │ Accrued Liabilities│            │2,500,000│
│ ────────────────────────────────────────────────────── │
│ TOTAL                              2,500,000   2,500,000│
│                                              ✓ Balanced │
└─────────────────────────────────────────────────────────┘
```

---

### 6.4 Trial Balance Page

```
┌─────────────────────────────────────────────────────────┐
│ Trial Balance — Q1 2026           [Generate] [Export ▾] │
├─────────────────────────────────────────────────────────┤
│ ACCOUNT                     │OPENING  │MOVEMENT │ENDING │
│                             │DR    CR │DR    CR │DR  CR │
├─────────────────────────────┼─────────┼─────────┼───────┤
│ ASSETS                      │         │         │       │
│ 1100  Cash & Bank           │2,450,000│  500,000│2,950,000│
│ 1200  Accounts Receivable   │1,800,000│  300,000│2,100,000│
│                             │         │         │       │
│ LIABILITIES                 │         │         │       │
│ 2100  Accounts Payable      │         │  750,000│  750,000│
│ ...                         │         │         │       │
├─────────────────────────────┼─────────┼─────────┼───────┤
│ TOTAL                       │4,250,000│4,250,000│4,250,000│
│                                              ✓ BALANCED  │
└─────────────────────────────────────────────────────────┘
```

Account group headers use DM Serif Display italic, lightweight.

---

### 6.5 Quarterly Closing Page

```
┌─────────────────────────────────────────────────────────┐
│ Quarterly Closing — Q1 2026       Status: IN PROGRESS   │
├──────────────────────┬──────────────────────────────────┤
│ CLOSING CHECKLIST    │  QUARTERLY SUMMARY               │
│                      │                                  │
│ ✓ Journals posted    │  Revenue     IDR 2,450,000       │
│ ✓ Trial balanced     │  Expenses    IDR 1,890,000       │
│ ✓ Bank reconciled    │  Net Profit  IDR   560,000       │
│ ○ AR reconciled  ←─  │  Margin      22.9%               │
│   [In Progress]      │                                  │
│ ○ Accruals posted    │  [View Financial Statement]      │
│ ○ Manager review     │                                  │
│ ○ Quarter lock       │  REVIEW NOTES (3 open)          │
│                      │  ─────────────────────          │
│ Progress: 3/8        │  ● Depreciation entry needs...  │
│ ████████░░░░ 38%     │  ● AR balance doesn't match...  │
│                      │  ● Tax provision unclear         │
│ [Approve & Lock ▶]   │                                  │
└──────────────────────┴──────────────────────────────────┘
```

**Quarter Lock Confirmation Modal:**

```
┌──────────────────────────────────────┐
│ 🔒 Lock Quarter Q1 2026              │
├──────────────────────────────────────┤
│ This action is permanent. Locked     │
│ quarters cannot be edited without    │
│ explicit unlock approval.            │
│                                      │
│ Checklist: 8/8 complete ✓           │
│ Pending journals: 0 ✓               │
│ Open review notes: 0 ✓              │
│                                      │
│ Type "Q1 2026" to confirm:           │
│ [______________________________]     │
├──────────────────────────────────────┤
│                [Cancel] [Lock Now →] │
└──────────────────────────────────────┘
```

Lock button only becomes active when text matches. Red border on confirm field.

---

### 6.6 Evidence Upload (Client View)

```
┌─────────────────────────────────────────────────────────┐
│ Document Request #DR-0024          Due: 20 Jan 2026     │
│ Bank Statement — December 2025     Priority: HIGH       │
├─────────────────────────────────────────────────────────┤
│ Requested by: Ahmad Darwis (Auditor)                    │
│ Description: Please provide the official bank statement │
│ for account BCA 123-456-7890 for December 2025.         │
├─────────────────────────────────────────────────────────┤
│ UPLOADED FILES                     Version History      │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 📄 bank_stmt_dec2025_v2.pdf     ACCEPTED ✓  v2      │ │
│ │    Uploaded Jan 15 · 2.4 MB                         │ │
│ └─────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 📄 bank_stmt_dec2025.pdf        REJECTED ✗  v1      │ │
│ │    Reason: File is password-protected.              │ │
│ └─────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────┤
│ UPLOAD NEW VERSION                                      │
│ ┌─────────────────────────────────────────────────────┐ │
│ │          Drop file here or click to browse          │ │
│ │       PDF, XLSX, DOCX · Max 25 MB                   │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

---

### 6.7 Working Paper Page

```
┌─────────────────────────────────────────────────────────┐
│ WP-C.1.1  Cash & Bank — Year End Cut-off Test          │
│ Engagement: Audit PT Maju 2025          [REVIEWED] ✓   │
├──────────────────────────┬──────────────────────────────┤
│ WORKING PAPER            │ SIGN-OFF TRAIL               │
│                          │                              │
│ Audit Area: Cash & Bank  │ Prepared:  Budi Santoso     │
│ Period:     Dec 2025     │            Jan 12, 09:15     │
│ Linked:     3 evidence   │                              │
│             JE-0038..42  │ Reviewed:  Ahmad Darwis     │
│                          │            Jan 14, 16:30     │
├──────────────────────────┤                              │
│ OBJECTIVE                │ Manager:   [PENDING]        │
│ To verify that cash...   │                              │
│                          │ [Approve Working Paper →]   │
├──────────────────────────┤                              │
│ PROCEDURES               │ REVIEW NOTES (2 resolved)   │
│ ✓ Inspect bank stmt      │ ────────────────────────── │
│ ✓ Trace to GL balance    │ ✓ Confirm statement date    │
│ ✓ Verify cutoff date     │ ✓ Missing sign on p.3       │
├──────────────────────────┤                              │
│ CONCLUSION               │                              │
│ Based on procedures...   │                              │
└──────────────────────────┴──────────────────────────────┘
```

---

### 6.8 Audit Findings Page

```
┌─────────────────────────────────────────────────────────┐
│ Audit Findings (12)          [+ New Finding] [Export]  │
├─────────────────────────────────────────────────────────┤
│ [All ▾] [CRITICAL 2] [HIGH 3] [MEDIUM 5] [LOW 2]       │
├──────┬──────────────────────────────┬────────┬──────────┤
│ REF  │ FINDING                      │SEVERITY│ STATUS   │
│ F-01 │ Segregation of duties in     │CRITICAL│ OPEN     │
│      │ AP processing missing        │        │          │
│ F-02 │ Journal approval bypassed    │HIGH    │ IN PROG. │
│      │ for 4 transactions           │        │          │
│ F-03 │ Bank recon delayed >30 days  │MEDIUM  │ RESOLVED │
│ F-04 │ Invoice filing incomplete    │LOW     │ CLOSED   │
└──────┴──────────────────────────────┴────────┴──────────┘
```

Critical findings row: subtle red-left-border on row.

---

## 7. Financial Data Visualization

### 7.1 Chart Style Guide

```
Library: Chart.js or lightweight SVG-based custom
Background: transparent (card provides white bg)
Grid lines: var(--border), 1px, dashed
Axis labels: IBM Plex Sans 11px, text-muted
Legend: IBM Plex Sans 12px, horizontal, below chart
Tooltip: card-style, white bg, border, shadow-dropdown

Chart colors (ordered):
  1. var(--brand-red)     #C0190A   — Primary metric
  2. #374151              (dark)    — Secondary metric
  3. #059669              (green)   — Positive comparative
  4. #D97706              (amber)   — Warning metric
  5. #8C93A0              (gray)    — Muted/baseline
```

### 7.2 Chart Types Per Module

| Module              | Chart Type         | Primary Color     |
| ------------------- | ------------------ | ----------------- |
| Revenue trend       | Line (smooth)      | brand-red         |
| Expense trend       | Line               | shell-500         |
| Profit/Loss         | Bar (grouped)      | brand-red + gray  |
| Cash flow           | Area (stacked)     | brand-red         |
| Ratio analysis      | Gauge or bullet    | contextual        |
| Quarter comparison  | Bar (side-by-side) | brand-red + shell |
| Risk heatmap        | 5×5 matrix         | gray to red       |
| Engagement progress | Horizontal bar     | brand-red         |

### 7.3 Risk Heatmap (5×5 Grid)

```
IMPACT →     1      2      3      4      5
LIKELIHOOD
    5     [Med]  [High] [High] [Crit] [Crit]
    4     [Low]  [Med]  [High] [High] [Crit]
    3     [Low]  [Low]  [Med]  [High] [High]
    2     [Min]  [Low]  [Low]  [Med]  [High]
    1     [Min]  [Min]  [Low]  [Low]  [Med]

Colors:
  Min/Low  → var(--status-neutral-bg) + gray text
  Medium   → var(--status-warning-bg) + warning text
  High     → var(--brand-red-muted) + brand-red text
  Critical → var(--brand-red) + white text

Risks plotted as circles with reference code (F-01, F-02...)
```

---

## 8. Micro-interactions & Motion

### 8.1 Animation Principles

```
Duration scale:
  Instant:     0ms    — toggle, select
  Fast:        120ms  — button press, badge state
  Default:     180ms  — hover, fade
  Moderate:    280ms  — modal open, slide panel
  Slow:        400ms  — page transition, skeleton load

Easing:
  Entrance:    cubic-bezier(0.16, 1, 0.3, 1)   — snappy deceleration
  Exit:        cubic-bezier(0.4, 0, 1, 1)       — quick exit
  Default:     ease                              — hover states
```

### 8.2 Key Interactions

```css
/* Journal Balance Indicator — animates on amount change */
.balance-indicator {
  transition:
    color 180ms ease,
    transform 180ms ease;
}
.balance-indicator.balanced {
  color: var(--status-success);
}
.balance-indicator.unbalanced {
  color: var(--status-danger);
  animation: shake 300ms;
}
@keyframes shake {
  0%,
  100% {
    transform: translateX(0);
  }
  20% {
    transform: translateX(-3px);
  }
  40% {
    transform: translateX(3px);
  }
  60% {
    transform: translateX(-2px);
  }
  80% {
    transform: translateX(2px);
  }
}

/* Status badge transition */
.badge {
  transition:
    background 200ms,
    color 200ms;
}

/* Table row reveal on load */
.data-table tbody tr {
  animation: row-in 200ms ease both;
}
.data-table tbody tr:nth-child(1) {
  animation-delay: 20ms;
}
.data-table tbody tr:nth-child(2) {
  animation-delay: 40ms;
}
.data-table tbody tr:nth-child(3) {
  animation-delay: 60ms;
}
/* ... max delay: 200ms */
@keyframes row-in {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Page load — content fade in */
.page-content {
  animation: page-in 280ms cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes page-in {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Sidebar collapse */
.sidebar {
  transition: width 240ms cubic-bezier(0.16, 1, 0.3, 1);
}

/* Notification badge bounce on new notification */
.notification-badge.new {
  animation: badge-pop 400ms cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes badge-pop {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.4);
  }
  100% {
    transform: scale(1);
  }
}
```

---

## 9. Iconography

### 9.1 Icon Library

Use **Lucide Icons** (already in Vue 3 ecosystem via `lucide-vue-next`).

```typescript
// Standard icon sizes
icon-xs:  12px
icon-sm:  14px
icon-md:  16px  // Default
icon-lg:  20px
icon-xl:  24px
```

### 9.2 Module Icons

```
Dashboard        → LayoutDashboard
Companies        → Building2
Fiscal Year      → CalendarDays
Journal Entries  → BookOpen
Chart of Accounts→ List
Trial Balance    → Scale
Reconciliation   → ArrowLeftRight
Financial Stmt   → FileBarChart2
Analysis         → TrendingUp
Engagements      → Briefcase
Working Papers   → FileText
Evidence         → Paperclip
Review Notes     → MessageSquare
Findings         → AlertTriangle
Controls         → ShieldCheck
Reports          → Download
Notifications    → Bell
Settings         → Settings2
Audit Log        → ActivitySquare
Lock             → Lock
Unlock           → Unlock
Posted           → CheckCircle2
Rejected         → XCircle
User             → UserCircle
```

### 9.3 Financial Status Icons

```
Balanced ✓   → CheckCircle2, status-success
Unbalanced ⚠ → AlertCircle, status-danger
Locked 🔒    → Lock, text-muted
Posted       → CheckCircle2, status-success
Draft        → Circle (outline), text-muted
Critical     → AlertOctagon, brand-red
```

---

## 10. Responsive Behavior

### 10.1 Breakpoints

```css
sm:  640px   /* Sidebar collapses to icon-only */
md:  768px   /* Single-column layouts */
lg:  1024px  /* Standard desktop */
xl:  1280px  /* Comfortable ERP layout */
2xl: 1536px  /* Ultra-wide, max content width cap */
```

### 10.2 Sidebar Behavior

```
Desktop (≥ lg):  240px expanded sidebar, always visible
Tablet (md):     64px icon-only sidebar
Mobile (< md):   Drawer overlay, triggered by hamburger
```

### 10.3 Table Behavior on Small Screens

```
< md: Priority columns only (hide secondary columns)
      Horizontal scroll with sticky first column (account/name)
      Row tap → slide-up detail drawer
```

---

## 11. Empty & Loading States

### 11.1 Loading Skeleton

```css
/* Skeleton shimmer — never use spinners for table data */
.skeleton {
  background: linear-gradient(
    90deg,
    var(--border) 25%,
    var(--surface-alt) 50%,
    var(--border) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.6s infinite;
  border-radius: 3px;
}
@keyframes shimmer {
  from {
    background-position: 200% 0;
  }
  to {
    background-position: -200% 0;
  }
}

/* Table skeleton rows */
.skeleton-row {
  height: 48px;
}
.skeleton-text-sm {
  height: 12px;
  width: 60%;
}
.skeleton-text-lg {
  height: 14px;
  width: 80%;
}
.skeleton-amount {
  height: 14px;
  width: 100px;
  margin-left: auto;
}
```

### 11.2 Empty States

```
No Journals:
  Icon: BookOpen (48px, text-muted)
  Title: "No journal entries"
  Body: "Create your first journal entry or import from Excel."
  CTA: [+ New Journal] [Import from Excel]

No Engagements:
  Icon: Briefcase
  Title: "No engagements yet"
  Body: "Create an engagement to start the audit workflow."
  CTA: [+ Create Engagement]

No Evidence:
  Icon: Paperclip
  Title: "No evidence uploaded"
  Body: "Upload supporting documents for this request."
  CTA: [Upload Evidence]

Locked Period (write blocked):
  Icon: Lock (32px, brand-red)
  Title: "This period is locked"
  Body: "Q1 2026 was locked on 15 Feb 2026 by Ahmad Darwis."
  CTA: [Request Unlock] (if permission)
```

---

## 12. Authentication Pages

### 12.1 Login Page

```
Layout: Split — left dark (shell-950), right white
Left side (40%):
  - LedgerScope logo (large, white + red)
  - Tagline: "Financial Precision. Audit Confidence."
  - Geometric red accent — thin diagonal line, top-right to bottom corner
  - Recent platform stats (animated numbers, subtle)

Right side (60%):
  - Centered login card (max-width: 380px)
  - "Welcome back" in DM Serif Display 28px
  - Email + Password inputs
  - "Forgot password" link (text-muted, right-aligned)
  - [Sign In] button — brand-red, full-width, 42px
  - MFA step: appears below password, slide-down animation
  - No social login buttons
  - No "Remember me" checkbox (handled by session)
```

### 12.2 Login Page CSS

```css
.auth-shell {
  display: grid;
  grid-template-columns: 40% 60%;
  min-height: 100vh;
}
.auth-left {
  background: var(--shell-950);
  position: relative;
  overflow: hidden;
  padding: 48px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
/* Diagonal red accent */
.auth-left::after {
  content: "";
  position: absolute;
  top: 0;
  right: -60px;
  width: 2px;
  height: 100%;
  background: var(--brand-red);
  transform: rotate(-8deg);
  transform-origin: top right;
  opacity: 0.4;
}
.auth-tagline {
  font: 400 1.875rem "DM Serif Display";
  color: white;
  line-height: 1.3;
}
.auth-tagline em {
  font-style: italic;
  color: var(--brand-red);
}
.auth-right {
  background: var(--page-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 48px;
}
.auth-card {
  background: white;
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 40px;
  width: 100%;
  max-width: 380px;
  box-shadow: var(--shadow-modal);
}
```

---

## 13. Tailwind CSS Utility Classes (Custom)

Add to your components layer:

```css
@layer components {
  /* Financial amount display */
  .amount-positive {
    @apply font-mono text-emerald-700 tabular-nums;
  }
  .amount-negative {
    @apply font-mono text-brand-600 tabular-nums;
  }
  .amount-zero {
    @apply font-mono text-shell-400 tabular-nums;
  }
  .amount-bold {
    @apply font-mono font-semibold tabular-nums;
  }

  /* Page section header */
  .page-header {
    @apply flex items-center justify-between mb-6;
  }
  .page-title {
    @apply text-xl font-semibold text-shell-950 tracking-tight;
  }
  .page-subtitle {
    @apply text-sm text-shell-500 mt-0.5;
  }

  /* Section divider with label */
  .section-divider {
    @apply flex items-center gap-3 my-6;
  }
  .section-divider::before,
  .section-divider::after {
    content: "";
    @apply flex-1 border-t border-shell-200;
  }
  .section-divider span {
    @apply text-xs font-medium uppercase tracking-wider text-shell-400;
  }

  /* Red focus ring — apply to all focusable elements */
  .focus-brand {
    @apply focus:outline-none focus:ring-2 focus:ring-brand-600/20 focus:border-brand-600;
  }

  /* Locked overlay — apply to locked containers */
  .locked-overlay {
    @apply relative pointer-events-none;
  }
  .locked-overlay::after {
    content: "";
    @apply absolute inset-0 bg-white/60 backdrop-blur-[1px] rounded-md z-10;
  }
}
```

---

## 14. Component File Structure (Vue 3)

```
resources/js/
├── Components/
│   ├── UI/                      ← Base components
│   │   ├── AppButton.vue
│   │   ├── AppInput.vue
│   │   ├── AppSelect.vue
│   │   ├── AppModal.vue
│   │   ├── AppBadge.vue
│   │   ├── AppCard.vue
│   │   ├── AppTable.vue
│   │   ├── AppPagination.vue
│   │   ├── AppDropdown.vue
│   │   ├── AppTabs.vue
│   │   └── AppAlert.vue
│   │
│   ├── Accounting/
│   │   ├── JournalLinesTable.vue
│   │   ├── BalanceIndicator.vue
│   │   ├── TrialBalanceTable.vue
│   │   ├── AmountCell.vue          ← Reusable debit/credit cell
│   │   └── PeriodStatusBadge.vue
│   │
│   ├── Audit/
│   │   ├── EvidenceCard.vue
│   │   ├── WorkingPaperCard.vue
│   │   ├── ReviewNoteThread.vue
│   │   ├── FindingCard.vue
│   │   └── SignOffTrail.vue
│   │
│   ├── Charts/
│   │   ├── RevenueLineChart.vue
│   │   ├── ProfitBarChart.vue
│   │   ├── RiskHeatmap.vue
│   │   └── EngagementProgress.vue
│   │
│   └── Shared/
│       ├── FileUploadZone.vue
│       ├── FilterBar.vue
│       ├── ProgressTracker.vue
│       ├── CompanySwitcher.vue
│       └── NotificationBell.vue
│
├── Layouts/
│   ├── AppLayout.vue              ← Main shell (sidebar + topbar)
│   ├── AuthLayout.vue             ← Login/password pages
│   └── ClientLayout.vue           ← Client portal layout
│
└── Pages/
    ├── Auth/
    ├── Dashboard/
    ├── Companies/
    ├── Accounting/
    │   ├── FiscalYears/
    │   ├── Journals/
    │   ├── ChartOfAccounts/
    │   ├── TrialBalance/
    │   └── QuarterClosing/
    ├── FinancialStatements/
    ├── Analysis/
    ├── Engagements/
    ├── Evidence/
    ├── WorkingPapers/
    ├── Findings/
    ├── Reports/
    ├── Client/
    └── Admin/
```

---

## 15. Design Tokens Summary (Quick Reference)

```
BRAND
  Primary:        #C0190A  (brand-600)
  Dark:           #8B1208  (brand-800)
  Muted BG:       #F8E8E7  (brand-100)

SHELL (Sidebar)
  Background:     #0C0D10
  Surface:        #13141A
  Elevated:       #1A1C23
  Border:         #252830
  Text:           #F5F6F8
  Text Muted:     #7B8190

CONTENT
  Page BG:        #F4F5F7
  Card/Surface:   #FFFFFF
  Alt Surface:    #F9FAFB
  Border:         #E3E5E9
  Border Strong:  #CDD0D6

TEXT (on white)
  Primary:        #0F1114
  Secondary:      #4A5261
  Muted:          #8C93A0

FINANCIAL
  Debit/Positive: #0D6B3E
  Credit/Negative:#C0190A
  Zero:           #8C93A0

FONTS
  Display:        DM Serif Display
  UI/Body:        IBM Plex Sans
  Numbers:        IBM Plex Mono

BORDER RADIUS
  Buttons/Inputs: 4px
  Cards/Panels:   6px
  Modals:         8px
  Badges:         3px
```

---

_This DESIGN.md is the single visual source of truth for LedgerScope frontend. All components, pages, and interactions must follow these specifications. When in doubt: subtract, not add. Less is more precise._
