# Module 02 — Voice Profile / My Voice

## What this module does

Manages multiple domain-specific voice profiles.
Each profile is trained on writing samples from a specific topic area.
When creating a project, the user selects which domain profile to use.
Claude then writes in the user's voice specifically tuned for that domain.

This means: one person, many voices — each one sounds like them,
but naturally calibrated to the subject matter.

---

## Why domains matter

The same person writes differently when:
- Explaining a tech concept vs telling a children's story
- Writing a business strategy guide vs a personal development book
- Creating a social media swipe file vs a faith-based devotional

One general voice profile produces passable results.
Domain-specific profiles produce content that reads like you genuinely
know and live in that subject — because the training samples prove you do.

---

## UI page: resources/views/voice/index.blade.php

### Layout — domain profile list

```
Header: "My Voice"
Subtext: "Each domain profile learns how you write about a specific topic.
          The more you train it, the more it sounds like you."

[Button: "+ Add a new domain"] → opens create modal

[For each existing domain profile:]
  [Coloured card with emoji]
  Domain name (e.g. "Social Media & Algorithms")
  Domain description (one line)
  Word count: "[X] words trained"
  Last updated: [date]
  Status badge: "Ready" (green) / "Needs more content" (amber, under 500 words)
  [Edit] [Train more] [Set as default] [Delete]

[Default profile marked with a ★ star badge]
```

### Suggested domains shown at first visit (onboarding)

Show 8 suggested domain cards the user can click to create:

```
✍️  General Writing         — Your default voice for any topic
📱  Social Media & Algorithms — Platform guides, creator content
💼  Business & Entrepreneurship — Strategy, pricing, solopreneur content
🧒  Children's Content       — Stories and educational books
🙏  Faith & Personal Dev     — Devotionals, growth, mindset
💻  Tech & Digital Tools     — AI guides, tool breakdowns
🌿  Health & Wellness        — Habits, fitness, mental health
💰  Finance & Money          — Budgeting, investing, income

[+ Create a custom domain]
```

User clicks a suggested card → pre-fills the create form with that name,
emoji, colour, and a description. They can edit all fields.

---

## Create / Edit domain profile form

```
[Emoji picker — small grid of common emojis]
[Colour picker — 8 preset brand colours]
[Text input: "Domain name"] e.g. "Social Media & Algorithms"
[Text input: "What topics will this cover?"]
  e.g. "Instagram, LinkedIn, X, TikTok, content strategy, algorithm guides"
[Toggle: "Set as my default profile"]
[Button: "Create domain →"]
```

---

## Train a domain profile (upload samples)

```
Header: "Train [Domain Name]"
Subtext: "Upload anything you've written or said about [domain].
          The more you give, the more it sounds like you."

[Upload instructions box:]
  Works best with:
  - Your own social media posts or threads
  - Voice note transcripts on this topic
  - Old articles, essays, or blog posts
  - WhatsApp messages where you explained this topic
  - Course notes or scripts you've written

[File uploader — .txt, .md, .docx — multiple files allowed]

[Word count indicator:]
  Under 500:   "Add more — minimum for basic results"
  500-1000:    "Getting there — more is better"
  1000-2000:   "Good — solid foundation"
  2000-5000:   "Strong — your voice will come through clearly"
  5000+:       "Excellent — deep domain training"

[Button: "Extract my writing style →"]
```

### After processing

```
[Green badge: "Claude handled this"]

Header: "Here's what I learned about your [domain] writing voice"

[Style guide sections — same as voice-dna.md output:]
  Tone and energy
  Sentence style
  Vocabulary patterns
  How you open ideas
  How you close ideas
  Things you never say
  Signature phrases

[Button: "Save this profile"] → saves extracted_style to DB
[Button: "Add more content"] → back to uploader
```

---

## Domain selector on project creation

When user starts a new project (Book or Digital Product),
show a domain profile selector BEFORE the research step:

```
Header: "Which writing voice for this project?"

[For each domain profile — shown as selectable cards:]
  [Emoji] [Domain name]
  [Short description]
  [Word count badge]
  [★ Default] badge if applicable

[Button: "Use this voice →"]
```

Selected profile ID saved to `projects.voice_profile_id`.
All AI calls in this project inject this profile's `extracted_style`.

---

## Domain profile in AI injection

```php
// In ClaudeService::buildSystemPrompt()

$profile = $project->voiceProfile;

if ($profile && $profile->extracted_style) {
    $domainContext = $profile->domain_description
        ? "This content is in the domain of: {$profile->domain_description}."
        : '';

    return $base
        . "\n\n--- AUTHOR VOICE PROFILE: {$profile->name} ---\n"
        . $domainContext . "\n"
        . $profile->extracted_style
        . "\n--- END VOICE PROFILE ---\n\n"
        . "Write in this author's exact voice for this domain. "
        . "Sound like they personally wrote this. Not like an AI.";
}
```

---

## No profile / fallback behaviour

If no domain profile is selected or trained:
- Use neutral default tone
- Show amber banner: "No voice profile selected. Go to My Voice to make
  this sound like you. → Set up My Voice"
- Never block the user from continuing — generation still works

If selected profile has under 500 words trained:
- Still use it but show: "Your [domain] profile is still light.
  Add more samples for better results. → Train more"

---

## Processing logic

1. User uploads files for a domain
2. All file text concatenated → `raw_content`
3. `ProcessVoiceProfileJob` dispatched
4. Job calls ClaudeService with voice-dna prompt (docs/prompts/voice-dna.md)
5. Includes domain context: "This person writes about [domain_description]"
6. AI returns structured style guide
7. Display for review
8. On approval: save `extracted_style`, update `word_count`

---

## Updating a profile

User can always add more samples to any profile:
- Upload new files → re-extract → review → save
- New extraction replaces previous `extracted_style`
- `raw_content` appends (does not replace) — builds over time
- Word count updates to reflect total trained content
