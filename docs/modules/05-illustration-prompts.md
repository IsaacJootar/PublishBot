# Module 05 — Illustration Prompt Generator

## What this module does

Generates one detailed, ready-to-use Midjourney or Ideogram prompt per page.
Locks the character description and art style across ALL pages for consistency.
Displays each page as a card: page text on left, prompt on right.
User copies each prompt, generates in Midjourney/Ideogram, then ticks it done.
Once all pages are ticked, the pipeline advances.

THIS IS A MANUAL STEP — always shown with orange badge.

---

## UI page: pages/06_illustrations.py

### Layout — top of page

```
[Pipeline progress bar — Step 4 of 6 highlighted]

[ORANGE BADGE: "Your action needed"]

Header: "Create your illustrations"
Subtext: "I've written a prompt for every page. Copy each one into
          Midjourney or Ideogram to generate your illustrations."

[Instructions box — orange border:]
  "Here's what to do:
   1. Copy the prompt for each page using the Copy button
   2. Open Midjourney (midjourney.com) or Ideogram (ideogram.ai) — both have free tiers
   3. Paste the prompt and generate your image
   4. Save the image to your computer
   5. Tick the checkbox below to mark that page as done
   When all pages are ticked, you can move on."

[Style lock box — shown prominently:]
  "Character & style locked across all pages:"
  Character: [character description]
  Art style: [style description]
  [Green badge: "Consistent across all [X] pages"]
```

### Per-page cards

```
[Card for each page:]
  Left column (40%):
    "Page [N]"
    [Page text from manuscript]

  Right column (60%):
    [Orange badge: "Your action needed"]
    [Full prompt text — in a code/monospace block]
    [Copy prompt] button
    [ ] Tick when illustration is done

  [If ticked: green checkmark replaces orange badge]
```

### Progress tracker

```
[Progress bar:]
  "[X] of [Y] pages illustrated"

[When all ticked:]
  "All illustrations done! 🎉"
  [Button: "Continue to KDP Listing →"]
```

---

## Prompt structure (what each generated prompt contains)

Every prompt follows this locked template:

```
[Art style]. [Character description — exact same every page].
[Scene description for this specific page].
[Lighting and mood]. [Composition note].
[Technical specs: aspect ratio, quality].
```

Example output:
```
Soft watercolour illustration, children's picture book style.
A cheerful 6-year-old Nigerian girl named Amara with curly black hair,
brown skin, wearing a yellow dress — consistent character throughout.
Amara is sitting at a wooden desk looking at a glowing tablet screen,
her eyes wide with curiosity. Warm afternoon light through a window.
Simple background, plenty of white space. 4:3 ratio, high detail.
```

---

## Character consistency rules

On first generation:
- Extract main character description from manuscript
- Lock: age, appearance, clothing style, skin tone, hair
- This locked description is prefixed to EVERY page prompt

If multiple characters:
- Lock each character separately
- Include all relevant characters in each page prompt

---

## Data saved to illustration_prompts table

One row per page:
- project_id
- page_number
- page_text
- prompt (full generated prompt)
- character_desc (locked)
- style_desc (locked)
- is_completed (0/1 — user ticks this)

---

## Regeneration

"Regenerate this prompt" button on each card.
"Regenerate all prompts" button at top — asks for confirmation.
Character and style locks persist unless user explicitly resets them.
