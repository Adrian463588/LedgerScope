---
name: frontend-development
description: "Build React/TypeScript frontends with modern patterns. Use for components, Suspense, lazy loading, useSuspenseQuery, MUI v7 styling, TanStack Router, performance optimization."
argument-hint: "[component or feature]"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Frontend Development Guidelines

## When to Use

- Creating new React components or pages
- Fetching data with TanStack Query
- Setting up routing with TanStack Router
- Styling components with MUI v7
- Performance optimization
- TypeScript best practices

## New Component Checklist

- [ ] `React.FC<Props>` pattern with TypeScript
- [ ] Lazy load if heavy: `React.lazy(() => import())`
- [ ] Wrap in `<SuspenseLoader>` for loading states
- [ ] Use `useSuspenseQuery` for data fetching (no loading spinners)
- [ ] Import aliases: `@/`, `~types`, `~components`, `~features`
- [ ] Styles: inline if < 100 lines, separate file if > 100 lines
- [ ] `useCallback` for event handlers passed to children
- [ ] Default export at bottom

## New Feature Checklist

- [ ] Create `features/{feature-name}/` directory
- [ ] Subdirectories: `api/`, `components/`, `hooks/`, `helpers/`, `types/`
- [ ] API service file: `api/{feature}Api.ts`
- [ ] TypeScript types in `types/`
- [ ] Route in `routes/{feature-name}/index.tsx`
- [ ] Lazy load feature components with Suspense boundaries
- [ ] Export public API from feature `index.ts`

## Data Fetching Pattern

```tsx
// Preferred: useSuspenseQuery (no loading state needed in component)
const { data } = useSuspenseQuery({
  queryKey: ['resource', id],
  queryFn: () => fetchResource(id),
});

// Wrap page/component in Suspense boundary
<Suspense fallback={<SuspenseLoader />}>
  <MyComponent />
</Suspense>
```

## File Size Rule

Keep files under 200 lines. Split by:
- Component logic vs presentation
- API layer vs UI layer
- Shared hooks vs component-local state

## Performance

- Lazy load heavy components with `React.lazy`
- Memoize expensive computations with `useMemo`
- Stabilize callbacks with `useCallback`
- Avoid prop drilling — use context or feature state

## References

- `references/component-patterns.md` — Component architecture
- `references/data-fetching.md` — TanStack Query patterns
- `references/routing.md` — TanStack Router setup
- `references/styling.md` — MUI v7 + theming
- `references/performance.md` — Optimization techniques
- `references/typescript.md` — TypeScript best practices
