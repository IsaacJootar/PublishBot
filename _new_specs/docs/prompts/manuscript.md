# Prompt — Manuscript Writer

## Used in: modules/manuscript.py
## Purpose: Write each chapter of the book in the author's voice

---

## System prompt

```
You are a ghostwriter. Your only job is to write content that sounds
exactly like the author whose voice profile is provided below.

Do not sound like a generic AI. Do not use filler phrases.
Do not start sentences with "In conclusion" or "Furthermore" or "It's worth noting".
Write the way a real person writes — direct, clear, purposeful.

{voice_profile_injection}
```

---

## User prompt (Children's Educational — one chapter)

```
Book title: {book_title}
Target age: {target_age}
Chapter {chapter_number} of {total_chapters}: {chapter_title}

Chapter summary: {chapter_summary}
Learning objective: {learning_obj}
Illustration note: {illustration_note}

Previous chapter ended with: {previous_chapter_last_sentence}
(Use this to ensure smooth continuity. If this is Chapter 1, ignore.)

Write this chapter now.

Rules:
- Aim for {word_count_target} words
- One clear idea per page spread (roughly every 100-150 words)
- Use simple, age-appropriate language for {target_age} year olds
- Sentences: short and rhythmic. Children like rhythm.
- Include natural repetition where it helps learning
- End the chapter at a natural stopping point that flows to the next chapter
- Do not include chapter numbers or titles in the text — just the content
- Write as if you are telling the story to a child directly

Write the chapter now. No preamble, no commentary. Just the chapter.
```

---

## User prompt (Parenting Guide — one chapter)

```
Book title: {book_title}
Target reader: {target_reader}
Chapter {chapter_number} of {total_chapters}: {chapter_title}

Chapter summary: {chapter_summary}
Key insight for this chapter: {learning_obj}

Previous chapters covered: {previous_chapters_summary}

Write this chapter now.

Rules:
- Aim for {word_count_target} words
- Open with a parent's real experience (relatable scenario)
- Build to the insight or solution
- Include at least one practical, actionable step
- Use subheadings if the chapter covers multiple points
- Close with either a summary, a challenge, or a motivating statement
- Speak to the parent as an equal — not as an expert lecturing
- Do not include the chapter number in the text

Write the chapter now. No preamble, no commentary. Just the chapter.
```

---

## User prompt (Digital Product — PDF Guide section)

```
Product title: {product_title}
Target buyer: {target_reader}
Section {chapter_number} of {total_chapters}: {chapter_title}

Section summary: {chapter_summary}
What the buyer should be able to DO after this section: {learning_obj}

Write this section now.

Rules:
- Aim for {word_count_target} words
- Lead with the problem this section solves
- Be practical — steps, examples, specifics
- Use subheadings for each distinct point
- End with a clear action the reader can take today
- No fluff, no padding — every sentence earns its place

Write the section now. No preamble. Just the content.
```

---

## Word count targets by format

| Format | Per chapter target |
|---|---|
| Children's picture book (ages 3-6) | 60-120 words |
| Children's early reader (ages 6-8) | 200-400 words |
| Children's chapter book (ages 8-12) | 800-1500 words |
| Parenting guide chapter | 1500-2500 words |
| Educational non-fiction chapter | 1200-2000 words |
| PDF Guide section | 400-800 words |

---

## Context building across chapters

Before writing each chapter, build the previous_chapters_summary:
- Concatenate the first and last paragraph of each approved chapter
- Keep it under 200 words total
- Pass to the prompt so Claude maintains continuity

For chapter 1: previous_chapters_summary = ""
