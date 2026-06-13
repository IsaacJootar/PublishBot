# PublishAI — Claude Code Master Instructions

> Read this file at the start of every session. Every decision traces back here.

## What this product is

PublishAI is a local AI-powered publishing engine for solopreneurs.
It produces two types of sellable products from a single dashboard:

1. **KDP Books** — children's books, parenting guides, educational books
   published on Amazon Kindle Direct Publishing (KDP)
2. **Digital Products** — prompt packs, Notion templates, PDF guides,
   swipe files, and niche toolkits sold on Gumroad or Selar

Everything is generated in the user's own writing voice, trained from
their uploaded transcripts and content samples.

**Tagline:** Your AI publishing engine. One topic in. Sellable product out.
**Operator:** Solo founder / solopreneur. Non-technical user.
**Builder:** Isaac Jootar. Claude Code is the engineering team.
**Runs:** Locally on the user's machine. No cloud server required at launch.

---

## Stack — do not suggest alternatives without asking

| Layer | Decision |
|---|---|
| Framework | Laravel 13 (PHP 8.3+) |
| Frontend templates | Blade |
| CSS | Tailwind CSS + DaisyUI |
| JS interactivity | Alpine.js |
| Complex UI / real-time | Livewire 3 |
| Database (local) | SQLite |
| Database (production) | PostgreSQL via Supabase free tier |
| ORM | Laravel Eloquent |
| Auth | Laravel Breeze (email + password) |
| Background jobs | Laravel Horizon + Redis |
| Email | Resend (free tier) |
| Payments | Paystack + Flutterwave (NO Stripe) |
| AI primary | Anthropic Claude API (claude-sonnet-4-6) |
| AI fallback | OpenAI API (gpt-4o) — silent fallback if Claude fails |
| File storage | Laravel local storage → Supabase Storage |
| Document export | Laravel calling Pandoc via exec() for DOCX + PDF |
| Performance monitoring | Laravel Pulse |
| App monitoring | Laravel Telescope (dev only) |
| AI dev assistant | Laravel Boost (MCP server for Claude Code) |
| Hosting | Render.com — Frankfurt region |
| Icons | blade-heroicons |

---

## Git identity — all commits must use

```bash
git config user.name "Isaac Jootar"
git config user.email "jootarisaac@gmail.com"
```

Run this once inside the publishai folder before the very first commit.

---

## Session start ritual — do this EVERY session

Before anything else, run this check:
1. Read this file (CLAUDE.md)
2. Check Super Memory for this project (what was built last session)
3. Read `docs/phases.md` and confirm the CURRENT PHASE
4. Activate `/caveman` for the session
5. Report back: phase status, last completed work, what to build next

---

## Before writing any code — read these files

- `docs/claude-code-setup.md` — RTK + Super Memory + Caveman (read FIRST on a new machine)
- `docs/git-workflow.md` — commit rules and approval protocol, ALWAYS read before any git operation
- `docs/architecture.md` — app structure, ALWAYS read before any code work
- `docs/database.md` — complete schema, ALWAYS read before any model/migration work
- `docs/ui-rules.md` — naming and UX rules, ALWAYS read before any Blade/Livewire work
- `docs/stack.md` — packages and versions, read when installing anything
- `docs/colors.md` — complete colour system, read before any CSS/Tailwind work
- `docs/api-integrations.md` — AI API setup, read before any AI feature work
- The relevant `docs/modules/` file for whatever module you are building
- The relevant `docs/prompts/` file when building any AI feature

---

## Navigation names — FIXED, do not change

| Internal name | UI label shown to user |
|---|---|
| Dashboard | Dashboard |
| Voice Profile Manager | My Voice | (multiple domain profiles) |
| Niche Research Engine | Research |
| Outline Generator | Outline |
| Manuscript Writer | Write |
| Illustration Prompt Generator | Illustrations |
| KDP Pack Generator | KDP Listing |
| Digital Product Builder | Digital Product |
| Launch Content Generator | Launch |
| Series Planner | My Series |
| Project Library | My Projects |
| Settings | Settings |

---

## Pipeline modes — two product types

### Mode A — Book pipeline
Research → Outline → Write → Illustrations → KDP Listing → Launch

