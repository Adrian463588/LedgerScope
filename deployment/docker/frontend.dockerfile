# syntax=docker/dockerfile:1.7

# ─── Stage 1: Build stage ─────────────────────────────────────────────────────
FROM node:22-alpine AS build

WORKDIR /app

# Cypress is installed by CI for E2E, never bundled into the production image.
ENV CYPRESS_INSTALL_BINARY=0

# Copy package.json and package-lock.json first for efficient caching
COPY frontend/package.json frontend/package-lock.json ./

# Install npm dependencies
RUN --mount=type=cache,target=/root/.npm npm ci --no-audit --no-fund

# Copy the rest of the frontend files
COPY frontend/ ./

# Build the frontend Vue 3 SPA
RUN npm run build

# ─── Stage 2: Production Stage (Nginx) ─────────────────────────────────────────
FROM nginx:1.27-alpine AS production

# Copy custom nginx configuration
COPY deployment/docker/frontend-nginx.conf /etc/nginx/conf.d/default.conf

# Copy build output from Stage 1 to Nginx HTML folder
COPY --from=build /app/dist /usr/share/nginx/html

# Security: Ensure custom permissions for non-root execution
RUN touch /var/run/nginx.pid && \
    chown -R nginx:nginx /var/run/nginx.pid /usr/share/nginx/html /var/cache/nginx /var/log/nginx /etc/nginx/conf.d

# Run container as non-root user 'nginx' (DevSecOps Best Practice)
USER nginx

# Nginx port (ports < 1024 require root privileges, so we use 8080)
EXPOSE 8080

CMD ["nginx", "-g", "daemon off;"]
