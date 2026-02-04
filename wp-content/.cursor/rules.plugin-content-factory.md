# Cursor Project Rules — Content Factory WP Plugin (UI + n8n API)

You are building a WordPress plugin that is ONLY a UI/control panel.
All content-factory logic, state, and storage are outside WordPress (n8n + external DB).
The plugin communicates ONLY via HTTP API to n8n. Never connect WP directly to external DB.

## 0) Core principle

- WordPress = UI + editor only
- n8n = logic + external DB + generation + publishing integrations
- Plugin depends on API contract (actions + JSON), not on DB schema

## 1) Architecture

- One class = one responsibility
- No “god classes” (no single AjaxHandler doing everything)
- Prefer WP REST API endpoints over admin-ajax
- Keep templates thin (views only), no business logic in views

## 2) Settings (must be editable from WP Admin)

- n8n Base URL must be configurable in plugin settings
- API key/secret must be configurable in settings
- Endpoint mapping must be configurable:
  - either multiple endpoints OR a single endpoint + action
- No secrets in code, no hardcoded URLs

## 3) API Contract (must be explicit)

For each UI action define:

- action name (string)
- request payload JSON schema
- response JSON schema
- expected statuses
- error format
  Never “guess” fields. If missing, ask to define them.

## 4) Security

- All state-changing actions require:
  - capability checks (admin/editor)
  - WP nonce validation (for UI-triggered actions)
- API calls to n8n must include:
  - Authorization header and/or HMAC signature
  - timeouts + safe retries (idempotent actions only)
- Never log secrets; redact tokens in logs

## 5) UX rules (Admin UI)

- Pages: Settings, Context (Niche/ICP), Senses, Topics, Articles, Telegram, Logs
- Each button triggers exactly ONE action call to n8n
- Show status and last error clearly
- Provide “Retry” where safe

## 6) WordPress publishing bridge

- Create WP posts only when user clicks “Create WP draft/pending”
- After creating WP post, immediately call n8n:
  - link_wp_post(article_id, wp_post_id, wp_url)
- Do not treat WP post as the source of truth

## 7) Logging (minimal but mandatory)

- Log per request:
  - action, timestamp, entity_id (if any), response status
- Store last error per action for UI display
- Never store full generated content in logs

## 8) Coding style

- PHP 8+
- WordPress Coding Standards
- Strict sanitization of input and escaping of output
- Use prefixes/namespaces to avoid collisions
