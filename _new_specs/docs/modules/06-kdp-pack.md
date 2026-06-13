# Module 06 — KDP Listing Generator

## What this module does

Generates the complete Amazon KDP listing for a finished book.
Output includes: optimised title, subtitle, description, 7 backend keywords,
2 category recommendations, author bio, and price recommendation.
All sections are editable inline and copy-paste ready for the KDP dashboard.
Upload instructions shown clearly with orange badge.

---

## UI page: pages/07_kdp_listing.py

### Layout — top

```
[Pipeline progress bar — Step 5 of 6 highlighted]

[Green badge: "Claude handled this" on the generated content]
[Orange badge: "Your action needed" on the upload section]

Header: "Build your Amazon listing"
Subtext: "Everything you need to publish on KDP — copy and paste into
          Amazon's dashboard."
```

### Generated sections — each editable inline

```
Section 1: Book Title
  [Editable text field — pre-filled with optimised title]
  [Copy] button

Section 2: Subtitle
  [Editable text field]
  [Copy] button

Section 3: Book Description
  Subtext: "Amazon shows this to potential buyers. It's written to sell."
  [Editable text area — multi-line, ~150 words]
  [Copy] button
  [Preview as Amazon would show it] toggle

Section 4: Keywords (7 backend keywords)
  Subtext: "These go in Amazon's keyword fields — not visible to buyers
            but help your book get discovered."
  [7 editable text fields, one per keyword]
  [Copy all 7] button

Section 5: Categories
  [Primary category — editable]
  [Secondary category — editable]
  Subtext: "Search these exact names in Amazon's category browser."

Section 6: Author Bio
  [Editable text area — 2-3 sentences]
  [Copy] button

Section 7: Price Recommendation
  [Recommended price with reasoning]
  Subtext: "Based on comparable books in this category and format."
```

### Upload instructions section

```
[ORANGE BADGE: "Your action needed"]

Header: "How to publish on Amazon KDP"

Step-by-step instructions:
  1. Download your manuscript file using the button below
  2. Go to kdp.amazon.com — it's free to create an account
  3. Click "Add new title" → "Kindle eBook" or "Paperback"
  4. Copy and paste each section above into the matching KDP field
  5. Upload your manuscript file (.docx) and your cover image
  6. Set your price and click "Publish"

[Button: Download manuscript (.docx)]  ← green, prominent
[Button: Download as PDF]

Note: "KDP usually reviews your book within 24-72 hours."
```

---

## Description formula

The AI follows this structure for the book description:

```
Line 1: Hook — the core problem or promise (1 sentence, emotional)
Line 2-3: What the book delivers (specific outcomes)
Line 4: Who it's for (specific, not generic)
Line 5: Social proof or credibility line
Line 6: Call to action ("Perfect for [occasion]. Grab your copy today.")
```

---

## Keyword strategy

7 keywords must be:
- Problem-based (what buyer searches, not category names)
- Specific (3-5 word phrases, not single words)
- Mix of: exact problem, age/format, occasion/gift angle

Example for a screen time parenting book:
```
1. how to limit screen time kids
2. parenting screen addiction help
3. children phone addiction book
4. digital detox kids guide
5. screen time rules for toddlers
6. parenting book technology kids
7. raise kids without screens
```

---

## Data saved to kdp_listings table

- book_title
- subtitle
- description
- keywords (JSON array)
- primary_category
- secondary_category
- author_bio
- price_recommendation
- is_approved

---

## Payoneer reminder (shown as info box)

```
💡 Getting paid
Amazon KDP pays royalties approximately 60 days after the end of
the month of sale. For Nigeria and Africa, Payoneer is the most
reliable payment method. Set up your Payoneer account at
payoneer.com before publishing.
```
