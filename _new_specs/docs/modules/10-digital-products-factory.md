# Module — Digital Products Factory

## What this module does

A self-contained production pipeline that generates three types of
premium digital products from a single topic input.

Runs alongside the existing ebook pipeline. Completely independent.
User picks product type → fills a short brief → AI generates everything →
user reviews → exports and publishes.

**Three product types:**
1. Prompt Library — curated AI prompt packs for a specific niche
2. SOP Pack — standard operating procedures for a specific business type
3. Email Sequence Vault — complete email sequence library for a specific niche

Claude generates 100% of the content.
User reviews, tweaks, and publishes.
Total user time per product: under 1 hour.

---

## Where it lives in the app

New sidebar nav item: **"Digital Products"**
Sits below the existing ebook pipeline nav items.
Completely separate from the ebook pipeline — different routes,
different models, different views.

---

## The 5-stage pipeline (same for all three product types)

```
Stage 1: Brief         → User fills product brief (topic, niche, buyer, type)
Stage 2: Research      → AI researches niche, buyer pain points, what sells
Stage 3: Structure     → AI generates product structure (sections/chapters)
Stage 4: Content       → AI writes full product content
Stage 5: Publish Pack  → AI generates sales page + launch content
```

All stages run as background jobs via Laravel Horizon.
Each stage result displayed for user review before advancing.
User can regenerate any stage without losing other stages.

---

## UI: Digital Products dashboard

```
Route: /digital-products
View: resources/views/digital-products/index.blade.php

Header: "Digital Products Factory"
Subtext: "Pick a product type. Fill a brief. I build the rest."

[Three product type cards:]

┌─────────────────────────────┐
│ 📦 Prompt Library           │
│ Curated AI prompts for a    │
│ specific niche              │
│ Price range: $17 - $47      │
│ Build time: ~45 mins        │
│ [Start building →]          │
└─────────────────────────────┘

┌─────────────────────────────┐
│ 📋 SOP Pack                 │
│ Standard operating          │
│ procedures for a business   │
│ Price range: $97 - $297     │
│ Build time: ~60 mins        │
│ [Start building →]          │
└─────────────────────────────┘

┌─────────────────────────────┐
│ 📧 Email Sequence Vault     │
│ Complete email libraries    │
│ for a specific niche        │
│ Price range: $47 - $197     │
│ Build time: ~60 mins        │
│ [Start building →]          │
└─────────────────────────────┘

[Your products — recent list with status badges]
```

---

## Stage 1: Brief form

```
Route: /digital-products/create
View: resources/views/digital-products/create.blade.php

[Product type shown at top — locked from dashboard selection]

Fields:
- "What is the niche or topic?" (text input)
  e.g. "Freelance graphic designers"

- "Who is the buyer?" (text input)
  e.g. "Self-employed designers who want to use AI in their work"

- "What is their biggest problem?" (text input)
  e.g. "They spend too much time on client emails and proposals"

--- PROMPT LIBRARY ONLY ---
- "How many prompts?" (select: 30 / 50 / 75)
- "Prompt categories" (text — optional)
  e.g. "Client communication, proposals, social media, project briefs"

--- SOP PACK ONLY ---
- "What type of business?" (text input)
  e.g. "Freelance design studio"
- "How many SOPs?" (select: 10 / 20 / 30)
- "Key business areas to cover" (text — optional)
  e.g. "Client onboarding, project delivery, invoicing, revision requests"

--- EMAIL SEQUENCE VAULT ONLY ---
- "What type of sequences?" (text — optional)
  e.g. "Welcome, proposal follow-up, project completion, referral request"
- "How many sequences?" (select: 5 / 10 / 15)
- "Emails per sequence?" (select: 3 / 5 / 7)

[Button: "Start building →"] → dispatches Stage 2 job
```

---

## Stage 2: Research (background job)

```
Job: App\Jobs\DigitalProducts\ResearchJob
View: resources/views/digital-products/research.blade.php

Livewire polls for job completion.
Shows spinner with message: "Researching your niche..."

AI researches:
- Top buyer pain points in this niche
- Language and vocabulary buyers use
- What similar products exist and their gaps
- Price anchors in this market
- Best platforms to sell on

Output displayed as:
- Buyer profile summary
- Top 5 pain points (ranked)
- Competitive gap (what's missing in the market)
- Recommended price point with justification
- Recommended platform (Gumroad / Selar / Payhip)

[Green badge: "Claude handled this"]
[Button: "Looks good — build the structure →"]
[Button: "Regenerate research"]
```

---

## Stage 3: Structure (background job)

