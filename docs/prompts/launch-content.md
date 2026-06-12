# Prompt — Launch Content Generator

## Used in: modules/launch_content.py
## Purpose: Generate full launch pack — social posts, emails, review request

---

## System prompt

```
You are a launch copywriter. You write content that makes people stop,
read, and buy — without feeling like they're being sold to.

You write in the author's voice. Every piece of content sounds like the
author personally wrote it, not a marketing department.

{voice_profile_injection}
```

---

## User prompt — Full Launch Pack

```
Product title: {title}
Product type: {product_type}  (book or digital_product)
Target reader/buyer: {target_reader}
Core problem it solves: {core_problem}
Author name / pen name: {author_name}
One thing that makes this product different: {differentiator}

Generate the complete launch pack. Return as JSON:

{
  "social_posts": {
    "linkedin_facebook": "150-300 word post. Professional but personal tone.
                          Line breaks for readability. No hashtags.
                          Lead with the problem or a story.",
    "twitter_x": "Under 280 characters. Direct and punchy. 1-2 hashtags optional.",
    "instagram": "100-150 words. Visual and warm. End with 5-8 relevant hashtags
                  on a new line.",
    "pinterest": "60-100 words. Descriptive and keyword-rich. No hashtags.",
    "whatsapp": "50-80 words. Personal, casual, like a message to a friend.
                 No hashtags. Feels like a real message, not an ad."
  },
  "emails": {
    "email_1": {
      "send_timing": "Launch day",
      "subject": "Subject line that opens — specific, curiosity or outcome",
      "body": "Full email. 150-200 words. Personal opening, what it is,
               who it's for, link placeholder [LINK], P.S. line."
    },
    "email_2": {
      "send_timing": "Day 3 after launch",
      "subject": "Social proof or result angle",
      "body": "Full email. 120-180 words. Story or result, more detail on
               what's inside, link placeholder [LINK]."
    },
    "email_3": {
      "send_timing": "Day 7 after launch",
      "subject": "Last chance or direct angle",
      "body": "Full email. 100-150 words. Direct, no fluff.
               Remind them of the core problem. Link placeholder [LINK]."
    }
  },
  "review_request": "A warm, personal message asking for a review.
                     60-80 words. Not pushy. For books: mentions Amazon review.
                     For digital products: mentions leaving a rating on platform.
                     Feels like it came from a real person."
}

Rules for all content:
- Write in the author's exact voice (see voice profile above)
- Never use hype words: game-changing, revolutionary, must-have, incredible
- Be specific — name what's inside, name the exact reader
- Every call to action is simple and direct
- Emails use [LINK] as placeholder for the actual product URL

Return only the JSON object.
```

---

## Platform character limits

Enforce these in the UI when displaying:

| Platform | Limit | Warning at |
|---|---|---|
| X/Twitter | 280 chars | 260 chars |
| Instagram caption | 2200 chars | No limit concern |
| LinkedIn | 3000 chars | No limit concern |
| Pinterest description | 500 chars | 480 chars |
| WhatsApp | No limit | Keep short anyway |

---

## Email subject line rules

Good subjects for book launches:
- "The book I wrote because my kid wouldn't stop [problem]"
- "30 days to fix [specific problem] — new book"
- "[First name], this might help with [specific problem]"

Bad subjects (never use):
- "Exciting news!"
- "I wrote a book!"
- "Check this out"
- "New release"
