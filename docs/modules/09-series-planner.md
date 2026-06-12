# Module 09 — Series Planner

## What this module does

Groups related books into a named series.
Locks a shared character bible and style guide across all books.
Plans future books in the series with pre-filled context.
Reduces production time on each subsequent book significantly.

---

## UI page: pages/10_my_series.py

### Layout — series list

```
Header: "My Series"
Subtext: "Group your books into series to build a loyal readership
          and speed up production."

[Button: "Create a new series +"]

[For each existing series:]
  Series name
  [X] books · [X] completed · [X] in progress
  [View series →]
```

### Create series form

```
[Text input: "What is this series called?"]
[Text input: "Describe the series in one sentence"]
[Dropdown: "What type of books?"] → format options

[Character Bible section:]
  Subtext: "Lock your characters so every book looks and sounds consistent."
  [For each main character:]
    Name
    Age / appearance
    Personality in 2-3 words
    Always wears / key visual detail
  [Add another character +]

[Style Guide section:]
  Art style (for illustration prompts): [text input]
  Tone of writing: [text input]
  Recurring themes: [text input]
  Things this series never does: [text input]

[Button: "Create my series →"]
```

### Series detail view

```
Header: "[Series name]"
Character bible summary (expandable)
Style guide summary (expandable)

[Books in series:]
  [Book title] — [status] — [Resume / View]
  [Book title] — [status] — [Resume / View]
  [+ Add a book to this series]

[Button: "Plan the next book →"]
```

### Plan next book in series

```
Subtext: "I'll pre-fill everything from your series settings."

[Shows pre-filled:]
  Series: [series name]
  Format: [locked from series]
  Character context: [loaded automatically]
  Style: [loaded automatically]

[Text input: "What's the theme or problem for this book?"]
[Button: "Research title angles →"] → goes to Research page with series context
```

---

## How series context improves production

When a project belongs to a series, the following changes:

**Research prompt:** includes series name, existing titles, and tone guidelines
so new title angles fit the series naturally.

**Outline prompt:** includes series character names and recurring elements
so Claude maintains story continuity.

**Manuscript prompt:** includes character bible so descriptions stay consistent
without the user having to specify them again.

**Illustration prompt:** character_desc is pre-filled from the series character
bible — no need to re-describe characters on every book.

---

## Data saved

Series record in series table:
- name
- description
- character_bible (full text)
- style_guide (full text)
- book_count (auto-updated)

Projects linked via series_id foreign key.
