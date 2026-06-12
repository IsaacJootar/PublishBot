# PublishAI — Build Phases

Complete each phase fully and test before moving to the next.
Update CLAUDE.md CURRENT PHASE line when starting each phase.
Commit and wait for Isaac's approval after EVERY phase.

---

## Phase 01 — Project Setup
- `composer create-project laravel/laravel publishai`
- Install all packages from docs/stack.md
- Run `npm install` and configure Tailwind + DaisyUI
- Apply DaisyUI theme from docs/colors.md to tailwind.config.js
- Install Alpine.js and Livewire 3
- Install blade-heroicons
- Run `php artisan breeze:install blade`
- Run `php artisan migrate`
- Create storage directories: `storage/app/users/`
- Apply sidebar CSS and global styles from docs/colors.md
- Run `composer require laravel/boost --dev && php artisan boost:install`
- Test: `php artisan serve` — app loads at localhost:8000 with auth screens

## Phase 02 — Database Migrations
- Create all migrations from docs/database.md
- Run `php artisan migrate`
- Create all Eloquent models with relationships from docs/database.md
- Add encrypted cast to anthropic_api_key and openai_api_key on User model
- Test: `php artisan tinker` — create a test user, verify all relationships

## Phase 03 — Settings Page
- Build SettingsController and settings Blade views
- API key input fields (masked/password type) for Claude and OpenAI
- Save encrypted keys to users table
- "Test connection" button — calls Claude with "Say hello. Reply with one word."
- Author name and pen name fields
- Show API cost estimates table from docs/api-integrations.md
- Test: save keys, test connection button shows success/failure in plain English

## Phase 04 — AI Service Core
- Build `app/Services/AI/ClaudeService.php` — full implementation from docs/api-integrations.md
- Claude primary + OpenAI silent fallback
- `buildSystemPrompt()` with voice profile injection
- Build `app/Services/ExportService.php` — Pandoc DOCX + PDF
- Test: call `ClaudeService::generate()` directly in tinker with and without voice profile

## Phase 05 — My Voice (Domain Voice Profiles)
- Build VoiceProfileController and voice Blade views
- Domain profile list page — show all user's domain profiles as coloured cards
- Show 8 suggested domain cards at first visit (onboarding)
- Create domain form: emoji picker, colour picker, name, description, is_default toggle
- File uploader per domain — accepts .txt, .md, .docx (multiple files)
- Word count indicator with quality labels (500 / 1000 / 2000 / 5000+ thresholds)
- Dispatch ProcessVoiceProfileJob with domain context included in prompt
- Display extracted style guide for user review per domain
- Save to voice_profiles table on approval
- Domain selector shown on new project creation (before research step)
- Show amber banner on all generation pages when no profile selected
- `is_default` toggle — only one profile can be default at a time
- Test: create 3 domain profiles, upload samples to each, verify different
  style extractions, select different profiles on different projects

## Phase 06 — Dashboard
- Build DashboardController and dashboard Blade view
- Stat cards: total projects, books completed, products created, words generated
- Recent projects list with status badge and current step indicator
- "Start new project" button → modal to choose Book or Digital Product
- Quick-resume cards for in-progress projects (show current step)
- Test: dashboard shows correct data after test projects created

## Phase 07 — Research Module
- Build ResearchController, ResearchService, and research Blade views
- Build `ResearchResults` Livewire component for real-time results display
- Topic input, product type selector, book format selector
- Call ClaudeService with research prompt from docs/prompts/research.md
- Parse JSON response → save 10 rows to research_results table
- Display as cards: title, format, score bar, competition badge, reason
- Top 3 get "Top pick" violet badge
- Green "Claude handled this" badge on results section
- User selects title → creates project record → advances to step 2
- Test: research 3 topics, select titles, verify projects created

## Phase 08 — Outline Generator
- Build OutlineController, OutlineService, and outline Blade views
- Pipeline progress bar showing step 1 done, step 2 active
- Generate outline button → calls ClaudeService with outline prompt
- Display chapters as editable cards (inline editing via Alpine.js)
- Approve individual chapters and approve all button
- Save to outlines table, is_approved = true per chapter
- Advance project to step 3 on full approval
- Test: generate outlines for children's book and parenting guide formats