```
Job: App\Jobs\DigitalProducts\StructureJob
View: resources/views/digital-products/structure.blade.php

### PROMPT LIBRARY structure output:
- Product title (editable)
- Tagline (editable)
- [X] prompt categories, each with:
  - Category name
  - Number of prompts in category
  - What each prompt addresses

### SOP PACK structure output:
- Product title (editable)
- [X] SOPs listed, each with:
  - SOP title
  - What process it covers
  - Estimated steps

### EMAIL SEQUENCE VAULT structure output:
- Product title (editable)
- [X] sequences listed, each with:
  - Sequence name
  - Trigger (when to send)
  - Number of emails
  - Goal of the sequence

[Green badge: "Claude handled this"]
[Edit any section inline]
[Button: "This is right — write the full content →"]
[Button: "Regenerate structure"]
```

---

## Stage 4: Content generation (background job — longest stage)

```
Job: App\Jobs\DigitalProducts\ContentJob
View: resources/views/digital-products/content.blade.php

Full-page loading overlay shown — this takes 2-5 minutes:
"Writing your full product... this is the longest step.
 Don't close this tab."

Generated section by section. Each section displayed as an
accordion panel when complete. User can expand, read, and edit.

### PROMPT LIBRARY content per prompt:
PROMPT TITLE: [Short title]
USE WHEN: [One sentence]
THE PROMPT: [Full ready-to-use prompt with [BRACKETS] for variables]
TIP: [One sentence on getting best results]

### SOP PACK content per SOP:
SOP TITLE: [Title]
PURPOSE: [One sentence]
WHEN TO USE: [Trigger/situation]
STEPS:
  1. [Step with detail]
  2. [Step with detail]
  ...
NOTES: [Edge cases or important warnings]
TOOLS NEEDED: [Any tools or templates referenced]

### EMAIL SEQUENCE VAULT content per email:
SEQUENCE: [Sequence name]
EMAIL [N] OF [X]
SEND TIMING: [When to send]
SUBJECT LINE: [Full subject line]
PREVIEW TEXT: [Preview text]
BODY: [Full email body]
CTA: [Call to action]

[Green badge: "Claude handled this"]
[Approve all] or [approve section by section]
[Edit any section inline]
[Regenerate single section] button per section
[Button: "Content approved — build publish pack →"]
```

---

## Stage 5: Publish Pack (background job)

```
Job: App\Jobs\DigitalProducts\PublishPackJob
View: resources/views/digital-products/publish.blade.php

Generates everything needed to list and launch the product.

### Sales page copy (for Gumroad / Selar / Payhip):
- Headline
- Subheadline
- Opening hook (the problem)
- What's inside (bullet list — specific)
- Who this is for (3 statements)
- Who this is NOT for (1-2 statements)
- Price justification
- Call to action

### Social posts (5 platforms):
- LinkedIn / Facebook (150-300 words)
- X / Twitter (under 280 chars)
- Instagram (100-150 words + hashtags)
- Pinterest (60-100 words, SEO)
- WhatsApp broadcast (50-80 words, personal)

### Launch emails (3-email sequence):
- Email 1: Announcement (launch day)
- Email 2: Social proof (day 3)
- Email 3: Last chance (day 7)

### Upload instructions (orange badge):
Step-by-step for Gumroad, Selar, or Payhip
based on recommended platform from Stage 2.

[Copy button on every piece of content]
[Download full product as PDF]
[Download full product as DOCX]
[Green badge on all generated content]
[Orange badge on upload instructions]
```

---

## Database migrations

```php
Schema::create('digital_products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('product_type', [
        'prompt_library',
        'sop_pack',
        'email_sequence_vault'
    ]);
    $table->string('topic');
    $table->string('niche');
    $table->text('buyer_description');
    $table->text('buyer_problem');
    $table->json('brief_options')->nullable();      // type-specific options
    $table->integer('current_stage')->default(1);   // 1-5
    $table->enum('status', [
        'draft',
        'researching',
        'structuring',
        'writing',
        'publishing',
        'complete'
    ])->default('draft');
    $table->json('research_output')->nullable();
    $table->json('structure_output')->nullable();
    $table->longText('content_output')->nullable();
    $table->json('publish_pack')->nullable();
    $table->string('recommended_platform')->nullable();
    $table->decimal('recommended_price', 8, 2)->nullable();
    $table->string('product_title')->nullable();
    $table->boolean('is_archived')->default(false);
    $table->timestamps();
});
```

---

## Routes

```php
// routes/web.php — Digital Products Factory

Route::prefix('digital-products')->middleware('auth')->group(function () {
    Route::get('/', [DigitalProductController::class, 'index'])
        ->name('digital-products.index');

    Route::get('/create/{type}', [DigitalProductController::class, 'create'])
        ->name('digital-products.create');

    Route::post('/', [DigitalProductController::class, 'store'])
        ->name('digital-products.store');

    Route::get('/{product}', [DigitalProductController::class, 'show'])
        ->name('digital-products.show');

    Route::get('/{product}/stage/{stage}', [DigitalProductController::class, 'stage'])
        ->name('digital-products.stage');

    Route::post('/{product}/advance', [DigitalProductController::class, 'advance'])
        ->name('digital-products.advance');

    Route::post('/{product}/regenerate/{stage}', [DigitalProductController::class, 'regenerate'])
        ->name('digital-products.regenerate');

    Route::get('/{product}/export/{format}', [DigitalProductController::class, 'export'])
        ->name('digital-products.export');

    Route::patch('/{product}/archive', [DigitalProductController::class, 'archive'])
        ->name('digital-products.archive');
});
```

