# PublishBot — Application Specification v1.1

> Any Topic. Any Niche. Every Friday.
> A fully automated digital publishing pipeline.

---

## What PublishBot does

User enters any topic → app runs a 5-stage AI pipeline → produces:
- Ebook draft
- Workbook draft
- Platform listing copy (Amazon KDP, Etsy, Gumroad)
- Pinterest pin copy
- All packaged as downloadable output files

---

## Core design principles

| Principle | Description |
|---|---|
| Zero hardcoding | No topic is baked in. Everything derived from user input. |
| Single entry point | Type topic, click Run. Rest is automatic. |
| Organised outputs | Each run gets its own folder named after topic. |
| Non-destructive | Re-running same topic creates a new versioned folder. |
| Human in the loop | Produces drafts. User reviews before publishing. |

---

## Who uses this

- Independent content creators building digital product businesses
- Authors publishing across multiple niches
- Solopreneurs who want to publish consistently without a team

---

## What it does NOT do

- Does not design Canva files — produces raw text for design tools
- Does not publish to Amazon/Etsy/Gumroad — produces copy to paste
- Does not manage email marketing beyond generating email copy

---

## Pipeline stages

| Stage | Name | Output file | Duration |
|---|---|---|---|
| 1 | Topic validation (LIVE web search) | 01-validation.txt | 60–120s |
| 2 | Outline generation | 02-outline.txt | 30–60s |
| 3a | Ebook draft | 03-ebook-draft.txt | 3–6 min |
| 3b | Workbook extraction | 03-workbook-draft.txt | 2–4 min |
| 4 | Listings (KDP + Etsy + Gumroad) | 04-listings.txt | 60–90s |
| 5 | Pinterest pin copy | 05-pins.txt | 30–60s |

**Stage 1 pauses** — user sees validation report + Continue button before Stage 2 begins.
If a stage fails, user can retry only that stage.

---

## Stage 1 — Topic validation with live web search

Uses Anthropic native web search tool (`web_search_20250305`). No third-party service.

API call:
```
POST https://api.anthropic.com/v1/messages
model: claude-sonnet-4-6
tools: [{ "type": "web_search_20250305", "name": "web_search" }]
```

Output contains:
- Amazon demand level + evidence (top 3 results, review counts, prices)
- Etsy demand level + evidence (listings, bestseller presence, prices)
- Competition assessment
- 3 alternative topic angles
- Final Go / No-Go recommendation

Fallback: if web search errors → knowledge-based assessment labeled `FALLBACK — NO LIVE DATA`

---

## Stage 2 — Outline generation

Returns:
- Compelling title + subtitle
- 10 chapters with title + 3 key takeaways each
- 10 printable worksheet ideas
- 8 workbook exercise ideas

---

## Stage 3a — Ebook draft

One API call per chapter. 450–550 words per chapter.
Progress shows "Writing chapter X of Y".
All chapters assembled in order into single file.

---

## Stage 3b — Workbook extraction

One API call per chapter. Per page:
1. Short intro (2-3 sentences)
2. One reflection question
3. One fill-in activity
4. Weekly challenge
5. 7-day progress tracker

---

## Stage 4 — Listings generation

Three separate API calls:
- **Amazon KDP**: 150-200 word description, pain point opening, CTA close
- **Etsy**: Title + 5-paragraph description + exactly 13 tags
- **Gumroad**: 100-word conversational description

Validates Etsy tag count = 13.

---

## Stage 5 — Pinterest pin copy

15 captions total, under 100 chars each:
- 3 quote pins
- 3 tip list pins ("5 ways to...")
- 3 question pins
- 3 challenge pins
- 3 product preview pins

---

## Database schema

### users (existing Laravel Breeze table)
Standard + no extra fields needed beyond auth.

### user_settings
| Field | Type | Default |
|---|---|---|
| id | uuid | |
| user_id | uuid | FK |
| api_key_encrypted | string | |
| model | string | claude-sonnet-4-6 |
| web_search_tool_version | string | web_search_20250305 |
| max_tokens | integer | 2000 |
| chapter_count | integer | 8 |
| words_per_chapter | integer | 500 |
| pin_count | integer | 15 |
| default_tone | string | |
| author_name | string | |
| quick_topics | json | Parenting,Finance,Fitness... |

### pipeline_runs
| Field | Type | Description |
|---|---|---|
| id | uuid | PK |
| user_id | uuid | FK |
| topic | string | Raw user input |
| slug | string | URL-safe topic |
| status | enum | pending/running/paused/completed/failed |
| current_stage | integer | 1–5 |
| validation_result | string | go/no_go/null |
| validation_report | text | Full Stage 1 output |
| user_confirmed_continue | boolean | True after user clicks Continue |
| output_path | string | Relative path to output folder |
| started_at | timestamp | |
| completed_at | timestamp | |

