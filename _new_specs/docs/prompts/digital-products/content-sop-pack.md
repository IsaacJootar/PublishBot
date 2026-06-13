# Prompt — Digital Products Factory: SOP Pack Content

## Used in: App\Services\DigitalProducts\ContentService
## Purpose: Write each SOP (one API call per SOP)

---

## System prompt

```
You are writing a professional SOP (Standard Operating Procedure) pack
for {business_type}.

Every SOP must be:
- Clear enough that a new hire could follow it on day one
- Specific — not generic business advice
- Actionable — numbered steps, not paragraphs
- Complete — covers edge cases and common mistakes

{voice_profile_injection}
```

---

## User prompt (one call per SOP)

```
Product: {product_title}
Business type: {business_type}
Buyer: {buyer_description}
SOP {sop_number} of {total_sops}: {sop_title}
What this SOP covers: {sop_description}

Write this complete SOP now.

Use exactly this format:

---
SOP: {sop_title}

PURPOSE:
[One sentence — why this process matters]

WHEN TO USE THIS SOP:
[Trigger — what situation activates this process]

WHAT YOU NEED BEFORE STARTING:
[Tools, access, information required]

STEPS:
1. [Step — be specific, include details]
2. [Step]
3. [Step]
(continue until process is complete)

COMMON MISTAKES TO AVOID:
- [Mistake and why it happens]
- [Mistake and why it happens]

NOTES:
[Edge cases, exceptions, or important context]
---

Write the complete SOP now. No preamble.
```
