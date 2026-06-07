---
name: ui-ux-pro-max
description: "UI/UX design intelligence. 50 styles, palettes, font pairings, charts, across 9 stacks (React, Next.js, Vue, Svelte, SwiftUI, React Native, Flutter, Tailwind, shadcn/ui). Use when designing or reviewing any UI."
argument-hint: "[action] [element|project]"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# UI/UX Pro Max — Design Intelligence

Comprehensive design guide for web and mobile applications. Reference when designing, reviewing, or implementing any UI.

## When to Apply

- Designing new UI components or pages
- Choosing color palettes and typography
- Reviewing code for UX issues
- Building landing pages or dashboards
- Implementing accessibility requirements

## Rule Categories by Priority

| Priority | Category | Impact |
|----------|----------|--------|
| 1 | Accessibility | CRITICAL |
| 2 | Touch & Interaction | CRITICAL |
| 3 | Performance | HIGH |
| 4 | Layout & Responsive | HIGH |
| 5 | Typography & Color | MEDIUM |
| 6 | Animation | MEDIUM |
| 7 | Style Selection | MEDIUM |
| 8 | Charts & Data | LOW |

## Critical Rules (Always Apply)

### Accessibility
- `color-contrast` — minimum 4.5:1 for normal text, 3:1 for large text
- `focus-states` — visible focus rings on all interactive elements
- `alt-text` — descriptive alt for meaningful images
- `aria-labels` — aria-label for icon-only buttons
- `keyboard-nav` — tab order matches visual order
- `form-labels` — `<label>` with `for` attribute on every input

### Touch & Interaction
- `touch-target-size` — minimum 44×44px touch targets
- `loading-buttons` — disable button during async operations
- `error-feedback` — clear error messages near the problem element
- `cursor-pointer` — add `cursor-pointer` to all clickable elements

### Layout & Responsive
- `viewport-meta` — `width=device-width, initial-scale=1`
- `mobile-first` — design and build for mobile before desktop
- `content-jumping` — reserve space for async content (avoid CLS)
- `overflow-handling` — handle text overflow with truncation/wrapping

## Typography Guidelines

- Avoid Arial/Inter for display text — use characterful, distinctive fonts
- Pair display font + body font (contrast in weight/style)
- Line height: 1.5× for body, 1.2× for headings
- Max line length: 65–75 characters for readability
- Font scale: establish type scale with 3–5 size steps

## Color Guidelines

- Commit to a cohesive palette with dominant + accent colors
- Use CSS variables for all theme colors
- Test palette in both light and dark modes
- Avoid pure black (`#000`) — use near-black for softer contrast

## Animation Guidelines

- CSS transitions first; JS animation (anime.js) only for complex sequences
- Respect `prefers-reduced-motion` media query
- Orchestrated page-load animations > scattered micro-interactions
- Duration: 150–300ms for micro-interactions, 400–600ms for page transitions

## References

- `data/styles.json` — 50 design styles with descriptions
- `data/palettes.json` — 97 color palettes
- `data/fonts.json` — 57 font pairings
- `data/ux-rules.json` — 99 UX guidelines with priorities
- `data/charts.json` — 25 chart types with use cases
- `scripts/search-rules.js` — Search rules by domain/priority
