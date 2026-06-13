# Module 07 — Digital Product Builder

## What this module does

Builds a complete sellable digital product from a single topic.
Product types: prompt packs, Notion template guides, PDF guides,
swipe files, niche planning toolkits.
Generates the full product content AND the sales page copy.
Everything ready to upload to Gumroad, Selar, or Payhip.

---

## UI page: pages/08_digital_product.py

### Layout — product setup

```
[Pipeline progress bar — Step 3 of 5 highlighted]

Header: "Build your digital product"
Subtext: "Tell me what type of product you want to create."

[Dropdown: "What type of product?"]
  Options:
  - Prompt Pack (e.g. "50 Claude prompts for solopreneurs")
  - PDF Guide (e.g. "How to publish a children's book in 30 days")
  - Swipe File (e.g. "100 email subject lines that convert")
  - Planning Toolkit (e.g. "90-day KDP launch planner")
  - Notion Template (e.g. "KDP author workspace")

[Dropdown: "Where will you sell it?"]
  Options: Gumroad / Selar / Payhip

[Text input: "Describe your ideal buyer in one sentence"]
[Text input: "What's the main problem this product solves?"]

[Button: "Build my product →"]
```

### Product structure review (Step 3)

```
[Green badge: "Claude handled this"]

Header: "Product structure — [X] sections"
Subtext: "Review the structure before I write the full content."

[For each section:]
  Section title (editable)
  What it covers (editable)
  Estimated items or pages

[Button: "This looks good — write the full product →"]
[Button: "Regenerate structure"]
```

### Full content (Step 3 continued)

```
[Green badge: "Claude handled this"]

[For each section — displayed as tabs or accordion:]
  Section title
  Full content (all prompts / guide pages / swipe copy / templates)
  [Edit] button on each section
  [Approve this section ✓] toggle

[When all sections approved:]
  [Download as PDF]
  [Download as Word file]
  [Continue to Sales Page →]
```

### Sales page (Step 4)

```
[Green badge: "Claude handled this"]

Header: "Your sales page copy"
Subtext: "Copy and paste this into Gumroad, Selar, or Payhip."

[Product title — editable]
[Tagline — editable, 1 sentence]
[Full sales page body — editable]
  Structure: Hook → Problem → Solution → What's inside → Who it's for →
             Objection handler → Price justification → CTA
[Price recommendation with reasoning]

[Copy all] button

[ORANGE BADGE: "Your action needed"]
Upload instructions:
  1. Create a free account at [platform]
  2. Click "New product"
  3. Upload your PDF file
  4. Copy the title, tagline, and description from above
  5. Set your price and publish

[Button: Download product PDF]
[Button: Continue to Launch →]
```

---

## Product type specifications

### Prompt Pack
- 30-75 prompts organised into categories
- Each prompt: title + the full prompt text + a usage tip
- Intro section explaining how to use the pack
- PDF format, clean layout

### PDF Guide
- 15-30 pages
- Chapter-based like a mini book
- Step-by-step, practical, action-focused
- No padding — every page earns its place

### Swipe File
- 50-150 examples organised by type
- Each item: the copy + a brief note on why it works
- Introduction explaining when to use each type

### Planning Toolkit
- 5-10 planning templates (tables, checklists, trackers)
- Instruction page for each template
- Cover page + contents page

### Notion Template
- Written as a guide document (Claude cannot build Notion directly)
- Includes: full structure specification, all database fields, all views
- User builds it in Notion from the spec
- Or: package as a detailed PDF guide with screenshots guide

---

## Price recommendations

| Product type | Recommended price |
|---|---|
| Prompt Pack (30-50 prompts) | $9-$17 |
| Prompt Pack (50-75 prompts) | $17-$27 |
| PDF Guide (15-20 pages) | $7-$12 |
| PDF Guide (20-30 pages) | $12-$19 |
| Swipe File | $9-$15 |
| Planning Toolkit | $12-$25 |
| Notion Template guide | $9-$19 |
