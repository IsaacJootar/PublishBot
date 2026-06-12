# PublishAI — Colour System

## Brand colours

```
Primary:         #6C3CE1   /* Deep violet — buttons, active nav, links */
Primary dark:    #5A2EC9   /* Hover and pressed states */
Primary light:   #F0EBFF   /* Tint — backgrounds, tags, selections */
Accent:          #F59E0B   /* Amber — CTAs, highlights, launch items */
Accent dark:     #D97706   /* Amber hover */
```

---

## Sidebar

```css
background: linear-gradient(165deg, #1A0D33 0%, #0D0719 100%);
```

Active nav item:
```css
background: rgba(108, 60, 225, 0.35);
border-left: 3px solid #6C3CE1;
```

Inactive nav text: `rgba(255, 255, 255, 0.5)`

---

## Light mode (default)

```
Page background:     #FFFFFF
Surface / panels:    #F8F7FF    /* Faint violet tint on cards */
Border:              #E4E0F0
Title text:          #0F0A1E
Body text:           #5C5470
Muted text:          #9B93B0
```

---

## Dark mode (toggle available)

```
Page background:     #0F0A1E
Cards / panels:      #1A1035
Card border:         #2C1F50
Title text:          #F0EBFF
Body text:           #9B8EC4
Muted text:          #5C4E80
```

---

## Pipeline step colours

Used in the progress indicator across both pipelines:

```
Completed step:     #10B981   /* Emerald green — done */
Current step:       #6C3CE1   /* Violet — in progress */
Future step:        #C4B8E0   /* Muted — locked */
```

---

## Automation label colours — THE MOST IMPORTANT RULE

**Every output in the app is labelled as automated or manual.**

```
Green label (automated):
  background: #ECFDF5
  text:       #065F46
  border:     #6EE7B7
  label text: "Claude handled this"

Orange label (your action needed):
  background: #FFFBEB
  text:       #92400E
  border:     #FCD34D
  label text: "Your action needed"
```

Apply these as pill badges on every module output card.

---

## Status colours

```
Success / complete:   #10B981   /* Emerald */
Error / failed:       #EF4444   /* Red */
Warning / pending:    #F59E0B   /* Amber */
Info / neutral:       #6C3CE1   /* Violet */
Archived:             #9B93B0   /* Muted */
```

---

## Stat / metric cards on Dashboard

```
Total projects:     bg #F0EBFF   value #5A2EC9   (violet)
Books completed:    bg #ECFDF5   value #065F46   (green)
Products created:   bg #FFFBEB   value #92400E   (amber)
Words generated:    bg #EFF6FF   value #1D4ED8   (blue)
Exports made:       bg #FFF1F2   value #BE123C   (rose)
```

---

## Streamlit custom CSS

Apply via `st.markdown("<style>...</style>", unsafe_allow_html=True)` in app.py:

```css
/* Primary button */
.stButton > button {
    background-color: #6C3CE1;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
}
.stButton > button:hover {
    background-color: #5A2EC9;
}

/* Sidebar background */
[data-testid="stSidebar"] {
    background: linear-gradient(165deg, #1A0D33 0%, #0D0719 100%);
}

/* Sidebar text */
[data-testid="stSidebar"] * {
    color: rgba(255, 255, 255, 0.85) !important;
}

/* Card surface */
.publish-card {
    background: #F8F7FF;
    border: 1px solid #E4E0F0;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}

/* Green automation badge */
.badge-auto {
    background: #ECFDF5;
    color: #065F46;
    border: 1px solid #6EE7B7;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 12px;
    font-weight: 500;
}

/* Orange manual badge */
.badge-manual {
    background: #FFFBEB;
    color: #92400E;
    border: 1px solid #FCD34D;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 12px;
    font-weight: 500;
}
```

---

## Domain profile colour tags

Each domain profile gets a colour tag shown on its card.
8 preset options the user picks from:

```
Violet:   #6C3CE1   — default (General Writing)
Emerald:  #10B981   — Social Media & Algorithms
Blue:     #3B82F6   — Business & Entrepreneurship
Amber:    #F59E0B   — Children's Content
Rose:     #F43F5E   — Faith & Personal Development
Cyan:     #06B6D4   — Tech & Digital Tools
Lime:     #84CC16   — Health & Wellness
Orange:   #F97316   — Finance & Money
```

In Tailwind, render as coloured left border on domain card:
```html
<div class="card border-l-4" style="border-color: {{ $profile->color }}">
```
