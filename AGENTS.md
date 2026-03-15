# Project Agent Rules

## First-Turn Startup Rule
- On the first user message of every new Codex session in this repository, read the relevant files in `.project-docs/` before proposing or making code changes.
- Start with the most recent memory file and load additional project-doc files only when they are relevant to the request.
- After reading `.project-docs/`, summarize the relevant context briefly in a commentary update before editing code.

## Fallback
- If `.project-docs/` is missing or empty, say that briefly and continue with normal repository inspection.

## Memory Hygiene
- After significant setup work, behavior changes, or architectural decisions, append or add a concise memory in `.project-docs/` so the next session has current context.
## Frontend Styling Rules
- Do not add page or component CSS directly inside Blade templates with `<style>` blocks when working on this project.
- Do not rely on inline `style=""` attributes for normal UI styling work unless there is a narrow one-off exception that cannot reasonably live in a stylesheet.
- Put styling in modular CSS files under `basic_crud/resources/css/` and wire them through `basic_crud/resources/css/app.css` so the frontend stays organized and reusable.
- When touching existing Blade views that contain embedded CSS, prefer refactoring that styling into modular CSS files instead of expanding the inline styles further.
