# PublishBot — Claude Code Master Instructions

> Read this file at the start of every session.

## What this product is

PublishBot is a fully automated digital publishing pipeline web application.
User types any topic → AI runs 5 stages → produces ebook draft, workbook,
platform listings (Amazon KDP, Etsy, Gumroad), and Pinterest pin copy.

**Tagline:** Any Topic. Any Niche. Every Friday.
**Builder:** Isaac Jootar. Claude Code is the engineering team.
**Runs:** Locally on the user's machine.

---

## Stack — do not change without asking

| Layer | Decision |
|---|---|
| Framework | Laravel 13 (PHP 8.3+) |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Complex UI | Livewire 3 |
| Database (local) | SQLite |
| Auth | Laravel Breeze |
| Background jobs | Laravel Horizon + Redis (local: database queue) |
| AI | Anthropic Claude API (claude-sonnet-4-6) |
| AI web search | Anthropic native web_search_20250305 tool (Stage 1 only) |
| File storage | Local storage (storage/outputs/) |
| Monitoring | Laravel Pulse + Telescope (dev) |
| AI dev assistant | Laravel Boost |

---

## Full spec

See `docs/spec.md` — read before building any feature.

---

## Git identity

```bash
git config user.name "Isaac Jootar"
git config user.email "jootarisaac@gmail.com"
```

---

## Golden rules

1. Read `docs/spec.md` before building any feature.
2. Every AI call uses the user's encrypted API key from user_settings.
3. Stage 1 is the ONLY stage that uses the web search tool.
4. Files always served through the app — never direct URL access.
5. Topic input sanitised before injection into any prompt.
6. Pipeline runs as background jobs — never synchronous.
7. Failures handled gracefully — plain English to user, retry available.
8. One phase at a time. Commit + push after each phase.
9. Stop after every phase. Wait for Isaac's approval.
10. Silence is not approval.
11. Responses: short and dense. No pleasantries. Just code and direct answers.

---

## Current build phase

**CURRENT PHASE: 02 — Database Migrations**
