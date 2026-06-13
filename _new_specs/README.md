# PublishAI — Your Personal Publishing Engine

Turn one topic into a sellable book or digital product.
Runs on your laptop. Powered by Claude AI.
Built with Laravel 13 + Tailwind + DaisyUI.

---

## What it does

You type a topic. PublishAI handles everything else:
- Finds 10 title angles buyers are already searching for
- Builds a full chapter outline
- Writes the entire manuscript in your voice
- Generates consistent illustration prompts for every page
- Creates your complete Amazon KDP listing
- Writes your full launch pack (social posts + emails)

Two product types: **KDP Books** and **Digital Products** (prompt packs, PDF guides, swipe files, toolkits).

---

## Requirements

- PHP 8.3+
- Composer
- Node.js + NPM
- Pandoc (for Word and PDF export)
- A Claude API key (free to get at console.anthropic.com)

---

## Setup — 6 steps

### Step 1 — Install PHP 8.3+
Mac: `brew install php`
Windows: Download from windows.php.net
Linux: `sudo apt install php8.3`

### Step 2 — Install Composer
Download from getcomposer.org

### Step 3 — Install Pandoc
Download from pandoc.org/installing.html
This is needed for Word and PDF export.

### Step 4 — Install the app

```bash
cd publishai
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
```

### Step 5 — Add your API key

Open the `.env` file in any text editor.
Add your Claude API key:
```
ANTHROPIC_API_KEY=sk-ant-your-key-here
```
Get your key free at console.anthropic.com

### Step 6 — Run the app

```bash
php artisan serve
```

Open your browser at: http://localhost:8000

In a second terminal, start the queue worker:
```bash
php artisan queue:work
```

---

## Your first book in 60 minutes

1. Go to **Settings** → add your Claude API key → test connection
2. Go to **My Voice** → upload some of your writing → save your voice profile
3. Go to **Research** → type your topic → pick the best title
4. Click through **Outline** → approve the chapters
5. Click through **Write** → approve each chapter as it generates
6. Go to **Illustrations** → copy prompts into Midjourney or Ideogram
7. Go to **KDP Listing** → copy everything into your Amazon KDP dashboard
8. Go to **Launch** → copy your social posts and emails

Done.

---

## Cost to run

About $0.70 per full book end-to-end in Claude API calls.
Digital products cost about $0.30 each.
Everything else is free.