### pipeline_stages
| Field | Type | Description |
|---|---|---|
| id | uuid | PK |
| run_id | uuid | FK |
| stage_number | string | 1, 2, 3a, 3b, 4, 5 |
| stage_name | string | Human-readable |
| status | enum | pending/running/completed/failed |
| output_filename | string | |
| error_message | string | null unless failed |
| progress_note | string | e.g. "Writing chapter 4 of 8" |
| started_at | timestamp | |
| completed_at | timestamp | |

### sales
| Field | Type | Description |
|---|---|---|
| id | uuid | PK |
| user_id | uuid | FK |
| run_id | uuid | optional FK |
| topic | string | |
| product_type | enum | ebook/workbook/printable/bundle |
| platform | enum | amazon/etsy/gumroad |
| amount | decimal | |
| currency | string | NGN, USD, GBP |
| sale_date | date | |
| notes | string | |

---

## Pages

| Page | Route | Description |
|---|---|---|
| Login | /login | Public |
| Register | /register | Public |
| Dashboard | /dashboard | Topic input, metrics, recent runs |
| Run detail | /runs/{id} | Live stage progress + outputs |
| History | /runs | Paginated all runs |
| File viewer | /runs/{id}/files/{filename} | In-browser text viewer |
| Sales | /sales | Revenue tracking |
| Settings | /settings | API key, pipeline defaults |
| Account | /account | Name, email, password |

---

## Sidebar navigation

1. Dashboard
2. History
3. Sales
4. Settings
5. Account
6. Logout

Sidebar also shows: **Recent topics** — 5 most recent run topics as quick links.

---

## Run detail page behaviour

- Polls server every 3 seconds while pipeline is running
- Stage cards: Pending (grey) → Running (blue, animated) → Paused (amber + Continue button) → Done (green + file link) → Failed (red + Retry button)
- After Stage 1: full validation report panel appears below card
- After all stages: Download All button → zip of all 5 output files
- Re-run button available at any time

---

## File storage

```
storage/outputs/{user-id}/{topic-slug}-{run-number}/
  01-validation.txt
  02-outline.txt
  03-ebook-draft.txt
  03-workbook-draft.txt
  04-listings.txt
  05-pins.txt
```

Files served through app (not direct URL). Authenticated access only.

---

## AI service rules

- All pipeline calls: `https://api.anthropic.com/v1/messages`, model `claude-sonnet-4-6`
- User's encrypted API key decrypted at runtime, never logged
- Stage 1 only: web search tool enabled
- Stages 2–5: standard completion calls only
- Error handling:
  - Timeout → retry up to 3x with exponential backoff
  - Rate limit → wait retry-after period
  - Invalid response → log + mark failed + show retry button
  - Empty response → retry once, then fail
  - Invalid API key → fail immediately with plain English message

---

## Sales dashboard

Manual entry only — no platform API connections.
Shows:
- Total revenue this month
- Total revenue all time
- Products sold this month
- Revenue by topic
- Revenue by platform
- Month-by-month last 12 months

---

## Non-functional requirements

- Page load: under 2 seconds
- Pipeline start: redirect within 1 second
- Status polling: every 3 seconds, stops on completion
- API keys: AES-256 encrypted at rest, never logged
- Files: owner-only access verified on every request
- Input: topic string sanitised before prompt injection

---

## Build phases

| Phase | What to build |
|---|---|
| 01 | Setup (done — Laravel 13, UI shell) |
| 02 | Database migrations (user_settings, pipeline_runs, pipeline_stages, sales) |
| 03 | Settings page (API key, pipeline defaults, author name, quick topics) |
| 04 | Dashboard (topic input, metrics, quick topic tags, recent runs) |
| 05 | Pipeline engine (jobs, stage dispatch, file storage) |
| 06 | Run detail page (live polling, stage cards, validation panel, Continue button) |
| 07 | Stage 1 — Topic validation with web search |
| 08 | Stage 2 — Outline generation |
| 09 | Stage 3a — Ebook draft (chapter loop) |
| 10 | Stage 3b — Workbook extraction |
| 11 | Stage 4 — Listings generation |
| 12 | Stage 5 — Pinterest pin copy |
| 13 | File viewer + Download All zip |
| 14 | History page |
| 15 | Sales dashboard |
| 16 | QA + polish |
