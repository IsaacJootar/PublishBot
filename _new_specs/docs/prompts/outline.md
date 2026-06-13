# Prompt — Outline Generator

## Used in: modules/outline.py
## Purpose: Generate a complete chapter-by-chapter outline

---

## System prompt

```
You are an expert book architect. You create outlines that are:
- Structured to teach or engage the target reader progressively
- Paced correctly for the format and age group
- Clear enough that a writer can expand each chapter without confusion

{voice_profile_injection}
```

---

## User prompt (Book — Children's Educational)

```
Book title: {title}
Format: Children's Educational Book
Target age: {target_age}
Target reader description: {target_reader}

Create a complete chapter-by-chapter outline for this book.

Guidelines:
- {chapter_count} chapters total (appropriate for age group)
- Each chapter teaches one clear concept
- Build progressively — each chapter leads naturally to the next
- Keep it age-appropriate and engaging, not academic

Return as JSON:
[
  {
    "chapter_number": 1,
    "chapter_title": "Chapter title here",
    "chapter_summary": "What happens or is covered in this chapter (2-3 sentences)",
    "page_count_est": 4,
    "learning_obj": "What the child learns from this chapter",
    "illustration_note": "What the illustration for this chapter should show"
  }
]

Return only the JSON array.
```

---

## User prompt (Book — Parenting Guide)

```
Book title: {title}
Format: Parenting Guide
Target reader: {target_reader}

Create a complete chapter outline for this parenting guide.

Guidelines:
- 8-12 chapters
- Each chapter addresses one specific aspect of the problem
- Structure: start with the problem/why, build to practical solutions,
  end with implementation and results
- Every chapter must give the reader something actionable

Return as JSON:
[
  {
    "chapter_number": 1,
    "chapter_title": "Chapter title here",
    "chapter_summary": "What this chapter covers and what the reader gains (2-3 sentences)",
    "page_count_est": 12,
    "learning_obj": "The specific insight or skill the reader takes away",
    "illustration_note": null
  }
]

Return only the JSON array.
```

---

## User prompt (Digital Product — PDF Guide)

```
Product title: {title}
Product type: PDF Guide
Target buyer: {target_reader}

Create a complete section outline for this PDF guide.

Guidelines:
- 5-8 sections
- Each section solves one specific sub-problem
- Move from foundational → advanced
- Every section should end with something the reader can do immediately

Return as JSON:
[
  {
    "chapter_number": 1,
    "chapter_title": "Section title",
    "chapter_summary": "What this section covers (2-3 sentences)",
    "page_count_est": 3,
    "learning_obj": "What the buyer can DO after reading this section",
    "illustration_note": null
  }
]

Return only the JSON array.
```

---

## Parsing

Parse JSON array.
Save each chapter as a row in outlines table.
is_approved = 0 for all until user approves.
