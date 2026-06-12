# Prompt — Digital Product Builder

## Used in: modules/digital_product.py
## Purpose: Generate full digital product content and sales page

---

## System prompt

```
You are a digital product creator who builds practical, high-value resources
that solopreneurs and creators pay for without hesitation.

Your products are specific, not generic. Dense with value, not padded.
Every item in a product earns its place.

{voice_profile_injection}
```

---

## User prompt — Product Structure

```
Product title: {title}
Product type: {product_type}
Target buyer: {target_reader}
Core problem it solves: {problem}

Generate the complete structure for this {product_type}.

Return as JSON:
{
  "sections": [
    {
      "section_number": 1,
      "section_title": "Section title",
      "what_it_covers": "What's in this section (1-2 sentences)",
      "item_count": 10,
      "estimated_pages": 2
    }
  ],
  "total_items": 50,
  "total_pages_est": 18,
  "tagline": "One sentence that captures the product's value"
}

For prompt packs: sections = categories of prompts
For PDF guides: sections = chapters
For swipe files: sections = categories of copy examples
For toolkits: sections = individual templates/tools

Return only the JSON object.
```

---

## User prompt — Write Section Content (one call per section)

### For Prompt Packs
```
Product: {product_title}
Section {section_number}: {section_title}
What it covers: {what_it_covers}
Target buyer: {target_reader}

Write {item_count} Claude/AI prompts for this section.

For each prompt:
PROMPT TITLE: [Short title]
USE WHEN: [One sentence — when to use this prompt]
THE PROMPT:
[Full, ready-to-use prompt text. Use [BRACKETS] for variables the user fills in.]
TIP: [One sentence on getting the best result]

---

Write all {item_count} prompts now. No preamble.
```

### For PDF Guides
```
Product: {product_title}
Section {section_number} of {total_sections}: {section_title}
What it covers: {what_it_covers}
What buyer can DO after reading: {learning_obj}
Target buyer: {target_reader}

Write this section now.

Rules:
- {word_count_target} words
- Lead with the problem or question
- Use subheadings for each point
- Be specific — steps, examples, numbers
- End with one clear action
- No padding, no throat-clearing

Write now. No preamble.
```

### For Swipe Files
```
Product: {product_title}
Category {section_number}: {section_title}
Target buyer: {target_reader}

Write {item_count} swipe file examples for this category.

For each example:
LABEL: [Short descriptive label]
COPY: [The actual swipe copy — ready to use or adapt]
WHY IT WORKS: [One sentence explanation]

---

Write all {item_count} examples now. No preamble.
```

---

## User prompt — Sales Page Copy

```
Product title: {product_title}
Product type: {product_type}
Platform: {platform}
Target buyer: {target_reader}
Core problem it solves: {problem}
What's inside (summary): {product_contents_summary}
Recommended price: ${price}

Write the complete sales page for this product.

Structure:
1. HEADLINE: Specific, outcome-focused, no hype
2. SUBHEADLINE: Who it's for and what they get
3. OPENING HOOK: The problem (make them feel seen — 2-3 sentences)
4. WHAT'S INSIDE: Bullet list of 5-8 specific items (not vague — be exact)
5. WHO THIS IS FOR: 3 specific "this is for you if..." statements
6. WHO THIS IS NOT FOR: 1-2 honest statements (builds trust)
7. PRICE JUSTIFICATION: Why this is worth more than the price
8. CALL TO ACTION: Direct, simple, one sentence

Total: 200-350 words.

Return as JSON:
{
  "headline": "...",
  "subheadline": "...",
  "full_sales_page": "Complete formatted sales page text",
  "platform_tagline": "One line for the product tagline field"
}

Return only the JSON object.
```
