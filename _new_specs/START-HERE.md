# PublishAI — START HERE

Open Claude Code inside this folder and paste the prompt below.
That is all you do. Claude Code handles everything else.

---

## PASTE THIS INTO CLAUDE CODE:

```
You are the engineering team for PublishAI — a Laravel 13 AI-powered
publishing engine built for Isaac Jootar (jootarisaac@gmail.com).

## STEP 1 — READ ALL SPEC FILES FIRST

Read every file below completely before doing anything else:

1. CLAUDE.md
2. docs/stack.md
3. docs/architecture.md
4. docs/database.md
5. docs/ui-rules.md — read fully including toast and button loading sections
6. docs/colors.md
7. docs/api-integrations.md
8. docs/git-workflow.md
9. docs/phases.md
10. docs/claude-code-setup.md
11. docs/modules/01-research.md
12. docs/modules/02-voice-profile.md
13. docs/modules/03-outline.md
14. docs/modules/04-manuscript.md
15. docs/modules/05-illustration-prompts.md
16. docs/modules/06-kdp-pack.md
17. docs/modules/07-digital-product.md
18. docs/modules/08-launch-content.md
19. docs/modules/09-series-planner.md
20. docs/prompts/voice-dna.md
21. docs/prompts/research.md
22. docs/prompts/outline.md
23. docs/prompts/manuscript.md
24. docs/prompts/illustration-prompts.md
25. docs/prompts/kdp-listing.md
26. docs/prompts/digital-product.md
27. docs/prompts/launch-content.md

After reading all files, confirm by listing:
- Current build phase
- Full tech stack
- Git identity you will use
- The two pipeline modes
- Toast trigger method
- Approval rule

---

## STEP 2 — INSTALL RTK (token compression)

Run these commands now:

```bash
curl -sSL https://rtk-ai.app/install.sh | sh
rtk init -g
```

Confirm RTK is installed with: `rtk --version`
RTK now runs silently on every terminal command. No further setup needed.

---

## STEP 3 — INSTALL SUPER MEMORY (cross-session memory)

Run these commands:

```bash
npm install -g @supermemoryai/claude-supermemory
claude mcp add supermemory -- supermemory-claude
```

Then run: `/claude-supermemory:setup`
When prompted, Isaac will provide the API key from supermemory.ai

Create this config file at: ~/.supermemory-claude/settings.json

```json
{
    "maxProfileItems": 8,
    "signalExtraction": true,
    "signalKeywords": [
        "voice_profile", "domain", "pipeline", "phase",
        "schema", "module", "architecture", "decision",
        "bug", "fix", "completed", "publishai"
    ],
    "signalTurnsBefore": 3,
    "includeTools": ["Edit", "Write", "MultiEdit"]
}
```

Create this file at: .claude/.supermemory-claude/config.json

```json
{
    "repoContainerTag": "publishai-project",
    "signalExtraction": true
}
```

Confirm Super Memory is active before continuing.

---

## STEP 4 — ACTIVATE CAVEMAN (shorter responses)

Run: /caveman

This stays active for the session. Activate it at the start of
every future session. Keeps responses tight and context budget long.

---

## STEP 5 — SET GIT IDENTITY

Run these now before any commit:

```bash
git init
git config user.name "Isaac Jootar"
git config user.email "jootarisaac@gmail.com"
```

---

## STEP 6 — INSTALL LARAVEL AND ALL DEPENDENCIES

Run every command below in order. Do not skip any.
Do not continue to the next command until the current one succeeds.
If any command fails, stop and report the exact error.

```bash
# Confirm PHP version is 8.3+
php -v

# Confirm Composer is installed
composer --version

# Confirm Node.js is installed
node -v

# Install Laravel 13
composer create-project laravel/laravel . --prefer-dist

# Core Laravel packages
composer require livewire/livewire
composer require blade-ui-kit/blade-heroicons
composer require anthropic-php/laravel
composer require openai-php/laravel
composer require laravel/horizon
composer require laravel/pulse
composer require resend/resend-laravel
composer require unicodeveloper/laravel-paystack
composer require kingflamez/laravelrave

# Dev only packages
composer require laravel/telescope --dev
composer require laravel/boost --dev

# Install Breeze for auth
composer require laravel/breeze
php artisan breeze:install blade

# Install Laravel Boost MCP server
php artisan boost:install

# Install Horizon
php artisan horizon:install

# Install Pulse
php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"

# Install Telescope
php artisan telescope:install

