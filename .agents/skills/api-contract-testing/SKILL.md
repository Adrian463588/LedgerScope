---
name: api-contract-testing
description: Enforces LedgerScope API response contracts, Form Request validation, API Resources, authorization, and Pest feature tests. Use when creating or reviewing API endpoints, controllers, requests, resources, policies, or feature tests.
---

# API Contract Testing

Use this skill for every backend API endpoint.

## Endpoint Shape

- Controllers are thin and call actions or services only.
- Controllers authorize with policy/gate checks before model actions.
- Form Requests own validation and authorization input checks.
- API Resources shape returned models; do not return raw Eloquent models.
- Use the shared `ApiResponse` helper/trait for all JSON responses.

## Response Contract

- Success:
  ```json
  {"success": true, "message": "Resource loaded successfully.", "data": {}, "meta": {}}
  ```
- Validation error:
  ```json
  {"success": false, "message": "Validation failed.", "errors": {}}
  ```
- Authorization/domain errors:
  ```json
  {"success": false, "message": "You do not have permission to perform this action."}
  ```

Keep messages specific but never expose stack traces or raw production errors.

## Tests To Write First

For each endpoint, write Pest feature tests for:

- Authenticated success.
- Validation failure.
- Unauthorized or forbidden access.
- Domain failure when business rules reject the action.
- Company isolation when `company_id` is involved.

Use RTK: write the failing test, run the narrow filter, then implement.
