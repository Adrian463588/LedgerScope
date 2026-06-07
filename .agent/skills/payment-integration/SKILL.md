---
name: payment-integration
description: "Integrate payments with SePay (VietQR), Polar, Stripe, Paddle (MoR subscriptions), Creem.io (licensing). Checkout, webhooks, subscriptions, QR codes, multi-provider orders."
argument-hint: "[provider] [task]"
version: 2.2
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# Payment Integration

Production-proven payment processing with SePay (Vietnamese banks), Polar (global SaaS), Stripe, Paddle (MoR), and Creem.io (licensing).

## When to Use

- Payment gateway integration (checkout, processing)
- Subscription management (trials, upgrades, billing)
- Webhook handling (notifications, idempotency)
- QR code payments (VietQR, NAPAS)
- Software licensing and device activation
- Multi-provider order management

## Platform Selection

| Platform | Best For |
|----------|----------|
| **SePay** | Vietnamese market, VND, bank transfers, VietQR |
| **Polar** | Global SaaS, subscriptions, GitHub/Discord benefits |
| **Stripe** | Enterprise payments, Connect platforms |
| **Paddle** | MoR subscriptions, global tax compliance |
| **Creem.io** | MoR + licensing, revenue splits, no-code checkout |

## General Flow

```
auth → products → checkout → webhooks → handle events
```

## Webhook Security (All Providers)

- Always verify webhook signatures before processing
- Use idempotency keys to prevent duplicate processing
- Return 200 immediately, process async
- Log all webhook events for debugging

```javascript
// SePay verification
node .agent/skills/payment-integration/scripts/sepay-webhook-verify.js

// Polar verification
node .agent/skills/payment-integration/scripts/polar-webhook-verify.js
```

## Provider Reference Map

### SePay (Vietnam)
- `references/sepay/overview.md` — Auth, supported banks
- `references/sepay/api.md` — Endpoints, transactions
- `references/sepay/webhooks.md` — Setup, verification
- `references/sepay/qr-codes.md` — VietQR generation
- `references/sepay/best-practices.md` — Production patterns

### Polar
- `references/polar/overview.md` — Auth, MoR concept
- `references/polar/checkouts.md` — Checkout flows
- `references/polar/subscriptions.md` — Lifecycle management
- `references/polar/webhooks.md` — Event handling
- `references/polar/benefits.md` — Automated delivery

### Stripe
- `references/stripe/stripe-best-practices.md` — Integration design
- `references/stripe/stripe-js.md` — Payment Element
- `references/stripe/stripe-cli.md` — Local testing
- External: https://docs.stripe.com/llms.txt

### Paddle
- `references/paddle/overview.md` — MoR, auth, entity IDs
- `references/paddle/subscriptions.md` — Trials, upgrades, pause
- `references/paddle/webhooks.md` — SHA256 verification
- External: https://developer.paddle.com/llms.txt

### Creem.io
- `references/creem/overview.md` — MoR, auth, global support
- `references/creem/licensing.md` — Device activation
- `references/creem/webhooks.md` — Signature verification
- External: https://docs.creem.io/llms.txt

## Anti-Patterns

- Processing webhooks without signature verification
- Exposing secret API keys in frontend code
- Not handling idempotency (duplicate charge risk)
- Storing raw card data (use provider tokenization)
- Skipping sandbox/test mode testing before production
