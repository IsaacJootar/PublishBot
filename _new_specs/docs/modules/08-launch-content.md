# Module 08 — Launch Content Generator

## What this module does

Generates a complete launch content pack for any finished project.
Output: 5 social posts, 3-email sequence, 1 review request message.
Everything written in the author's voice.
All content copy-paste ready with individual copy buttons.

---

## UI page: pages/09_launch.py

### Layout

```
[Pipeline progress bar — Step 6 of 6 highlighted (books) or Step 5 of 5 (products)]

[Green badge: "Claude handled this"]

Header: "Prepare your launch"
Subtext: "Everything you need to tell the world about '[project title]'"

[Button: "Generate my launch pack →"]
```

### Results — Social Posts section

```
Header: "5 social media posts"
Subtext: "Each one written for a different platform and angle."

[Post 1 — LinkedIn / Facebook]
  Label: "Authority angle — lead with your expertise"
  [Full post text]
  [Copy] button
  Character count

[Post 2 — X / Twitter]
  Label: "Direct and punchy — built for X"
  [Full post text — under 280 chars]
  [Copy] button

[Post 3 — Instagram]
  Label: "Visual storytelling — for Instagram caption"
  [Full post text + hashtag block]
  [Copy] button

[Post 4 — Pinterest]
  Label: "SEO-friendly description"
  [Full description text]
  [Copy] button

[Post 5 — WhatsApp Broadcast]
  Label: "Personal tone — for your contacts"
  [Full message text]
  [Copy] button
```

### Results — Email Sequence section

```
Header: "3-email launch sequence"
Subtext: "Send these over 5-7 days around your launch."

[Email 1 — Announcement]
  Send timing: "Launch day"
  Subject line: [editable]
  Body: [full email]
  [Copy subject] [Copy body]

[Email 2 — Social proof]
  Send timing: "Day 3 after launch"
  Subject line: [editable]
  Body: [full email]
  [Copy subject] [Copy body]

[Email 3 — Last chance]
  Send timing: "Day 7 after launch"
  Subject line: [editable]
  Body: [full email]
  [Copy subject] [Copy body]
```

### Results — Review Request section

```
Header: "Review request message"
Subtext: "Send this to early readers 7-14 days after they receive the book."

[Full message text]
[Copy] button

[Small tip box:]
"Send this as a WhatsApp message, email, or DM.
 The first 5 reviews make the biggest difference on Amazon."
```

---

## Email sequence structure

### Email 1 — Announcement
```
Subject: [Specific, curiosity-driven, mentions the outcome]
Body:
- Opening: personal story or the problem this solves
- What I made: describe the book/product simply
- Who it helps: specific, not generic
- Where to get it: direct link
- P.S.: one specific thing that makes it different
```

### Email 2 — Social proof
```
Subject: [Reader result or reaction angle]
Body:
- Short story: someone who needed this
- What changed for them
- What's inside (specific details they haven't heard yet)
- Link
- P.S.: reminder of price or early access angle
```

### Email 3 — Last chance
```
Subject: [Urgency or loss-framing]
Body:
- Direct: "This is my last email about [title]"
- Remind them of the core problem it solves
- One sentence on what they get
- Link
- Close: what happens if they don't act
```

---

## Platform post rules

| Platform | Length | Tone | Special |
|---|---|---|---|
| LinkedIn | 150-300 words | Professional but personal | Line breaks, no hashtags |
| X/Twitter | Under 280 chars | Direct, punchy | Optional 1-2 hashtags |
| Instagram | 100-150 words | Visual, warm | 5-10 hashtags at end |
| Pinterest | 50-100 words | Descriptive, SEO | Include keywords naturally |
| WhatsApp | 50-100 words | Personal, casual | No hashtags, feels like a real message |

---

## Data saved to launch_packs table

All 5 social posts, 3 email subjects + bodies, review request message.
is_approved set when user clicks "Save my launch pack".
