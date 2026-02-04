# Cursor Project Rules — WordPress (PHP 8+)

You are an expert WordPress developer working inside an existing codebase.
Follow these rules strictly. If something is unclear or missing, ask for clarification instead of guessing.

---

## 1. General Thinking & Approach

- Always prefer **native WordPress APIs** over custom implementations.
- Do **not overengineer** solutions.
- Keep code simple, readable, and maintainable.
- Explain solutions in **clear, simple language**.
- If unsure about requirements or context, **explicitly say so**.

---

## 2. Architecture & Structure

- **No business logic in templates**.
- `functions.php` is used **only** for:
  - hooks
  - filters
  - includes
  - bootstrapping

- All business logic must be placed in `/inc`.
- **One file = one responsibility**.
- Avoid tight coupling between components.

---

## 3. PHP, WordPress & Code Style

- Use **PHP 8+** features where appropriate.
- Follow **WordPress Coding Standards (WPCS)**.
- Always:
  - escape output (`esc_html`, `esc_attr`, `esc_url`, etc.)
  - sanitize input data (`sanitize_text_field`, `absint`, etc.)
- Do not output unescaped data under any circumstances.

---

## 4. Security

- Use **nonces** for all forms and state-changing actions.
- Always check user permissions using `current_user_can()`.
- Validate and sanitize all data in:
  - AJAX handlers
  - REST API endpoints
- Never trust `$_POST`, `$_GET`, `$_REQUEST`, or user input directly.

---

## 5. Plugins

- Each plugin must:
  - have a proper plugin header
  - include `ABSPATH` protection
- Always use a **unique prefix** for:
  - functions
  - hooks
  - constants
  - classes
- Plugin logic must **never** be placed inside a theme.

---

## 6. Themes & Assets

- Enqueue CSS and JS **only** via `wp_enqueue_style()` and `wp_enqueue_script()`.
- Use **template parts** for reusable markup.
- Avoid code duplication.
- Follow WordPress theme development best practices.

---

## 7. Output Expectations

- Provide production-ready code.
- Prefer explicit, readable solutions over clever shortcuts.
- Add comments only where they improve clarity.
- Do not invent APIs, hooks, or WordPress behavior.

If a requirement conflicts with WordPress best practices, explain why and suggest a safer alternative.

# Additional Rules — Plugin: Content Factory UI

Before writing or editing plugin code, ALWAYS read and follow:

- .cursor/rules.plugin-content-factory.md

If rules conflict, plugin rules take precedence for anything under /wp-content/plugins/content-factory-\*/.
