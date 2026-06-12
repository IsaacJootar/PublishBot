# Module 03 — Outline Generator

## What this module does

Takes the selected title and book format from the project.
Generates a complete chapter-by-chapter outline.
Each chapter includes: title, summary, page estimate, learning objective,
and illustration note.
User reviews and approves the outline before writing begins.

---

## UI page: pages/04_outline.py

### Layout

```
[Pipeline progress bar — Step 2 of 6 highlighted]

Header: "Build the structure"
Subtext: "I'll create a complete chapter plan for '[project title]'"

[Project info pill: title · format · target reader]

[Button: "Build my outline →"]
```

### Results display

```
[Green badge: "Claude handled this"]

Header: "Your outline — [chapter count] chapters"
Subtext: "Review each chapter. Edit anything that doesn't feel right.
          When you're happy, approve the whole outline."

[For each chapter:]
  Card layout:
  - Chapter number + title (editable inline)
  - Summary (editable inline)
  - Page estimate (small pill)
  - Learning objective (if educational book)
  - Illustration note (italic, smaller text)
  - [Approve this chapter ✓] toggle

[Bottom actions:]
  [Approve all chapters →]
  [Regenerate entire outline]
  [Back to Research]
```

### On full approval

"Outline saved. Ready to start writing."
Button: "Start writing →" navigates to Write page
Set project current_step = 3

---

## Chapter count guidelines

| Format | Recommended chapters |
|---|---|
| Children's educational (ages 3-6) | 5-8 chapters / scenes |
| Children's educational (ages 6-10) | 8-12 chapters |
| Children's story | 6-10 chapters / scenes |
| Parenting guide | 8-12 chapters |
| Educational non-fiction | 10-15 chapters |
| Digital product — PDF guide | 5-8 sections |
| Digital product — prompt pack | N/A (uses product structure module) |

---

## Data saved to outlines table

One row per chapter:
- project_id
- chapter_number
- chapter_title
- chapter_summary
- page_count_est
- learning_obj
- illustration_note
- is_approved (0 until user approves)

---

## Regeneration

Individual chapter: "Rewrite this chapter plan" button on each card.
Full outline: "Regenerate entire outline" button — asks for confirmation first.
Previous outline versions not deleted — stored for reference.
