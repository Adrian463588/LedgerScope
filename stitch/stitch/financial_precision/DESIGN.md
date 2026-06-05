---
name: Financial Precision
colors:
  surface: '#f8f9fb'
  surface-dim: '#d9dadc'
  surface-bright: '#f8f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f6'
  surface-container: '#edeef0'
  surface-container-high: '#e7e8ea'
  surface-container-highest: '#e1e2e4'
  on-surface: '#191c1e'
  on-surface-variant: '#5c403b'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f3'
  outline: '#916f6a'
  outline-variant: '#e5bdb7'
  surface-tint: '#bc1507'
  primary: '#970600'
  on-primary: '#ffffff'
  primary-container: '#c0190a'
  on-primary-container: '#ffd3cb'
  inverse-primary: '#ffb4a7'
  secondary: '#5e5e62'
  on-secondary: '#ffffff'
  secondary-container: '#e0dfe3'
  on-secondary-container: '#626266'
  tertiary: '#00552f'
  on-tertiary: '#ffffff'
  tertiary-container: '#156f42'
  on-tertiary-container: '#9aefb6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdad4'
  primary-fixed-dim: '#ffb4a7'
  on-primary-fixed: '#400100'
  on-primary-fixed-variant: '#920600'
  secondary-fixed: '#e3e2e6'
  secondary-fixed-dim: '#c7c6ca'
  on-secondary-fixed: '#1b1b1f'
  on-secondary-fixed-variant: '#46464a'
  tertiary-fixed: '#a0f5bb'
  tertiary-fixed-dim: '#84d8a1'
  on-tertiary-fixed: '#00210f'
  on-tertiary-fixed-variant: '#00522d'
  background: '#f8f9fb'
  on-background: '#191c1e'
  surface-variant: '#e1e2e4'
  brand-red-hover: '#A31508'
  brand-red-press: '#8B1208'
  brand-red-muted: '#F8E8E7'
  brand-red-border: '#EBBAB7'
  shell-surface: '#13141A'
  shell-elevated: '#1A1C23'
  shell-border: '#252830'
  text-primary: '#0F1114'
  text-secondary: '#4A5261'
  text-muted: '#8C93A0'
  status-warning: '#B45309'
  status-danger: '#DC2626'
  border-default: '#E3E5E9'
  border-strong: '#CDD0D6'
typography:
  display-lg:
    fontFamily: DM Serif Display
    fontSize: 36px
    fontWeight: '600'
    lineHeight: 40px
  display-md:
    fontFamily: DM Serif Display
    fontSize: 30px
    fontWeight: '600'
    lineHeight: 36px
  heading-lg:
    fontFamily: IBM Plex Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-md:
    fontFamily: IBM Plex Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 24px
  mono-md:
    fontFamily: IBM Plex Mono
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 24px
  label-lg:
    fontFamily: IBM Plex Sans
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
  label-sm:
    fontFamily: IBM Plex Sans
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.08em
spacing:
  baseline: 8px
  sub-unit: 4px
  gutter-desktop: 32px
  gutter-tablet: 24px
  card-padding: 24px
  table-cell-v: 12px
  table-cell-h: 16px
  form-gap: 20px
---

## Brand & Style
The brand personality is **Authoritative, Disciplined, and Precise**. It is designed for high-stakes financial environments where accuracy, traceability, and accountability are paramount. The system avoids the "playfulness" of consumer SaaS, opting instead for a serious, information-dense aesthetic that mirrors the gravitas of financial journalism and legal documentation.

The design style is **Editorial Minimalism**. It prioritizes high data density and legibility through a strict 8px baseline grid. The visual language is "anti-decorative"—every element must earn its place. Key characteristics include:
- **High-Contrast Typography:** Pairing a traditional serif for authority with a technical sans-serif for precision.
- **Structural Rigidity:** Sharp corners and heavy vertical dividers to convey immutability and order.
- **Semantic Restraint:** Brand colors are used sparingly, ensuring red denotes either primary action or specific financial meaning (Credits/Alerts) without overwhelming the data.

## Colors
This system uses a high-contrast palette to separate the "App Shell" from the "Content Area."