## Phase 09 — Manuscript Writer
- Build ManuscriptController, ManuscriptService, and manuscript Blade views
- Build `ChapterWriter` Livewire component
- Chapter list — all locked except current chapter
- Dispatch GenerateChapterJob per chapter (with voice profile injected)
- Livewire polls for job completion → shows chapter text on completion
- Chapter approve / rewrite / edit flow
- Word count tracker updating per approval
- Export buttons (DOCX + PDF) via ExportService after all chapters approved
- Advance project to step 4 on full manuscript approval
- Test: write a complete 5-chapter children's book end-to-end

## Phase 10 — Illustration Prompts
- Build IllustrationController, IllustrationService, and illustration Blade views
- Build `IllustrationChecklist` Livewire component
- Extract + lock character description and art style (one AI call)
- Generate one prompt per page
- Display: page text LEFT, prompt RIGHT, copy button
- Orange "Your action needed" badge prominently at top
- Checklist per page — tick when illustration is done
- Progress bar: "[X] of [Y] pages illustrated"
- Unlock next step when all ticked
- Test: generate prompts for 10-page book, verify character consistency

## Phase 11 — KDP Listing Generator
- Build KdpListingController, KdpListingService, and kdp-listing Blade views
- Generate full Amazon listing from project data
- Display editable sections: title, subtitle, description, 7 keywords, categories, bio
- Copy button on each section
- Orange upload instructions section with step-by-step KDP guide
- Payoneer payment tip info box
- Export manuscript download buttons
- Save to kdp_listings table
- Test: generate listing for completed book project

## Phase 12 — Digital Product Builder
- Build DigitalProductController, DigitalProductService, digital-product Blade views
- Product type selector, platform selector, buyer description inputs
- Generate product structure → review → generate full content
- Section-by-section display with approve toggles
- Sales page generation
- Price recommendation display
- Export to PDF
- Orange upload instructions for Gumroad/Selar
- Save to digital_products table
- Test: build a prompt pack and a PDF guide end-to-end

## Phase 13 — Launch Content Generator
- Build LaunchController, LaunchContentService, and launch Blade views
- Generate all 5 social posts + 3-email sequence + review request
- All in author voice (voice profile injected)
- Copy button on every piece of content
- Character count indicators on Twitter/X post
- Send timing labels on each email
- Save to launch_packs table
- Test: generate launch packs for a book and a digital product

## Phase 14 — Series Planner
- Build SeriesController and series Blade views
- Create series form: name, format, character bible builder, style guide
- Series detail view: character bible, books list, style guide
- "Plan next book" → pre-fills research with series context
- Link projects to series via series_id
- Test: create a 3-book series, plan second book with context pre-filled

## Phase 15 — My Projects Library
- Build ProjectController list view (projects index)
- List all projects: title, type badge, status badge, current step, date
- Filter tabs: All / Books / Digital Products / Completed / In Progress
- Resume button → goes to correct pipeline step
- Archive button (no delete)
- Duplicate project option
- Test: all filter and action options work correctly

## Phase 16 — Background Jobs + Queue
- Ensure GenerateChapterJob, ProcessVoiceProfileJob, ExportDocumentJob all work
- Local: `QUEUE_CONNECTION=database`, test with `php artisan queue:work`
- Add job progress feedback in Livewire components
- Failed job handling — show plain English error to user
- Test: generate a chapter as a queued job, verify Livewire updates on completion

## Phase 17 — Polish and QA
- Review ALL pages against docs/ui-rules.md
- Verify green/orange badges are correct on every output
- Verify all error messages use plain English
- Verify voice profile is injected in every AI call
- Verify OpenAI fallback works when Claude key is invalid
- Verify Pandoc export works for DOCX and PDF
- Test full end-to-end: research → outline → write → illustrate → KDP → launch
- Mobile responsive check at 375px on all pages
- Fix any broken flows

## Phase 18 — README and Setup Guide
- Write README.md in plain English
- Installation steps using Laravel/Composer (no technical jargon)
- How to get Claude API key (link to console.anthropic.com)
- How to install Pandoc (link to pandoc.org)
- How to run: `php artisan serve`
- Quick start: "Your first book in 60 minutes"
- RTK + Super Memory + Caveman setup summary