# Frontend packages
npm install -D tailwindcss postcss autoprefixer
npm install daisyui
npm install alpinejs

# Generate app key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run initial migrations (Breeze + Telescope + Pulse + Horizon)
php artisan migrate

# Create storage directories for user files
mkdir -p storage/app/users
php artisan storage:link
```

After all commands complete successfully, confirm:
- Laravel version installed
- All composer packages installed
- All npm packages installed
- Database migrated
- Storage linked

---

## STEP 7 — CONFIGURE ENVIRONMENT

Update the .env file with these values:

```env
APP_NAME=PublishAI
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite

QUEUE_CONNECTION=database

ANTHROPIC_MODEL=claude-sonnet-4-6

MAIL_MAILER=resend
MAIL_FROM_ADDRESS=hello@publishai.app
MAIL_FROM_NAME=PublishAI

FILESYSTEM_DISK=local
```

Leave ANTHROPIC_API_KEY and OPENAI_API_KEY empty for now.
Isaac will add these through the Settings page in the app.

---

## STEP 8 — APPLY TAILWIND AND DAISYUI CONFIGURATION

Update tailwind.config.js exactly as follows:

```javascript
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                primary:       '#6C3CE1',
                'primary-dark':'#5A2EC9',
                'primary-light':'#F0EBFF',
                accent:        '#F59E0B',
            },
        },
    },
    plugins: [
        require('daisyui'),
    ],
    daisyui: {
        themes: [
            {
                publishai: {
                    'primary':           '#6C3CE1',
                    'primary-content':   '#ffffff',
                    'secondary':         '#F59E0B',
                    'secondary-content': '#ffffff',
                    'accent':            '#10B981',
                    'accent-content':    '#ffffff',
                    'neutral':           '#1A0D33',
                    'base-100':          '#ffffff',
                    'base-200':          '#F8F7FF',
                    'base-300':          '#E4E0F0',
                    'base-content':      '#0F0A1E',
                    'info':              '#6C3CE1',
                    'success':           '#10B981',
                    'warning':           '#F59E0B',
                    'error':             '#EF4444',
                },
            },
        ],
        darkTheme: false,
    },
}
```

Run: `npm run build` — confirm it compiles without errors.

---

## STEP 9 — BUILD THE SIDEBAR LAYOUT SHELL

Before Phase 01 officially starts, create the main app layout shell:
`resources/views/layouts/app.blade.php`

The sidebar must:
- Use the dark gradient from docs/colors.md:
  `background: linear-gradient(165deg, #1A0D33 0%, #0D0719 100%)`
- Show all navigation items from CLAUDE.md navigation names table
- Highlight active nav item with violet left border
- Include `<x-toast />` component before closing body tag
- Be fully responsive (collapsible on mobile)

Create the toast component:
`resources/views/components/toast.blade.php`
Use the exact implementation from docs/ui-rules.md toast section.

---

## STEP 10 — BEGIN PHASE 01

Now begin Phase 01 exactly as described in docs/phases.md.

When Phase 01 is complete:
1. Run `npm run build` — confirm no errors
2. Run `php artisan serve` — confirm app loads at localhost:8000
3. Commit:
   ```bash
   git add .
   git commit -m "Phase 01 complete — Project Setup: Laravel 13, Tailwind, DaisyUI, Breeze, all packages installed"
   git push
   ```
4. Output the full phase completion report from docs/git-workflow.md
5. Stop and wait for Isaac's approval before starting Phase 02

---

## NON-NEGOTIABLE RULES FOR EVERY SESSION

- One phase at a time. Complete and commit before starting the next.
- Stop after every phase. Wait for Isaac to say approved / go ahead / continue.
- Silence is not approval. A thumbs up is not approval.
- All commits use: git config user.name "Isaac Jootar" / jootarisaac@gmail.com
- Every system message in the UI is a top-right toast notification.
- Every async button shows a loading state immediately on click.
- Green badge = Claude handled it. Orange badge = Isaac's action needed.
- Never show raw errors or stack traces to the user.
- Read the relevant docs/modules/ file before building any module.
- Ask rather than assume when anything is unclear.
- Run: remember: [decision] after any important architectural decision.
```

---

## AFTER PHASE 01 — START EVERY FUTURE SESSION WITH THIS

```
Read CLAUDE.md. Check your memory for this project. Tell me:
1. What phase are we on?
2. What was completed last session?
3. What should we build next?
Then activate /caveman and let's build.
```