---

## Jobs structure

```
app/Jobs/DigitalProducts/
├── ResearchJob.php          ← Stage 2
├── StructureJob.php         ← Stage 3
├── ContentJob.php           ← Stage 4 (longest — split into chunks)
└── PublishPackJob.php       ← Stage 5
```

Each job:
- Reads the digital_product record
- Calls ClaudeService with the relevant prompt
- Saves output to the correct JSON column
- Updates current_stage and status
- Dispatches a toast notification via broadcasting when complete

---

## AI prompts — where they live

```
docs/prompts/digital-products/
├── research.md              ← Stage 2 prompt
├── structure-prompt-lib.md  ← Stage 3 prompt (prompt library)
├── structure-sop-pack.md    ← Stage 3 prompt (SOP pack)
├── structure-email-vault.md ← Stage 3 prompt (email vault)
├── content-prompt-lib.md    ← Stage 4 prompt (prompt library)
├── content-sop-pack.md      ← Stage 4 prompt (SOP pack)
├── content-email-vault.md   ← Stage 4 prompt (email vault)
└── publish-pack.md          ← Stage 5 prompt (all types)
```

---

## Services structure

```
app/Services/DigitalProducts/
├── DigitalProductService.php    ← orchestrates the pipeline
├── ResearchService.php
├── StructureService.php
├── ContentService.php           ← largest — handles all 3 product types
└── PublishPackService.php
```

---

## Content generation strategy — Stage 4

Content is too long to generate in one API call.
Split by section — one API call per category/SOP/sequence.
Jobs dispatch child jobs per section.
Livewire shows progress: "Writing section 3 of 8..."

```php
// ContentJob.php
public function handle(): void
{
    $sections = json_decode($this->product->structure_output, true);

    foreach ($sections as $index => $section) {
        GenerateSectionJob::dispatch($this->product, $section, $index)
            ->onQueue('generation')
            ->delay(now()->addSeconds($index * 2)); // stagger calls
    }
}
```

---

## Voice profile integration

If the user has a domain voice profile that matches this product type,
inject it into every content generation call.

Check for matching domain profile on product creation:
```php
$voiceProfile = $user->voiceProfiles()
    ->where('domain', 'like', "%{$niche}%")
    ->orWhere('is_default', true)
    ->first();
```

If found — inject. If not — use neutral professional tone.
Show banner: "Using your [domain] voice profile for this product."

---

## Export

Same ExportService as the ebook pipeline.
Combines all content sections in order.
Adds cover page: product title + author name + platform tagline.
Adds table of contents.
Exports as DOCX and PDF via Pandoc.

```
storage/app/users/{user_id}/digital-products/{product_id}/
├── product.docx
└── product.pdf
```

---

## Phase additions to docs/phases.md

Add these phases after the existing ebook pipeline phases:

### Phase DP-01 — Digital Products database + routes
- Migration: create_digital_products_table
- DigitalProduct model with relationships
- All routes registered
- DigitalProductController stub methods
- Test: routes resolve, model creates correctly

### Phase DP-02 — Dashboard + Brief form
- Digital Products index view (3 product type cards)
- Create view with dynamic brief form per product type
- Alpine.js shows/hides type-specific fields
- Form submits, creates record, dispatches ResearchJob
- Test: all 3 brief forms submit correctly

### Phase DP-03 — Research stage
- ResearchJob + ResearchService
- Research prompt per product type
- Livewire polling component shows completion
- Research output displayed with approve/regenerate
- Toast on completion
- Test: research runs for all 3 product types

### Phase DP-04 — Structure stage
- StructureJob + StructureService
- Structure prompts for all 3 product types
- Structure output displayed as editable cards
- Inline editing via Alpine.js
- Toast on completion
- Test: structure generates correctly for all 3 types

### Phase DP-05 — Content generation stage
- ContentJob → GenerateSectionJob per section
- ContentService for all 3 product types
- Progress indicator: "Writing section X of Y"
- Full-page overlay during generation
- Section-by-section accordion display
- Inline editing per section
- Per-section regenerate button
- Toast on full completion
- Test: full content generated for all 3 types

### Phase DP-06 — Publish Pack stage
- PublishPackJob + PublishPackService
- Sales page copy generation
- 5 social posts generation
- 3-email launch sequence
- Upload instructions per platform
- Copy buttons on all content
- Toast on completion
- Test: publish pack generates for all 3 types

### Phase DP-07 — Export
- DOCX and PDF export for digital products
- Cover page + table of contents
- Download buttons in publish pack view
- Test: both formats export cleanly for all 3 types
