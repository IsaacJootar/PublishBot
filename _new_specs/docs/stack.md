# PublishAI — Complete Tech Stack

## Core framework

**Laravel 13** — PHP 8.3+. Full stack framework. All routing, database,
jobs, storage, email, and API integrations in one codebase.

```bash
composer create-project laravel/laravel publishai
cd publishai
php -v  # Must be 8.3+
```

---

## Frontend

| Package | Purpose | Install |
|---|---|---|
| Tailwind CSS | Utility-first CSS | `npm install -D tailwindcss` |
| DaisyUI | Pre-built Tailwind components | `npm install daisyui` |
| Alpine.js | Small JS interactions | CDN or `npm install alpinejs` |
| Livewire 3 | Complex interactive UI | `composer require livewire/livewire` |
| blade-heroicons | Icon set | `composer require blade-ui-kit/blade-heroicons` |

DaisyUI covers: buttons, modals, cards, tables, dropdowns, alerts, badges,
form inputs, navigation, and more. Always use DaisyUI components before
writing any custom CSS.

---

## Authentication

```bash
composer require laravel/breeze
php artisan breeze:install blade
```

Email + password only at launch.

**Onboarding flow:**
1. Register (name, email, password)
2. Add API keys in Settings
3. Upload voice profile in My Voice
4. Start first project

---

## Background jobs — Laravel Horizon + Redis

```bash
composer require laravel/horizon
php artisan horizon:install
```

**Requires Redis.** Use Redis free tier on Railway or Render in production.
Local dev: use `QUEUE_CONNECTION=database` — no Redis needed.

**PublishAI jobs:**
- `GenerateChapterJob` — generates one manuscript chapter async
- `GenerateResearchJob` — runs niche research in background
- `ExportDocumentJob` — calls Pandoc for DOCX/PDF export
- `ProcessVoiceProfileJob` — extracts style from uploaded transcripts

---

## AI — Anthropic Claude API (primary)

```bash
composer require anthropic-php/laravel
```

**Model:** `claude-sonnet-4-6`
**Max tokens:** 4096 per generation
**Temperature:** 0.7

```php
use Anthropic\Laravel\Facades\Anthropic;

$response = Anthropic::messages()->create([
    'model' => config('services.anthropic.model'),
    'max_tokens' => 4096,
    'system' => $systemPrompt,  // always includes voice profile
    'messages' => [
        ['role' => 'user', 'content' => $userPrompt]
    ],
]);

return $response->content[0]->text;
```

```env
ANTHROPIC_API_KEY=sk-ant-...
ANTHROPIC_MODEL=claude-sonnet-4-6
```

---

## AI — OpenAI API (silent fallback)

```bash
composer require openai-php/laravel
```

**Model:** `gpt-4o`
Only called if Claude API fails. User never sees the switch happen.

```php
use OpenAI\Laravel\Facades\OpenAI;

$response = OpenAI::chat()->create([
    'model' => 'gpt-4o',
    'max_tokens' => 4096,
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $userPrompt],
    ],
]);

return $response->choices[0]->message->content;
```

```env
OPENAI_API_KEY=sk-...
```

---

## AI service wrapper — always use this, never call APIs directly

```php
// app/Services/AI/ClaudeService.php
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
```

---

## Document export — Pandoc via exec()

Pandoc must be installed on the machine.
Install: https://pandoc.org/installing.html

Laravel calls Pandoc as a shell command:

```php
// app/Services/ExportService.php
public function exportToDocx(string $markdownContent, string $outputPath): string
{
    $tempInput = tempnam(sys_get_temp_dir(), 'publishai_') . '.md';
    file_put_contents($tempInput, $markdownContent);

    exec("pandoc '{$tempInput}' -o '{$outputPath}' --standalone", $output, $code);

    unlink($tempInput);

    if ($code !== 0) {
        throw new \Exception('Export failed. Make sure Pandoc is installed.');
    }

    return $outputPath;
}
```

---

## Email — Resend

```bash
composer require resend/resend-laravel
```

