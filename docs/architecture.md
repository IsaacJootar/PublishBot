# PublishAI — Architecture

## Overview

PublishAI is a single-user Laravel 13 application running locally.
No multi-tenancy. One user, one database, one machine.
Built with the same stack as Brandara — Laravel, Blade, Tailwind, DaisyUI,
Alpine.js, Livewire 3 — so patterns are identical and familiar.

---

## The two pipelines

Every project belongs to one pipeline mode selected at creation:

### Pipeline A — KDP Book
```
Step 1: Research       → Niche + 10 title angles
Step 2: Outline        → Chapter-by-chapter structure
Step 3: Write          → Full manuscript (chapter by chapter)
Step 4: Illustrations  → One prompt per page (MANUAL — orange label)
Step 5: KDP Listing    → Amazon title, description, keywords, categories
Step 6: Launch         → Social posts + email sequence + review request
```

### Pipeline B — Digital Product
```
Step 1: Research       → What's selling + product angle
Step 2: Structure      → Product sections and contents
Step 3: Write Content  → Full product written out
Step 4: Sales Page     → Gumroad/Selar listing copy
Step 5: Launch         → Social posts + email sequence
```

---

## Shared systems (used by both pipelines)

**Domain Voice Profiles** — user creates one profile per topic domain
(e.g. "Social Media & Algorithms", "Children's Content", "Business Strategy").
Each profile is trained on domain-specific writing samples. When a project
is created, one domain profile is selected. That profile's extracted_style
is injected into every AI call for that project. User can have unlimited
domain profiles. One is marked as default.

**ClaudeService** — single service wrapping Claude API + OpenAI fallback.
All pipeline services call this. Never call AI APIs directly.

**ExportService** — converts manuscript or product content to DOCX/PDF
via Pandoc called through Laravel's exec().

**Project Library** — every project stored in projects table. Resumable
at any step. Never deleted, only archived.

---

## Request lifecycle

```
1. User visits /research
2. Laravel Router → auth middleware checks login
3. ResearchController@index loads active project if exists
4. Blade view rendered with Tailwind + DaisyUI components
5. Livewire components mount (ResearchResults, PipelineProgress)
6. User submits topic → Livewire calls ResearchController
7. ResearchController → ResearchService → ClaudeService
8. ClaudeService calls Claude API (with voice profile injected)
9. If Claude fails → silent fallback to OpenAI
10. Results saved to research_results table
11. Livewire re-renders results on page without full reload
```

---

## AI service pattern — use everywhere

```php
// app/Services/AI/ClaudeService.php

class ClaudeService
{
    public function generate(string $systemPrompt, string $userPrompt): string
    {
        try {
            return $this->callClaude($systemPrompt, $userPrompt);
        } catch (\Exception $e) {
            try {
                return $this->callOpenAI($systemPrompt, $userPrompt);
            } catch (\Exception $e) {
                throw new \Exception(
                    'Generation failed. Check your API keys in Settings.'
                );
            }
        }
    }

    private function buildSystemPrompt(string $base, ?string $voiceProfile): string
    {
        if (!$voiceProfile) {
            return $base;
        }

        return $base . "\n\n--- AUTHOR VOICE PROFILE ---\n"
            . $voiceProfile
            . "\n--- END VOICE PROFILE ---\n\n"
            . "Write in this author's exact voice. Match their sentence length, "
            . "vocabulary, tone, and style. Sound like them, not like an AI.";
    }
}
```

---

## Voice profile injection rule

Every AI generation call MUST follow this pattern:

```php
// In any pipeline service (e.g. ManuscriptService.php)

$voiceProfile = auth()->user()->voiceProfile?->extracted_style;
$systemPrompt = $this->claude->buildSystemPrompt($basePrompt, $voiceProfile);
$result = $this->claude->generate($systemPrompt, $userPrompt);
```

If no voice profile exists: use base prompt only.
Show banner on all generation pages: "Set up My Voice for better results."

---

## Pipeline state management

Each project has a `current_step` integer (1–6 for books, 1–5 for products).

```php
// Project model
public function advanceStep(): void
{
    if ($this->current_step < $this->total_steps) {
        $this->increment('current_step');
    }
}

public function isStepComplete(int $step): bool
{
    return $this->current_step > $step;
}
```

Blade views read `$project->current_step` to show:
- ✓ green checkmark on completed steps
- → violet highlight on current step
- 🔒 locked/grey on future steps

---

## Manual vs automated steps — visual rule

**Green badge** (`badge-auto` class):
```html
<span class="badge badge-success gap-1">
    <x-heroicon-s-check-circle class="w-3 h-3" />
    Claude handled this
</span>
```

**Orange badge** (`badge-manual` class):
```html
<span class="badge badge-warning gap-1">
    <x-heroicon-s-hand-raised class="w-3 h-3" />
    Your action needed
</span>
```

Illustration steps: ALWAYS orange.
KDP upload step: ALWAYS orange.
Everything else Claude generates: ALWAYS green.

---

## Background jobs

Long AI generation tasks run as queued jobs so the UI doesn't freeze:

```php
// Dispatch from controller
GenerateChapterJob::dispatch($project, $chapterNumber, $outline)
    ->onQueue('generation');

// Livewire component polls for completion
public function getJobStatusProperty()
{
    return cache()->get("chapter_job_{$this->project->id}_{$this->chapter}");
}
```

Local dev: `QUEUE_CONNECTION=database`, run `php artisan queue:work`
Production: `QUEUE_CONNECTION=redis`, Horizon manages workers

---

## File storage structure

```
storage/app/users/{user_id}/
├── voice_profiles/          ← uploaded transcript files
│   └── transcript_1.txt
├── manuscripts/             ← chapter text files
│   └── {project_id}/
│       ├── chapter_1.txt
│       └── chapter_2.txt
└── exports/                 ← final DOCX and PDF files
    └── {project_id}/
        ├── my-book.docx
        └── my-book.pdf
```

All paths generated via Laravel's `Storage::disk('local')->path(...)`.
Never hardcode file paths.

---

## Database

Single SQLite file for local development: `database/database.sqlite`
Production: PostgreSQL via Supabase.

No multi-tenancy needed — single user app.
All tables use standard Laravel conventions.
See `docs/database.md` for complete schema.
