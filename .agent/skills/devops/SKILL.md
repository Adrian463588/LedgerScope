---
name: devops
description: "CI/CD pipelines, Docker, Kubernetes, cloud infrastructure, monitoring, deployment automation. Use for GitHub Actions, containerization, IaC, secrets management, observability."
argument-hint: "[platform or task]"
version: 1.0
allowed-tools: Read, Write, Edit, Glob, Grep, Bash
---

# DevOps

CI/CD, containerization, cloud infrastructure, and observability.

## When to Use

- Setting up or modifying CI/CD pipelines
- Dockerizing applications
- Kubernetes deployments
- Infrastructure as Code (Terraform, Pulumi)
- Secrets management
- Monitoring and alerting setup
- Deployment automation

## CI/CD (GitHub Actions)

```yaml
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with: { node-version: '20', cache: 'npm' }
      - run: npm ci
      - run: npm run typecheck
      - run: npm test
      - run: npm run build
```

**Rules:**
- Gate on tests before deploy — never skip failing tests
- Use `npm ci` (not `npm install`) for reproducible builds
- Cache dependencies to reduce build time
- Separate lint/test/build into distinct jobs for parallelism

## Docker

```dockerfile
# Multi-stage build — keep production image lean
FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM node:20-alpine AS runner
WORKDIR /app
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/node_modules ./node_modules
EXPOSE 3000
CMD ["node", "dist/index.js"]
```

**Rules:**
- Multi-stage builds to minimize image size
- Non-root user in production containers
- `.dockerignore` to exclude `node_modules`, `.env`, `dist`
- Pin base image versions (not `latest`)

## Secrets Management

- Never commit secrets to git
- Use environment variables injected at runtime
- GitHub Actions: use `secrets.*` context
- Production: use secret manager (AWS Secrets Manager, Vault, Doppler)
- Rotate secrets regularly; audit access logs

## Kubernetes Essentials

```yaml
# Always set resource limits
resources:
  requests: { cpu: "100m", memory: "128Mi" }
  limits: { cpu: "500m", memory: "512Mi" }
```

- Use `readinessProbe` and `livenessProbe`
- Rolling deployments with `maxUnavailable: 0`
- Namespaces to isolate environments (dev/staging/prod)

## Monitoring Checklist

- [ ] Health check endpoint (`/health` returning 200)
- [ ] Structured JSON logging
- [ ] Error rate alerting (> 1% 5xx)
- [ ] P95/P99 latency tracking
- [ ] Disk/CPU/memory alerts before saturation

## References

- `references/github-actions.md` — Pipeline patterns and reusable workflows
- `references/docker.md` — Dockerfile best practices
- `references/kubernetes.md` — K8s deployment patterns
- `references/terraform.md` — IaC patterns
- `references/monitoring.md` — Observability setup
