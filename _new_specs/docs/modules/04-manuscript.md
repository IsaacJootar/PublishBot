# Module 04 — Manuscript Writer

## What this module does

Writes the full manuscript chapter by chapter.
Every chapter is generated with the voice profile injected.
User reviews and approves each chapter individually.
Tracks word count across the full manuscript.
Final approved manuscript can be exported as DOCX or PDF.

---

## UI page: pages/05_write.py

### Layout — before writing starts

```
[Pipeline progress bar — Step 3 of 6 highlighted]

Header: "Write the content"
Subtext: "I'll write each chapter one at a time. You review and approve as we go."

[Progress summary:]
  "0 of [X] chapters written · 0 words so far"

[Chapter list — all locked except Chapter 1:]
  Chapter 1: [title] — [Write this chapter →]
  Chapter 2: [title] — locked (grey)
  Chapter 3: [title] — locked (grey)
  ...
```

### During chapter generation

```
[Spinner with message: "Writing Chapter [N]: [title]..."]
[Subtext: "This takes about 15 seconds"]
```

### After chapter generation

```
[Green badge: "Claude handled this"]

Header: "Chapter [N]: [title]"
Word count: "[X] words"

[Full chapter text — displayed in readable format]
[Editable text area below for user to make changes]

[Actions:]
  [Yes, this looks good →]   ← primary, violet button
  [Rewrite this chapter]     ← secondary
  [Edit and save my changes] ← appears when user edits text area

[After approval:]
  Green checkmark on chapter in list
  Next chapter unlocks
  Button: "Write Chapter [N+1] →"
```

### After all chapters approved

```
Header: "Your manuscript is complete 🎉"
Stats: "[X] chapters · [X] words · [X] pages estimated"

[Export actions:]
  [Download as Word file (.docx)]   ← green, prominent
  [Download as PDF]                 ← secondary

[Pipeline actions:]
  [Create illustration prompts →]
  [Skip to KDP listing →] (if no illustrations needed)
```

---

## Writing rules for AI

These are embedded in the manuscript prompt (docs/prompts/manuscript.md):

- Match the author's voice profile exactly
- Use age-appropriate vocabulary for the target reader
- Children's books: short sentences, vivid language, one idea per page
- Parenting guides: practical, direct, example-driven, no jargon
- Each chapter ends with a natural transition to the next
- Never pad with filler content — every sentence earns its place
- Target word counts per format:
  - Children's picture book (ages 3-6): 500-800 words total
  - Children's chapter book (ages 6-10): 4,000-10,000 words total
  - Parenting guide: 15,000-25,000 words total
  - Educational non-fiction: 10,000-20,000 words total

---

## Data saved to manuscripts table

One row per chapter:
- project_id
- chapter_number
- chapter_title
- content (full text)
- word_count
- is_approved

---

## Export logic

Combine all approved chapters in order.
Add title page (title, author name/pen name).
Add table of contents (auto-generated from chapter titles).
Export via Pandoc to DOCX and PDF.
Save to /data/exports/[project_id]/
Show download button in Streamlit.
