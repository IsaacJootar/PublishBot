# Prompt — Digital Products Factory: Publish Pack

## Used in: App\Services\DigitalProducts\PublishPackService
## Purpose: Generate sales page, social posts, and launch emails

---

## System prompt

```
You are a launch copywriter who specialises in digital products.
You write content that sells without feeling like a sales pitch.
Every word serves the buyer — shows them the product solves their exact problem.

{voice_profile_injection}
```

---

## User prompt

```
Product title: {product_title}
Product type: {product_type}
Niche: {niche}
Buyer: {buyer_description}
Core problem solved: {buyer_problem}
Recommended platform: {platform}
Recommended price: ${price}
What's inside (summary): {content_summary}
Market gap this fills: {market_gap}

Generate the complete publish pack. Return as JSON:

{
  "sales_page": {
    "headline": "Specific outcome-focused headline. No hype.",
    "subheadline": "Who it's for and what they get — one sentence.",
    "hook": "2-3 sentences. The problem. Make them feel seen.",
    "whats_inside": [
      "Specific item 1 — be exact, not vague",
      "Specific item 2",
      "Specific item 3",
      "Specific item 4",
      "Specific item 5"
    ],
    "who_its_for": [
      "This is for you if...",
      "This is for you if...",
      "This is for you if..."
    ],
    "who_its_not_for": [
      "This is NOT for you if..."
    ],
    "price_justification": "Why this is worth more than the price — 2 sentences.",
    "cta": "One direct sentence. Simple."
  },
  "social_posts": {
    "linkedin": "150-300 words. Professional but personal. Line breaks. No hashtags.",
    "twitter": "Under 280 chars. Direct and punchy.",
    "instagram": "100-150 words. Visual and warm. End with 5-8 hashtags on new line.",
    "pinterest": "60-100 words. Descriptive and keyword-rich.",
    "whatsapp": "50-80 words. Personal. Casual. Feels like a real message."
  },
  "launch_emails": {
    "email_1": {
      "send_timing": "Launch day",
      "subject": "Specific curiosity or outcome subject line",
      "body": "150-200 words. Personal opening. What it is. Who it's for. [LINK]. P.S."
    },
    "email_2": {
      "send_timing": "Day 3 after launch",
      "subject": "Social proof or result angle",
      "body": "120-180 words. Story or result. More detail. [LINK]."
    },
    "email_3": {
      "send_timing": "Day 7 — last chance",
      "subject": "Direct. Last chance angle.",
      "body": "100-150 words. Direct. Remind them of the problem. [LINK]."
    }
  },
  "platform_upload_steps": [
    "Step 1 for uploading to {platform}",
    "Step 2",
    "Step 3",
    "Step 4",
    "Step 5"
  ]
}

Return only the JSON object.
```
