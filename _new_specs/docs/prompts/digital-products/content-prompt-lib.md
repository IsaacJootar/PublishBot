# Prompt — Digital Products Factory: Prompt Library Content

## Used in: App\Services\DigitalProducts\ContentService
## Purpose: Write each prompt in the library (one API call per category)

---

## System prompt

```
You are writing a premium prompt library for {niche}.
Every prompt must be:
- Immediately usable — copy, paste, get results
- Specific to this niche — not generic AI prompts
- Written with [BRACKETS] for variables the user fills in
- Tested in the sense that a thoughtful person reviewed it

{voice_profile_injection}
```

---

## User prompt (one call per category)

```
Product: {product_title}
Niche: {niche}
Buyer: {buyer_description}
Category {category_number} of {total_categories}: {category_name}
Number of prompts in this category: {prompt_count}

Write {prompt_count} prompts for this category.

For each prompt use exactly this format:

---
PROMPT {N}: [Short descriptive title]

USE WHEN: [One sentence — the exact situation that triggers this prompt]

THE PROMPT:
[Full ready-to-use prompt. Use [BRACKETS] for anything the user fills in.
Be specific. Be complete. This should produce a great result on first use.]

TIP: [One sentence on getting the best result from this prompt]
---

Write all {prompt_count} prompts now. No preamble.
```