### Mode B — Digital Product pipeline
Research → Product Structure → Write Content → Sales Page → Launch

Both modes share: Voice Profile, Research Engine, Launch Content Generator.

---

## Token management tools — always active during build sessions

See full setup in `docs/claude-code-setup.md`.

| Tool | Role | Command |
|---|---|---|
| RTK (Rust Token Killer) | Compresses terminal output 60-90% | Auto (hook) |
| Super Memory | Remembers decisions across sessions | Auto (MCP) |
| Caveman | Shortens responses 65-75% | `/caveman` at session start |

**Every build session starts with:** `/caveman`
**Every important decision gets saved with:** `remember: [decision]`

---

## Laravel Boost — MCP server setup

Laravel Boost gives Claude Code real-time access to your codebase.
Install it in the project before starting any coding session:

```bash
composer require laravel/boost --dev
php artisan boost:install
```

Then in Claude Code settings, enable the laravel-boost MCP server.
This lets Claude Code read your routes, models, schema, and logs live.

---

## Approval workflow — non-negotiable

11. **Commit after completing each phase** — when a phase is fully built
    and tested, run `git add .`, `git commit`, and `git push` before doing
    anything else. No exceptions.

12. **Stop and wait for approval before continuing** — after every completed
    phase, stop completely. Report what was built, confirm it works, and wait
    for Isaac's explicit approval before starting the next phase.

13. **Do not proceed without Isaac's approval** — silence is not approval.
    A thumbs up is not approval. Wait for Isaac to say "approved", "go ahead",
    or "continue" before writing a single line of the next phase.

14. **Keep commits clear and phase-based** — every commit message must name
    the phase and what was completed.
    Example: `"Phase 05 complete — Research module: 10 title angles, scoring, project creation"`
    No vague messages like "updates" or "fixes".

15. **Do not stop halfway through a phase** — do not pause in the middle of
    a phase because something is difficult. Work through it. A phase is only
    done when every feature in its spec is built, tested, committed, and pushed.
    Then stop and wait for approval.

16. **Report format after every phase** — when a phase is complete, report
    exactly this before stopping:
    ```
    PHASE [N] COMPLETE
    What was built: [list of files created/modified]
    How to test: [exact steps to verify it works]
    Committed: [commit hash]
    Waiting for Isaac's approval to start Phase [N+1]: [phase name]
    ```

---

## Golden rules — non-negotiable

1. **Plain language only** — every button, label, and message must be
   understood by a non-technical solopreneur with zero training.

2. **Domain voice profile is always injected** — every AI generation call
   MUST include the project's selected domain voice profile in the system
   prompt. Each project has one domain profile. Never generate without it
   once a profile exists. If none selected, use neutral default and show banner.

3. **Green = automated, Orange = your action needed** — this visual rule
   applies everywhere in the app. The user must always know what Claude
   handled and what needs their hands.

4. **Failures are handled gracefully** — every Claude API call wrapped in
   try-catch. If Claude fails, silently retry via OpenAI. If both fail,
   show plain English error. Never show raw errors or stack traces.

5. **Ask before assuming** — if a requirement is unclear, stop and ask.
   Do not silently pick an approach and build 200 lines on a wrong assumption.

6. **Surgical changes** — only touch what the task requires. Do not refactor
   or improve adjacent code unless explicitly asked.

7. **One phase at a time** — complete and test each phase before starting
   the next. See `docs/phases.md` for the full build sequence.

8. **Mobile responsive** — every screen works at 375px minimum width.
   Use Tailwind responsive prefixes (sm: md: lg:) on every layout element.

9. **Export is always available** — every completed manuscript or product
   must have a one-click export to DOCX and PDF via Pandoc.

10. **Projects are never deleted** — archive only. The user's work is never
    permanently lost from the UI.

---

## AI model setup

**Primary:** `claude-sonnet-4-6` via Anthropic API
**Fallback:** `gpt-4o` via OpenAI API

Fallback triggers automatically if:
- Anthropic API returns a 5xx error
- Request times out after 30 seconds
- API key is missing or invalid

Both keys stored in `.env` file. Never hardcoded.

---

## Current build phase

Update this line when starting a new phase:
**CURRENT PHASE: 01 — Project Setup**

See `docs/phases.md` for all phases.
