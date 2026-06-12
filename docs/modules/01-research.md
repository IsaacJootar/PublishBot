# Module 01 — Research Engine

## What this module does

Takes one topic from the user.
Returns 10 problem-based, buyer-intent title angles.
Scores each angle by buyer intent (1-100) and competition level (Low/Medium/High).
User picks one title to proceed with.
A new project record is created with the selected title.

---

## UI page: pages/03_research.py

### Layout

```
Header: "Find the right title"
Subtext: "Type a topic and I'll find 10 angles that actually sell."

[Text input: "What do you want to write about?"]
[Dropdown: "What type of product?"] → Book / Digital Product
[If Book selected, show:]
  [Dropdown: "What type of book?"] → Children's Educational / Children's Story /
                                      Parenting Guide / Educational Non-fiction
  [Text input: "Who is this for? (age or reader description)"]
[Button: "Find title angles →"]
```

### Results display

After generation, show:
- Summary row: "10 angles found · 3 hot opportunities · Top score: XX"
- Top 3 recommended (marked with violet "Top pick" badge)
- All 10 as cards

Each card shows:
- Title angle (large, clear)
- Format suggestion
- Buyer intent score with visual bar
- Competition level badge (green=Low, amber=Medium, red=High)
- Why this works (2-3 sentence explanation)
- "Use this title →" button

Green badge: "Claude handled this" on the results section header.

### On title selection

- Show confirmation: "Great choice. Starting your project with this title."
- Create project record in SQLite
- Set current_step = 1 (research done), advance to step 2
- Button: "Build the outline →" navigates to Outline page

---

## Data flow

Input:
- topic (string)
- product_type ('book' or 'digital_product')
- book_format (string, if book)
- target_reader (string)

Output saved to research_results table:
- 10 rows, one per title angle
- is_selected = 1 on chosen title

Project created in projects table:
- title = selected title angle
- product_type = input product_type
- book_format = input book_format
- target_reader = input target_reader
- current_step = 2
- voice_profile_id = active voice profile id

---

## Regeneration

"Try different angles" button regenerates all 10.
Previous results saved (not deleted) — show as "Previous results" collapsed section.
User can go back and pick from any previous batch.