Free tier: 3,000 emails/month.

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_...
MAIL_FROM_ADDRESS=hello@publishai.app
MAIL_FROM_NAME=PublishAI
```

---

## Payments

Not included in v1. PublishAI is a single-user local tool.
No billing, no subscriptions, no checkout.
Payments are a v2 consideration if PublishAI becomes a SaaS product.

---

## File storage

**Local (development):**
```env
FILESYSTEM_DISK=local
```
Files stored at: `storage/app/users/{user_id}/`

**Production:** Switch to Supabase Storage — one line config change.

---

## Performance monitoring — Laravel Pulse

```bash
composer require laravel/pulse
php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"
php artisan migrate
```

Dashboard at `/pulse`. Shows slow queries, failed jobs, queue health.

---

## Development monitoring — Laravel Telescope

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Dev only. Dashboard at `/telescope`. Never deploy to production.

---

## Laravel Boost — MCP server for Claude Code

```bash
composer require laravel/boost --dev
php artisan boost:install
```

Gives Claude Code real-time access to routes, models, schema, and logs.
Enable in Claude Code settings at the start of every session.

---

## Hosting — Render.com

- Free tier for Laravel web service
- **Region: Frankfurt** (closest to West Africa)
- Database: Supabase PostgreSQL (better free tier than Render's)
- Redis: Railway free tier for Horizon

---

## Folder structure

```
publishai/
├── CLAUDE.md                              ← Master instructions (root)
├── docs/                                  ← All spec files for Claude Code
│   ├── architecture.md
│   ├── database.md
│   ├── stack.md
│   ├── ui-rules.md
│   ├── colors.md
│   ├── phases.md
│   ├── api-integrations.md
│   ├── claude-code-setup.md
│   ├── git-workflow.md
│   ├── modules/
│   │   ├── 01-research.md
│   │   ├── 02-voice-profile.md
│   │   ├── 03-outline.md
│   │   ├── 04-manuscript.md
│   │   ├── 05-illustration-prompts.md
│   │   ├── 06-kdp-pack.md
│   │   ├── 07-digital-product.md
│   │   ├── 08-launch-content.md
│   │   └── 09-series-planner.md
│   └── prompts/
│       ├── voice-dna.md
│       ├── research.md
│       ├── outline.md
│       ├── manuscript.md
│       ├── illustration-prompts.md
│       ├── kdp-listing.md
│       ├── digital-product.md
│       └── launch-content.md
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   ├── DashboardController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── ResearchController.php
│   │   │   ├── OutlineController.php
│   │   │   ├── ManuscriptController.php
│   │   │   ├── IllustrationController.php
│   │   │   ├── KdpListingController.php
│   │   │   ├── DigitalProductController.php
│   │   │   ├── LaunchController.php
│   │   │   ├── SeriesController.php
│   │   │   ├── VoiceProfileController.php
│   │   │   └── SettingsController.php
│   │   └── Livewire/
│   │       ├── PipelineProgress.php
│   │       ├── ChapterWriter.php
│   │       ├── ResearchResults.php
│   │       └── IllustrationChecklist.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Project.php
│   │   ├── VoiceProfile.php
│   │   ├── ResearchResult.php
│   │   ├── Outline.php
│   │   ├── Manuscript.php
│   │   ├── IllustrationPrompt.php
│   │   ├── KdpListing.php
│   │   ├── DigitalProduct.php
│   │   ├── LaunchPack.php
│   │   └── Series.php
│   │
│   ├── Services/
│   │   ├── AI/
│   │   │   ├── ClaudeService.php          ← Primary AI + fallback logic
│   │   │   ├── VoiceProfileService.php    ← Style extraction
│   │   │   ├── ResearchService.php
│   │   │   ├── OutlineService.php
│   │   │   ├── ManuscriptService.php
│   │   │   ├── IllustrationService.php
│   │   │   ├── KdpListingService.php
│   │   │   ├── DigitalProductService.php
│   │   │   └── LaunchContentService.php
│   │   └── ExportService.php              ← Pandoc DOCX/PDF
│   │
│   └── Jobs/
│       ├── GenerateChapterJob.php
│       ├── GenerateResearchJob.php
│       ├── ProcessVoiceProfileJob.php
│       └── ExportDocumentJob.php
│
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php                  ← Main dashboard shell
│   │   └── auth.blade.php
│   ├── dashboard/
│   ├── voice/
│   ├── research/
│   ├── outline/
│   ├── manuscript/
│   ├── illustrations/
│   ├── kdp-listing/
│   ├── digital-product/
│   ├── launch/
│   ├── series/
│   ├── projects/
│   ├── voice/
│   │   ├── index.blade.php            ← All domain profiles list
│   │   ├── create.blade.php           ← Create new domain form
│   │   ├── edit.blade.php             ← Edit domain settings
│   │   └── train.blade.php            ← Upload samples + review extraction
│   └── settings/
│
├── database/
│   └── migrations/
│
├── routes/
│   ├── web.php
│   └── api.php                            ← Webhook endpoints
│
└── storage/app/users/{id}/               ← Per-user file storage
    ├── voice_profiles/
    ├── manuscripts/
    └── exports/
```
