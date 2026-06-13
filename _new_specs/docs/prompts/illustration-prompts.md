# Prompt — Illustration Prompt Generator

## Used in: modules/illustration_prompts.py
## Purpose: Generate consistent Midjourney/Ideogram prompts for every page

---

## Step 1 — Extract and lock character + style (run once per project)

### System prompt
```
You are an art director for children's books. You create precise, consistent
illustration briefs that ensure a character looks exactly the same on every
single page of a book.
```

### User prompt
```
Book title: {book_title}
Target age: {target_age}
Full manuscript: {full_manuscript_text}

Do two things:

1. EXTRACT THE MAIN CHARACTER(S):
For each main character, define:
- Name
- Age and size
- Skin tone (be specific: e.g. "warm brown", "deep ebony", "golden tan")
- Hair (colour, texture, style)
- Eyes (colour, shape)
- Typical clothing (specific items, colours)
- One defining visual detail that appears on every page

2. DEFINE THE ART STYLE:
- Medium (e.g. "soft watercolour", "bold digital illustration", "pencil and ink")
- Colour palette (name 4-6 specific colours)
- Mood/atmosphere (e.g. "warm and cozy", "bright and energetic")
- Line style (e.g. "soft edges", "clean bold outlines")
- Background style (e.g. "minimal with colour washes", "detailed environments")

Return as JSON:
{
  "characters": [
    {
      "name": "Character name",
      "description": "Full locked visual description — every detail needed
                      to draw this character identically on every page"
    }
  ],
  "art_style": "Complete art style description in one paragraph.
                Must be specific enough to produce consistent results
                across 20+ different illustration generations."
}

Return only the JSON object.
```

---

## Step 2 — Generate prompt for each page

### System prompt
```
You are an expert at writing AI image generation prompts for children's
book illustrations. Your prompts produce consistent, beautiful results
that look like they belong in a professionally published book.
```

### User prompt (one call per page)
```
Book: {book_title}
Page {page_number} of {total_pages}

LOCKED CHARACTER DESCRIPTION (use exactly as written):
{character_descriptions}

LOCKED ART STYLE (use exactly as written):
{art_style}

PAGE TEXT:
{page_text}

Write an illustration prompt for this page.

Rules:
- Start with the art style (copy exactly from locked style)
- Include the character description (copy exactly — do not paraphrase)
- Describe the specific scene on this page
- Specify mood, lighting, composition
- End with technical specs: aspect ratio 4:3, high detail, children's book illustration
- Keep total prompt under 120 words
- Never reference text or words in the image

Return only the prompt. No explanation, no preamble.
```

---

## Example output prompt

```
Soft watercolour illustration, children's picture book style, warm colour
palette of dusty rose, sage green, golden yellow, and cream.
A cheerful 7-year-old girl named Zara with deep brown skin, curly black
hair in two puffs tied with yellow ribbons, wearing a red dress with white
dots — consistent character throughout.
Zara is sitting cross-legged on a colourful rug, holding an open book,
her face lit with wonder and delight. Cozy bedroom setting, warm lamp
light, soft shadows. Simple background with a bookshelf suggested in
soft washes behind her. Centred composition, plenty of breathing room.
4:3 ratio, high detail, professional children's book illustration.
```

---

## Consistency verification

After generating all prompts, run a check:
- Every prompt must contain the locked character description
- Every prompt must start with the locked art style
- Flag any prompts missing these elements for regeneration
