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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3
- laravel/breeze (BREEZE) - v2
- laravel/framework (LARAVEL) - v13
- laravel/horizon (HORIZON) - v5
- laravel/prompts (PROMPTS) - v0
- laravel/pulse (PULSE) - v1
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/telescope (TELESCOPE) - v5
- phpunit/phpunit (PHPUNIT) - v12
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
