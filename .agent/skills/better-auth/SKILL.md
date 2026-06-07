---
name: better-auth
description: "Add authentication with Better Auth (TypeScript). Use for email/password, OAuth providers, 2FA/MFA, passkeys/WebAuthn, sessions, RBAC, rate limiting."
argument-hint: "[auth-method or feature]"
version: 2.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Better Auth

Comprehensive, framework-agnostic authentication for TypeScript with email/password, social OAuth, and a powerful plugin ecosystem.

## When to Use

- Auth in TypeScript/JavaScript applications
- Email/password or social OAuth setup
- 2FA, passkeys, magic links, advanced auth
- Multi-tenant apps with organization support
- Any framework: Next.js, Nuxt, SvelteKit, Remix, Astro, Hono, Express

## Quick Start

```bash
npm install better-auth
```

```env
BETTER_AUTH_SECRET=<32-char-min-secret>
BETTER_AUTH_URL=http://localhost:3000
```

```typescript
// auth.ts
import { betterAuth } from "better-auth";

export const auth = betterAuth({
  database: { /* see references/database-integration.md */ },
  emailAndPassword: { enabled: true, autoSignIn: true },
  socialProviders: {
    github: {
      clientId: process.env.GITHUB_CLIENT_ID!,
      clientSecret: process.env.GITHUB_CLIENT_SECRET!,
    }
  }
});
```

```bash
npx @better-auth/cli generate  # Generate schema/migrations
npx @better-auth/cli migrate   # Apply (Kysely only)
```

## Framework Mounting

```typescript
// Next.js App Router: app/api/auth/[...all]/route.ts
import { auth } from "@/lib/auth";
import { toNextJsHandler } from "better-auth/next-js";
export const { POST, GET } = toNextJsHandler(auth);
```

## Client Setup

```typescript
import { createAuthClient } from "better-auth/client";
export const authClient = createAuthClient({
  baseURL: process.env.NEXT_PUBLIC_BETTER_AUTH_URL
});
```

## Feature Selection

| Feature | Plugin Required | Reference |
|---------|----------------|-----------|
| Email/Password | No | `references/email-password-auth.md` |
| OAuth (GitHub, Google, etc.) | No | `references/oauth-providers.md` |
| Email Verification | No | `references/email-password-auth.md` |
| 2FA/TOTP | Yes (`twoFactor`) | `references/advanced-features.md` |
| Passkeys/WebAuthn | Yes (`passkey`) | `references/advanced-features.md` |
| Magic Link | Yes (`magicLink`) | `references/advanced-features.md` |
| Organizations/Multi-tenant | Yes (`organization`) | `references/advanced-features.md` |
| Rate Limiting | No (built-in) | `references/advanced-features.md` |

## Implementation Checklist

- [ ] Install package, set env vars
- [ ] Create auth server instance with database config
- [ ] Run schema migration
- [ ] Mount API handler in framework
- [ ] Create client instance
- [ ] Implement sign-up/sign-in UI
- [ ] Add session management
- [ ] Set up protected routes/middleware
- [ ] Configure email sending (verification/reset)
- [ ] Enable rate limiting for production
- [ ] Test complete auth flow end-to-end

## Resources

- Docs: https://www.better-auth.com/docs
- GitHub: https://github.com/better-auth/better-auth

## References

- `references/email-password-auth.md` — Email/password, verification, reset
- `references/oauth-providers.md` — Social login setup
- `references/database-integration.md` — DB adapters, schema, migrations
- `references/advanced-features.md` — 2FA, passkeys, organizations, sessions
