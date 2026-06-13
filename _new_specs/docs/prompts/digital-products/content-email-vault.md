# Prompt — Digital Products Factory: Email Sequence Vault Content

## Used in: App\Services\DigitalProducts\ContentService
## Purpose: Write each email sequence (one API call per sequence)

---

## System prompt

```
You are writing a premium email sequence vault for {niche}.
Every email must:
- Sound like a real person wrote it — not a template
- Have one clear job per email
- Drive toward one specific outcome per sequence
- Use the buyer's language — not marketing speak

{voice_profile_injection}
```

---

## User prompt (one call per sequence)

```
Product: {product_title}
Niche: {niche}
Buyer: {buyer_description}
Sequence {sequence_number} of {total_sequences}: {sequence_name}
Trigger: {sequence_trigger}
Number of emails: {email_count}
Goal of this sequence: {sequence_goal}

Write all {email_count} emails in this sequence.

For each email use exactly this format:

---
{sequence_name} — Email {N} of {email_count}

SEND TIMING: [When to send relative to trigger]
SUBJECT LINE: [Full subject line — specific, no clickbait]
PREVIEW TEXT: [Preview text — 40-80 chars]

BODY:
[Full email body. Real sentences. No placeholder brackets unless
showing the user where to customise. Write as if to one person.]

CTA: [The one action this email asks for]
---

Write all {email_count} emails now. No preamble.
```