- **Primary Red (#C0190A):** Reserved for core brand identity, primary calls to action, and representing "Credit" or "Active" states. It should never be used decoratively; a maximum of three red elements per screen is recommended.
- **Dark Shell (#0C0D10):** A constant dark background for sidebars and primary navigation, providing a focused frame for financial data.
- **Surface & Backgrounds:** The main page uses a light gray background (`#F4F5F7`), while interactive cards and panels use pure white (`#FFFFFF`) to maximize contrast for data entry.
- **Semantic Logic:** Green (`#0D6B3E`) is used for "Debits" and success states. Red is context-dependent, switching between "Brand Identity" and "Financial Credit/Error."

## Typography
The typographic system establishes a hierarchy of "Editorial Authority."

- **DM Serif Display:** Used exclusively for high-level page titles and dashboard KPIs to evoke the feeling of a financial report.
- **IBM Plex Sans:** The workhorse for the UI. It provides technical precision and clarity for form fields, labels, and standard body text.
- **IBM Plex Mono:** Essential for all financial figures, account codes, and timestamps. Monospacing ensures that decimal points align vertically in tables, which is critical for rapid visual scanning of ledgers.
- **Alignment Rule:** Numbers must be right-aligned; labels must be left-aligned.

## Layout & Spacing
The system utilizes a **Fixed Grid** model within a max content width of 1440px, optimized for desktop-first financial workflows.

- **Grid:** All components and layout containers must align to the 8px baseline grid. For smaller adjustments, a 4px sub-unit is permitted.
- **App Shell:** A sticky topbar (56px height) and a collapsible sidebar (240px expanded / 64px collapsed) create a persistent navigation frame.
- **Density:** High-density spacing is prioritized for data tables and ledger views, utilizing narrow vertical padding (12px) to maximize the amount of information visible on a single screen.

## Elevation & Depth
Depth is conveyed through **Tonal Layers** and subtle shadows rather than excessive layering.

- **Surface Tiers:** Backgrounds are `#F4F5F7`. Cards and panels sit on top at `#FFFFFF` with a very light `shadow-card` (1px blur) to define edges.
- **Active Focus:** Interactive focus states use a "Brand Red Glow" (`0 0 0 3px rgba(192,25,10,0.20)`) to provide high visibility without changing the element's footprint.
- **Overlays:** Modals use a heavy, high-contrast overlay (`#0C0D10` at 72% opacity) with a 2px backdrop blur to simulate an "immutable" state for the underlying content during approvals or critical edits.
- **Dividers:** Horizontal and vertical lines (1px) are the primary tool for separating data, using `#E3E5E9` for standard separation and `#CDD0D6` for headers.

## Shapes
The shape language is strictly **Sharp**. This reinforces the "Financial Precision" narrative and ensures that UI elements feel structural and disciplined.

- **Exceptions:** While the base system is 0px (sharp), specific interactive components like buttons and inputs may use a minimal 4px radius, and cards/panels use a 6px radius to provide just enough distinction from the browser chrome.
- **Status Badges:** Use a 3px radius to appear as distinct, "stamped" indicators.
- **Accents:** 3px solid vertical lines are used as active indicators for sidebar items and stat card highlights.

## Components
- **Buttons & Inputs:** Use 4px corner radius. Primary buttons are `#C0190A` with white text. Inputs use high-contrast borders (`border-strong`) to ensure clarity during rapid data entry.
- **Data Tables:** The heart of the system. Use zebra striping with `#F9FAFB`. Headers must be sticky, utilizing `border-strong` at the bottom. Columns for currency must use monospaced fonts and right-alignment.
- **Status Badges:** Compact pills with a 3px radius. Color-coded by status: Success (Green), Warning (Amber), Danger (Red), and Neutral (Gray).
- **Cards:** 6px radius with a 1px solid border (`#E3E5E9`). Used to group KPI summaries or domain modules.
- **Navigation:** The dark sidebar uses `#1A1C23` for active states with a 3px red vertical accent on the left edge.
- **Skeletons:** Loading states must use a 1.6s shimmer animation; spinners are prohibited to maintain a clean, data-first feel.
- **Financial Indicators:** "Unbalanced" indicators utilize a 300ms shake animation to draw immediate attention to errors.