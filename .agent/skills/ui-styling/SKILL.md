---
name: ui-styling
description: "Style UIs with shadcn/ui components (Radix UI + Tailwind CSS). Use for accessible components, themes, dark mode, responsive layouts, design systems, color customization."
argument-hint: "[component or layout]"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# UI Styling

Beautiful, accessible UIs using shadcn/ui components, Tailwind CSS, and design system tokens.

## When to Use

- Building UI with React-based frameworks (Next.js, Vite, Remix, Astro)
- Implementing accessible components (dialogs, forms, tables, navigation)
- Styling with utility-first CSS
- Responsive, mobile-first layouts
- Dark mode and theme customization
- Design systems with consistent tokens

## Core Stack

| Layer | Tool | Purpose |
|-------|------|---------|
| Components | shadcn/ui (Radix UI) | Accessible, composable primitives |
| Styling | Tailwind CSS | Utility-first, zero-runtime |
| Tokens | CSS variables | Consistent colors, spacing, typography |

## Quick Start

```bash
# Initialize shadcn/ui
npx shadcn@latest init

# Add components
npx shadcn@latest add button
npx shadcn@latest add dialog
npx shadcn@latest add form
```

## Component Usage Pattern

```tsx
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog"

// Compose primitives — don't fight the component API
<Dialog open={open} onOpenChange={setOpen}>
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Title</DialogTitle>
    </DialogHeader>
    <Button onClick={handleAction}>Action</Button>
  </DialogContent>
</Dialog>
```

## Tailwind Rules

- Mobile-first breakpoints: `sm:` `md:` `lg:` `xl:`
- Use CSS variables for theme colors: `bg-background text-foreground`
- Dark mode via `.dark` class on `<html>`
- Extract repeated utility groups into components (not `@apply`)
- Never use arbitrary values (`w-[137px]`) for standard spacing

## Accessibility Must-Haves

- All interactive elements keyboard accessible
- `aria-label` on icon-only buttons
- Color contrast ≥ 4.5:1 for body text
- Focus rings visible (don't `outline-none` without replacement)
- Form inputs have associated `<label>`

## Theme Customization

Edit `globals.css` CSS variables — don't override Tailwind config directly:
```css
:root {
  --background: 0 0% 100%;
  --foreground: 222.2 84% 4.9%;
  --primary: 222.2 47.4% 11.2%;
}
```

## References

- shadcn/ui docs: https://ui.shadcn.com/llms.txt
- Tailwind CSS: https://tailwindcss.com/docs
- `references/component-library.md` — Component patterns
- `references/theming.md` — Theme and dark mode setup
- `references/canvas-design.md` — Canvas-based visual compositions
