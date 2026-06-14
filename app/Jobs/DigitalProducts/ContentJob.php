<?php

namespace App\Jobs\DigitalProducts;

use App\Models\DigitalProduct;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ContentJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public int $tries = 2;

    public function __construct(public int $productId) {}

    public function handle(): void
    {
        $product = DigitalProduct::find($this->productId);
        if (! $product) {
            return;
        }

        $product->update(['status' => 'writing', 'progress_note' => 'Writing the full product...', 'error_message' => null]);

        try {
            $settings = $product->user->getSettings();
            $claude = new ClaudeService($settings);

            $structure = $product->structure_output ?? [];
            $voice = $this->voiceSuffix($product);

            match ($product->product_type) {
                'prompt_library' => $this->writePromptLibrary($product, $structure, $claude, $voice),
                'sop_pack' => $this->writeSopPack($product, $structure, $claude, $voice),
                'email_sequence_vault' => $this->writeEmailVault($product, $structure, $claude, $voice),
                'content_calendar_system' => $this->writeContentCalendar($product, $structure, $claude, $voice),
                'excel_tracker' => $this->writeExcelTracker($product, $structure, $claude, $voice),
                'notion_business_os' => $this->writeNotionOs($product, $structure, $claude, $voice),
                'website_copy_pack' => $this->writeWebsiteCopyPack($product, $structure, $claude, $voice),
                'brand_messaging_system' => $this->writeBrandMessaging($product, $structure, $claude, $voice),
                'sales_funnel_copy' => $this->writeSalesFunnel($product, $structure, $claude, $voice),
                'niche_research_report' => $this->writeNicheResearch($product, $structure, $claude, $voice),
                'buyer_persona_pack' => $this->writeBuyerPersona($product, $structure, $claude, $voice),
            };
        } catch (Throwable $e) {
            $product->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'progress_note' => null]);
            throw $e;
        }
    }

    // ── Original 3 types ─────────────────────────────────────────────────────

    private function writePromptLibrary(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $categories = $structure['categories'] ?? [];
        $total = count($categories);
        $system = "You are writing a premium prompt library for {$p->niche}. Every prompt must be immediately usable, niche-specific, and use [BRACKETS] for variables.{$voice}";

        $output = $this->header($p);

        foreach ($categories as $i => $cat) {
            $n = $i + 1;
            $p->update(['progress_note' => "Writing category {$n} of {$total}: {$cat['name']}"]);
            $count = $cat['prompt_count'] ?? 5;
            $user = "Product: {$p->product_title}\nNiche: {$p->niche}\nBuyer: {$p->buyer_description}\n"
                ."Category {$n} of {$total}: {$cat['name']}\nNumber of prompts: {$count}\n\n"
                ."Write {$count} prompts for this category. Format each as:\n\n"
                ."PROMPT N: [title]\nUSE WHEN: [trigger]\nTHE PROMPT:\n[full prompt with [BRACKETS]]\nTIP: [one sentence]\n\nNo preamble.";

            $text = $claude->complete($system, $user);
            $output .= "CATEGORY {$n}: {$cat['name']}\n".str_repeat('-', 40)."\n{$text}\n\n";
            $p->update(['content_output' => $output]);
        }

        $p->update(['current_stage' => 4, 'status' => 'draft', 'progress_note' => null]);
    }

    private function writeSopPack(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $sops = $structure['sops'] ?? [];
        $bizType = $structure['business_type'] ?? $p->niche;
        $total = count($sops);
        $system = "You are writing a professional SOP pack for {$bizType}. Numbered steps, clear, complete.{$voice}";

        $output = $this->header($p);

        foreach ($sops as $i => $sop) {
            $n = $i + 1;
            $p->update(['progress_note' => "Writing SOP {$n} of {$total}: {$sop['title']}"]);
            $user = "Product: {$p->product_title}\nBusiness type: {$bizType}\nSOP {$n} of {$total}: {$sop['title']}\nCovers: {$sop['covers']}\n\n"
                ."Write the complete SOP. Format:\n\n"
                ."SOP: {$sop['title']}\nPURPOSE:\n[one sentence]\nWHEN TO USE THIS SOP:\n[trigger]\nWHAT YOU NEED BEFORE STARTING:\n[list]\nSTEPS:\n1. ...\n2. ...\nCOMMON MISTAKES TO AVOID:\n- ...\nNOTES:\n[edge cases]\n\nNo preamble.";

            $text = $claude->complete($system, $user);
            $output .= "SOP {$n}: {$sop['title']}\n".str_repeat('-', 40)."\n{$text}\n\n";
            $p->update(['content_output' => $output]);
        }

        $p->update(['current_stage' => 4, 'status' => 'draft', 'progress_note' => null]);
    }

    private function writeEmailVault(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $sequences = $structure['sequences'] ?? [];
        $total = count($sequences);
        $system = "You are writing a premium email vault for {$p->niche}. Real person voice. Buyer's language. One job per email.{$voice}";

        $output = $this->header($p);

        foreach ($sequences as $i => $seq) {
            $n = $i + 1;
            $emailCount = $seq['email_count'] ?? 5;
            $p->update(['progress_note' => "Writing sequence {$n} of {$total}: {$seq['name']}"]);
            $user = "Product: {$p->product_title}\nNiche: {$p->niche}\nBuyer: {$p->buyer_description}\n"
                ."Sequence {$n} of {$total}: {$seq['name']}\nTrigger: {$seq['trigger']}\nNumber of emails: {$emailCount}\nGoal: {$seq['goal']}\n\n"
                ."Write all {$emailCount} emails. Format each as:\n\n"
                ."{$seq['name']} — Email N of {$emailCount}\nSEND TIMING: [when]\nSUBJECT LINE: [subject]\nPREVIEW TEXT: [preview]\nBODY:\n[full body]\nCTA: [one action]\n\nNo preamble.";

            $text = $claude->complete($system, $user);
            $output .= "SEQUENCE {$n}: {$seq['name']}\n".str_repeat('-', 40)."\n{$text}\n\n";
            $p->update(['content_output' => $output]);
        }

        $p->update(['current_stage' => 4, 'status' => 'draft', 'progress_note' => null]);
    }

    // ── New 8 types ───────────────────────────────────────────────────────────

    private function writeContentCalendar(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $opts = $p->brief_options ?? [];
        $platforms = implode(', ', $opts['platforms'] ?? ['Instagram', 'LinkedIn']);
        $freq = $opts['posting_frequency'] ?? '5';
        $pillars = json_encode($structure['content_pillars'] ?? []);
        $monthlyThemes = $structure['monthly_themes'] ?? [
            ['month' => 1, 'theme' => 'Foundation', 'focus' => 'Build awareness'],
            ['month' => 2, 'theme' => 'Authority', 'focus' => 'Build credibility'],
            ['month' => 3, 'theme' => 'Conversion', 'focus' => 'Drive sales'],
        ];

        $system = "You are a content strategist writing a 90-day content calendar for {$p->niche}. "
            ."Every post entry must be specific, compelling, and immediately actionable.{$voice}";

        $sections = [];

        // Usage guide first (per spec: first section of PDF/DOCX)
        $p->update(['progress_note' => 'Writing usage guide...']);
        $usageUser = "Niche: {$p->niche}\nPlatforms: {$platforms}\nPosting frequency: {$freq} posts/week\n\n"
            ."Write a complete usage guide for this content calendar system. Cover:\n"
            ."1. How to read this calendar\n"
            ."2. How to batch create content (one day per week strategy)\n"
            ."3. How to customise topics to your specific situation\n"
            ."4. How to use the caption frameworks\n"
            ."5. How to schedule posts using free tools (Buffer, Later, Meta Suite)\n"
            ."6. What to do when you miss a day\n"
            ."7. How to track what is working\n"
            ."8. How to refresh this calendar after 90 days\n\n"
            .'Write in clear practical language. No fluff. This buyer needs to start posting tomorrow.';
        $usageText = $claude->complete("You write usage guides for digital products.{$voice}", $usageUser);
        $sections[] = ['title' => 'How to Use This Calendar', 'body' => $usageText];

        // Content pillars section
        $pillarsUser = "Niche: {$p->niche}\nContent pillars: {$pillars}\n\n"
            ."Write a clear explanation of each content pillar. For each pillar include:\n"
            ."- What it covers and why it matters\n- Types of content that work for this pillar\n"
            ."- 5 specific topic ideas\n- What % of posts should be this pillar and why\n\nNo preamble.";
        $pillarsText = $claude->complete($system, $pillarsUser);
        $sections[] = ['title' => 'Your Content Pillars', 'body' => $pillarsText];

        // One month at a time
        foreach ($monthlyThemes as $month) {
            $monthNum = $month['month'];
            $theme = $month['theme'];
            $focus = $month['focus'];
            $startDay = ($monthNum - 1) * 30 + 1;
            $endDay = $monthNum * 30;

            $p->update(['progress_note' => "Writing Month {$monthNum}: {$theme}..."]);

            $calUser = "Niche: {$p->niche}\nTarget audience: {$p->buyer_description}\n"
                ."Platforms: {$platforms}\nMonthly theme: {$theme}\nFocus: {$focus}\n"
                ."Month: {$monthNum} (Days {$startDay}-{$endDay})\n"
                ."Posting frequency: {$freq} posts per week\nContent pillars: {$pillars}\n\n"
                ."Generate the complete content calendar for this month.\n"
                ."For each post entry include: Day | Platform | Content Pillar | Content Type | Topic | Hook | Caption Notes | CTA | Repurpose To\n"
                ."Format as a clear list. Every entry must be specific — not generic placeholders.\n"
                ."The hook must be compelling enough to stop scrolling.\nNo preamble.";

            $calText = $claude->complete($system, $calUser);
            $sections[] = ['title' => "Month {$monthNum}: {$theme}", 'body' => $calText];
        }

        // Caption frameworks
        $p->update(['progress_note' => 'Writing caption frameworks...']);
        $captionUser = "Niche: {$p->niche}\nPlatforms: {$platforms}\n\n"
            ."Write 10 proven caption frameworks with examples for {$p->niche}. For each framework:\n"
            ."- Framework name\n- When to use it\n- The structure\n- A complete example\n- Why it works\n\nNo preamble.";
        $captionText = $claude->complete($system, $captionUser);
        $sections[] = ['title' => 'Caption Frameworks', 'body' => $captionText];

        // Repurposing guide
        $p->update(['progress_note' => 'Writing repurposing guide...']);
        $repurposeUser = "Platforms: {$platforms}\nNiche: {$p->niche}\n\n"
            .'Write a complete repurposing guide. Show how one content idea becomes posts across multiple platforms. '
            .'Give 5 full examples. For each example show the original idea then how it adapts for each platform.';
        $repurposeText = $claude->complete($system, $repurposeUser);
        $sections[] = ['title' => 'Repurposing Guide', 'body' => $repurposeText];

        $this->saveJsonContent($p, $sections);
    }

    private function writeExcelTracker(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $opts = $p->brief_options ?? [];
        $trackerType = $opts['tracker_type'] ?? 'Client tracker';
        $sheets = $structure['sheets'] ?? [];

        $system = "You are writing a professional {$trackerType} for {$p->niche}. "
            ."Write complete, immediately usable content.{$voice}";

        $sections = [];

        // Usage guide first
        $p->update(['progress_note' => 'Writing usage guide...']);
        $usageUser = "Tracker type: {$trackerType}\nBusiness type: {$p->niche}\nTarget user: {$p->buyer_description}\n\n"
            ."Write a complete usage guide for this tracker. Cover:\n"
            ."1. How to start using this tracker today\n"
            ."2. How to customise columns for your situation\n"
            ."3. How to use the Dashboard\n"
            ."4. Weekly and monthly review process\n"
            ."5. How to back up your data\n"
            ."6. Frequently asked questions\n\n"
            .'Write in plain, practical language. This buyer is not technical.';
        $usageText = $claude->complete("You write usage guides for spreadsheet products.{$voice}", $usageUser);
        $sections[] = ['title' => 'How to Use This Tracker', 'body' => $usageText];

        // Dashboard specification
        $p->update(['progress_note' => 'Writing dashboard setup...']);
        $dashUser = "Tracker type: {$trackerType}\nBusiness type: {$p->niche}\n"
            .'Sheet structure: '.json_encode($sheets)."\n\n"
            ."Write the complete dashboard and tracker specification. Include:\n"
            ."1. Dashboard — what metrics appear and what each formula does in plain English\n"
            ."2. Example data (3 rows per sheet) — realistic, professional, believable\n"
            ."3. All dropdown options with values\n"
            ."4. Formula explanations in plain English\n"
            ."5. Colour coding guide\n\nNo preamble.";
        $dashText = $claude->complete($system, $dashUser);
        $sections[] = ['title' => 'Tracker Specification', 'body' => $dashText];

        $this->saveJsonContent($p, $sections);
    }

    private function writeNotionOs(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $opts = $p->brief_options ?? [];
        $workspaceSections = $structure['workspace_sections'] ?? [];
        $bizType = $p->niche;

        $system = "You are a Notion expert writing a complete Business OS specification for {$bizType}.{$voice}";

        $sections = [];

        // Full setup guide first (usage guide)
        $p->update(['progress_note' => 'Writing setup guide...']);
        $setupUser = "Business type: {$bizType}\nWorkspace structure: ".json_encode($structure)."\n\n"
            ."Write a complete setup guide for this Notion OS. Cover:\n"
            ."Section 1: Getting started (10 minutes) — create Notion account, duplicate workspace, first 5 things to do\n"
            ."Section 2: Understanding your workspace — what each section does, how databases connect\n"
            ."Section 3: Daily workflow — morning routine (5 min), update as you work, end of day review\n"
            ."Section 4: Weekly workflow — review process, plan the week, track progress\n"
            ."Section 5: Customising — add properties, create templates, add team members\n"
            ."Section 6: Pro tips — 10 Notion tips specific to this workspace\n\n"
            .'Write in clear friendly language. Zero technical jargon.';
        $setupText = $claude->complete("You write clear setup guides for non-technical users.{$voice}", $setupUser);
        $sections[] = ['title' => 'Setup Guide & How to Use This OS', 'body' => $setupText];

        // Each workspace section
        $total = count($workspaceSections);
        foreach ($workspaceSections as $i => $section) {
            $sectionName = $section['section_name'] ?? 'Section '.($i + 1);
            $p->update(['progress_note' => 'Writing section '.($i + 1)." of {$total}: {$sectionName}..."]);

            $sectionUser = "Business type: {$bizType}\nSection: {$sectionName}\n"
                .'Databases: '.json_encode($section['databases'] ?? [])."\n"
                ."Target user: {$p->buyer_description}\n\n"
                ."Write the complete content for this workspace section. Include:\n"
                ."1. Section overview — welcome message and how to use it\n"
                ."2. For each database: template page content with all fields and placeholder text\n"
                ."3. Example rows (3 per database) — realistic fictional examples\n"
                ."4. View configurations — what each view shows and filter/sort settings\n"
                ."5. Automation suggestions — simple automations for this section\n\nNo preamble.";

            $sectionText = $claude->complete($system, $sectionUser);
            $sections[] = ['title' => $sectionName, 'body' => $sectionText];
        }

        $this->saveJsonContent($p, $sections);
    }

    private function writeWebsiteCopyPack(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $opts = $p->brief_options ?? [];
        $businessName = $opts['business_name'] ?? $p->niche;
        $tone = $opts['tone'] ?? 'Friendly and warm';
        $differentiator = $opts['differentiator'] ?? '';
        $businessLocation = $opts['business_location'] ?? '';
        $pages = $structure['pages'] ?? ['Home', 'About', 'Contact & SEO'];
        $research = $p->research_output ?? [];

        $system = "You are a professional website copywriter writing for {$businessName}. "
            ."Tone: {$tone}. Write copy that converts browsers into buyers.{$voice}";

        $sections = [];

        // Usage guide first
        $p->update(['progress_note' => 'Writing usage guide...']);
        $usageText = "HOW TO USE YOUR WEBSITE COPY PACK\n\n"
            ."This pack contains all the words for your website. Here is how to use it in 5 steps:\n\n"
            ."Step 1: Read everything first (30 minutes)\n"
            ."Before pasting anything, read through the full pack. Get familiar with your copy. Note anything you want to adjust.\n\n"
            ."Step 2: Start with your home page (1 hour)\n"
            .'Open your website builder (WordPress, Wix, Squarespace, Webflow, Carrd — any works). '
            ."Go to your home page editor. Copy the hero headline → paste it in. Work section by section until your home page is done.\n\n"
            ."Step 3: Complete each page (30 mins per page)\n"
            ."Follow the same process for About, Services, and Contact. Each page section is clearly labelled.\n\n"
            ."Step 4: Add your meta copy (15 minutes)\n"
            ."Go to your website SEO settings. Copy each meta title and description. Paste into the matching page SEO fields.\n\n"
            ."Step 5: Update your social media bios (15 minutes)\n"
            ."Go to each platform. Copy the bio from the Social Bios section. Paste and save.\n\n"
            .'IMPORTANT: Personalise as you go — replace any [PLACEHOLDER] text with your real details. '
            .'Add your real testimonials where indicated. Your website should be fully written in one day.';
        $sections[] = ['title' => 'How to Use Your Website Copy Pack', 'body' => $usageText];

        // Write each page
        $total = count($pages);
        foreach ($pages as $i => $page) {
            $p->update(['progress_note' => 'Writing page '.($i + 1)." of {$total}: {$page}..."]);

            if (str_contains(strtolower($page), 'home')) {
                $pageUser = "Business name: {$businessName}\nWhat they do: {$p->niche}\n"
                    ."Ideal customer: {$p->buyer_description}\nDifferentiator: {$differentiator}\n"
                    ."Top problems solved: {$p->buyer_problem}\nTone: {$tone}\n"
                    .'Primary message: '.($research['primary_message'] ?? '')."\n\n"
                    .'Write the complete home page copy. Include: hero (headline, subheadline, 2 CTAs), '
                    .'social proof bar, problem section (3 pain points), solution section (3 benefits), '
                    ."how it works (3 steps), testimonials placeholder, final CTA.\n"
                    .'Format each section clearly with labels. No preamble.';
            } elseif (str_contains(strtolower($page), 'about')) {
                $pageUser = "Business/founder: {$businessName}\nWhat they do: {$p->niche}\n"
                    .'Values: '.($opts['core_values'] ?? 'not specified')."\nTone: {$tone}\n\n"
                    .'Write the complete about page copy. Include: opening hook, founder story (3-4 paragraphs), '
                    ."mission statement, 3 values with descriptions, why choose us section, closing CTA.\n"
                    .'Format each section clearly with labels. No preamble.';
            } elseif (str_contains(strtolower($page), 'contact') || str_contains(strtolower($page), 'seo')) {
                $pageUser = "Business name: {$businessName}\nWhat they do: {$p->niche}\n"
                    ."Location: {$businessLocation}\nTone: {$tone}\n\n"
                    .'Write: 1) Contact page copy (headline, intro, form labels, confirmation message) '
                    .'2) Meta copy for all pages (title + description under 60/160 chars each) '
                    .'3) Social media bios (Instagram 150 chars, LinkedIn headline 220 chars, LinkedIn about, '
                    .'Twitter 160 chars, Facebook 255 chars, Google Business 750 chars) '
                    ."4) 10 tagline options with recommended one.\n"
                    .'Format each section clearly. No preamble.';
            } else {
                // Service page
                $pageUser = "Business name: {$businessName}\nService name: {$page}\n"
                    ."Ideal customer: {$p->buyer_description}\nTone: {$tone}\n"
                    ."Problems solved: {$p->buyer_problem}\nDifferentiator: {$differentiator}\n\n"
                    .'Write the complete service page copy. Include: hero headline + subheadline, '
                    ."3-4 paragraph description, 5 what's included items with descriptions, "
                    ."who it's for (3 bullets), who it's not for, how it works (3 steps), "
                    ."3 FAQs, and CTA section.\nFormat each section clearly. No preamble.";
            }

            $pageText = $claude->complete($system, $pageUser);
            $sections[] = ['title' => $page, 'body' => $pageText];
        }

        $this->saveJsonContent($p, $sections);
    }

    private function writeBrandMessaging(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $opts = $p->brief_options ?? [];
        $businessName = $opts['business_name'] ?? $p->niche;
        $founderName = $opts['founder_name'] ?? $businessName;
        $differentiator = $opts['differentiator'] ?? '';
        $coreValues = $opts['core_values'] ?? '';
        $personality = $opts['personality_words'] ?? '';
        $avoidSounding = $opts['avoid_sounding_like'] ?? '';
        $pricePoint = $opts['price_point'] ?? '';
        $research = $p->research_output ?? [];
        $archetype = $research['brand_archetype'] ?? '';

        $system = "You are a senior brand strategist writing a complete brand messaging system for {$businessName}. "
            ."Be specific, distinctive — not generic brand-speak.{$voice}";

        $sections = [];

        $deliverables = [
            ['title' => 'How to Use Your Brand Messaging System', 'prompt' => 'usage'],
            ['title' => 'Brand Foundation', 'prompt' => 'foundation'],
            ['title' => 'Buyer Personas', 'prompt' => 'personas'],
            ['title' => 'Brand Voice Guide', 'prompt' => 'voice'],
            ['title' => 'Taglines & Pitches', 'prompt' => 'taglines'],
            ['title' => 'Platform Copy & Scripts', 'prompt' => 'platform'],
        ];

        $total = count($deliverables);
        foreach ($deliverables as $i => $deliverable) {
            $p->update(['progress_note' => 'Writing '.($i + 1)." of {$total}: {$deliverable['title']}..."]);

            switch ($deliverable['prompt']) {
                case 'usage':
                    $text = "HOW TO USE YOUR BRAND MESSAGING SYSTEM\n\n"
                        ."This document is your brand bible. Here is how to use every section:\n\n"
                        .'BRAND FOUNDATION — Read this section first. Memorise your mission and brand promise. '
                        ."Every decision you make should align with these.\n\n"
                        .'BUYER PERSONAS — Before writing any copy, open the persona section. Find the phrases '
                        ."they use to describe their problem. Use those exact words.\n\n"
                        .'BRAND VOICE GUIDE — Share this with anyone who writes for your brand. '
                        ."Your VA, your ghostwriter, your designer — they all need this.\n\n"
                        ."TAGLINES — Test 3 options with real customers. Which one gets the strongest reaction? Use that one.\n\n"
                        ."PLATFORM COPY — Copy and paste directly into each platform. Update your bios today.\n\n"
                        .'SCRIPTS — Practise the elevator pitch out loud until it sounds natural. '
                        ."Use the networking introduction at every event this month.\n\n"
                        .'QUARTERLY REVIEW — Every 90 days, review this document. Update anything that no longer feels accurate.';
                    break;

                case 'foundation':
                    $user = "Business: {$businessName}\nFounder: {$founderName}\nWhat they do: {$p->niche}\n"
                        ."Who they help: {$p->buyer_description}\nProblem solved: {$p->buyer_problem}\n"
                        ."Differentiator: {$differentiator}\nValues: {$coreValues}\n"
                        ."Brand archetype: {$archetype}\n\n"
                        ."Write the complete brand foundation. Include:\n"
                        ."1. Positioning statement (For [target] who [need], [brand] is the [category] that [benefit] because [reason])\n"
                        ."2. Mission statement (We [action] [who] so they can [outcome])\n"
                        ."3. Vision statement (A world where [change this brand creates])\n"
                        ."4. Brand promise (The one thing customers can always count on)\n"
                        ."5. Core values (each with definition, what it looks like in practice, what it is NOT)\n\n"
                        .'Be specific. No generic mission statements. No preamble.';
                    $text = $claude->complete($system, $user);
                    break;

                case 'personas':
                    $user = "Ideal customer: {$p->buyer_description}\nBusiness: {$p->niche}\nPrice point: {$pricePoint}\n\n"
                        ."Create 3 detailed buyer personas. For each persona include:\n"
                        ."- Name, age, location, job title, income\n"
                        ."- Day in life (vivid paragraph)\n"
                        ."- Goals and dream outcome\n"
                        ."- Pain points and emotional impact\n"
                        ."- What they tried before and why it failed\n"
                        ."- Where they are online and what content they consume\n"
                        ."- Buying triggers and blockers\n"
                        ."- Exact language phrases (5 phrases they actually use)\n"
                        ."- One persona quote that captures exactly how they feel\n\n"
                        .'Make them feel like real people. No preamble.';
                    $text = $claude->complete($system, $user);
                    break;

                case 'voice':
                    $user = "Business: {$businessName}\nPersonality: {$personality}\n"
                        ."What to avoid: {$avoidSounding}\nIdeal customer: {$p->buyer_description}\n"
                        ."Brand archetype: {$archetype}\nPrice point: {$pricePoint}\n\n"
                        ."Write the complete brand voice guide. Include:\n"
                        ."1. Voice overview (2-3 sentences)\n"
                        ."2. Tone attributes (4-5) — each with what it means, example ON, example OFF\n"
                        ."3. Writing rules (5-7 specific rules with before/after examples)\n"
                        ."4. Words to use (10) with why each fits the brand\n"
                        ."5. Words to avoid (10) with why each conflicts\n"
                        ."6. Sentence structure guidance\n"
                        ."7. How to open and close content\n"
                        ."8. Before/after rewrites (3 examples)\n\nNo preamble.";
                    $text = $claude->complete($system, $user);
                    break;

                case 'taglines':
                    $user = "Business: {$businessName}\nWhat they do: {$p->niche}\n"
                        ."Ideal customer: {$p->buyer_description}\nDifferentiator: {$differentiator}\n"
                        ."Brand promise: see brand foundation\n\n"
                        ."Generate:\n"
                        ."1. 10 tagline options (outcome / problem / question / statement types) with reasoning\n"
                        ."2. RECOMMENDED tagline with full explanation of why it wins\n"
                        ."3. Elevator pitches: 10-second, 30-second, 2-minute, written/email version\n"
                        ."4. Key messages: 1 primary + 3 supporting\n"
                        ."5. Objection responses (5 common objections with honest, conversational responses)\n\nNo preamble.";
                    $text = $claude->complete($system, $user);
                    break;

                case 'platform':
                    $user = "Business: {$businessName}\nFounder: {$founderName}\nWhat they do: {$p->niche}\n"
                        ."Tone: see voice guide\nIdeal customer: {$p->buyer_description}\n\n"
                        ."Write:\n"
                        .'1. Social media bios: Instagram (150 chars), LinkedIn headline (220 chars), '
                        .'LinkedIn About (full section), Twitter (160 chars), Facebook (255 chars), '
                        ."TikTok (80 chars), Pinterest (160 chars), Google Business (750 chars)\n"
                        .'2. Scripts: networking introduction, discovery call opening, proposal intro, '
                        ."testimonial request, referral request, cold outreach template, partnership pitch\n"
                        .'3. Brand application guide: before-publishing checklist (5 items), '
                        ."how to brief designers, how to onboard team/VA, quarterly review process\n\nNo preamble.";
                    $text = $claude->complete($system, $user);
                    break;

                default:
                    $text = '';
            }

            $sections[] = ['title' => $deliverable['title'], 'body' => $text];
        }

        $this->saveJsonContent($p, $sections);
    }

    private function writeSalesFunnel(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $opts = $p->brief_options ?? [];
        $pricePoint = $opts['price_point'] ?? '';
        $tone = $opts['tone'] ?? 'Friendly';
        $triedBefore = $opts['tried_before'] ?? '';
        $differentiator = $opts['differentiator'] ?? '';
        $needsUpsell = $opts['needs_upsell'] ?? 'No';
        $needsWebinar = $opts['needs_webinar'] ?? 'No';
        $research = $p->research_output ?? [];
        $emailCount = (int) ($structure['email_count'] ?? 7);
        $leadMagnetType = $structure['lead_magnet_type'] ?? 'Checklist';
        $awarenessLevel = $structure['awareness_level'] ?? 'Problem aware';

        $system = "You are an expert direct response copywriter writing a complete sales funnel for: {$p->niche}. "
            ."Price point: {$pricePoint}. Tone: {$tone}.{$voice}";

        $sections = [];

        // Usage guide first
        $usageText = "HOW TO USE YOUR SALES FUNNEL COPY PACK\n\n"
            ."This pack contains all the words for your entire sales funnel. Here is the order to use it:\n\n"
            ."Step 1: Create your lead magnet (1-2 hours)\n"
            ."Use the Lead Magnet section to create your free offer. This attracts people into your funnel.\n\n"
            ."Step 2: Build your landing page (2-3 hours)\n"
            ."Copy the landing page section. Paste into your page builder:\n"
            ."Carrd (free) / ClickFunnels / ConvertKit / Kajabi / Systeme.io\n\n"
            ."Step 3: Set up your email sequence (2-3 hours)\n"
            ."Copy each email in order. Paste into your email tool:\n"
            ."Mailchimp (free) / ConvertKit / ActiveCampaign / MailerLite\n"
            ."Set up as an automation triggered by signup.\n\n"
            ."Step 4: Build your sales page (3-4 hours)\n"
            ."Copy the sales page section. Build in your page builder. Add your real testimonials where indicated.\n\n"
            ."Step 5: Set up post-purchase flow (1 hour)\n"
            ."Copy the confirmation email. Set up as a purchase automation.\n\n"
            .'Your funnel should be live within one day. Every word is written. You just paste and publish.';
        $sections[] = ['title' => 'How to Use Your Sales Funnel Copy Pack', 'body' => $usageText];

        $components = [
            ['title' => "Lead Magnet ({$leadMagnetType})", 'key' => 'lead_magnet'],
            ['title' => 'Landing Page', 'key' => 'landing_page'],
            ['title' => "Email Sequence ({$emailCount} emails)", 'key' => 'emails'],
            ['title' => 'Sales Page', 'key' => 'sales_page'],
        ];

        if ($needsUpsell === 'Yes') {
            $components[] = ['title' => 'Upsell Page', 'key' => 'upsell'];
        }

        $total = count($components);
        foreach ($components as $i => $component) {
            $p->update(['progress_note' => 'Writing '.($i + 1)." of {$total}: {$component['title']}..."]);

            switch ($component['key']) {
                case 'lead_magnet':
                    $user = "Offer: {$p->niche}\nBuyer: {$p->buyer_description}\nProblem: {$p->buyer_problem}\n"
                        ."Lead magnet type: {$leadMagnetType}\nTone: {$tone}\n\n"
                        ."Write the complete lead magnet copy. Include:\n"
                        ."1. Title (irresistible — specific outcome)\n2. Subtitle (what it does in one sentence)\n"
                        ."3. Description (2-3 sentences — what they get and why it matters)\n"
                        ."4. 5 specific what's inside bullets\n5. CTA button text\n\nNo preamble.";
                    break;

                case 'landing_page':
                    $user = "Offer: {$p->niche}\nBuyer: {$p->buyer_description}\nProblem: {$p->buyer_problem}\n"
                        ."Awareness level: {$awarenessLevel}\nTone: {$tone}\n\n"
                        ."Write the complete landing page copy. Include:\n"
                        ."1. Headline (outcome-focused, under 12 words)\n2. Subheadline (who it is for + what they get)\n"
                        ."3. Social proof line (short credibility line)\n4. Body copy (3-4 paragraphs: problem → agitation → solution)\n"
                        ."5. 3 what's included bullets with descriptions\n6. Form headline and CTA button text\n"
                        ."7. Privacy note\n8. Thank you page (headline + what happens next)\n\nNo preamble.";
                    break;

                case 'emails':
                    $emailGoals = [
                        1 => 'Deliver lead magnet. Welcome. Set expectations.',
                        2 => 'Quick win. Help them get a result right now.',
                        3 => 'Story. Build connection and trust.',
                        4 => 'Teach something valuable. Mention the offer casually.',
                        5 => 'Introduce the paid offer properly.',
                        6 => 'Social proof and objection handling.',
                        7 => 'Last chance. Clear call to action.',
                    ];

                    $allEmails = '';
                    $prevSummary = '';
                    for ($e = 1; $e <= $emailCount; $e++) {
                        $goal = $emailGoals[$e] ?? 'Continue nurturing and building trust.';
                        $timing = match ($e) {
                            1 => 'Immediately', 2 => 'Day 1', 3 => 'Day 3',
                            4 => 'Day 5', 5 => 'Day 7', 6 => 'Day 9',
                            default => 'Day '.($e * 2 - 1),
                        };

                        $emailUser = "Offer: {$p->niche}\nPrice: {$pricePoint}\nBuyer: {$p->buyer_description}\n"
                            ."Tone: {$tone}\nEmail {$e} of {$emailCount}\nSend timing: {$timing}\n"
                            ."Email goal: {$goal}\nPrevious emails covered: {$prevSummary}\n\n"
                            ."Write Email {$e}. Include:\n"
                            ."- Send timing: {$timing}\n- Subject line (specific, under 50 chars)\n"
                            ."- Preview text (under 90 chars)\n- Full email body (real sentences, one clear job)\n"
                            ."- CTA link text\n- Optional P.S. line\n\nNo preamble.";

                        $emailText = $claude->complete($system, $emailUser);
                        $allEmails .= "EMAIL {$e} OF {$emailCount} — {$timing}\n".str_repeat('-', 40)."\n{$emailText}\n\n";
                        $prevSummary .= "Email {$e}: {$goal} | ";
                    }
                    $text = $allEmails;
                    $sections[] = ['title' => "Email Sequence ({$emailCount} emails)", 'body' => $text];

                    continue 2;

                case 'sales_page':
                    $objections = json_encode($research['top_objections'] ?? []);
                    $user = "Offer: {$p->niche}\nPrice: {$pricePoint}\nBuyer: {$p->buyer_description}\n"
                        ."Problem: {$p->buyer_problem}\nTried before: {$triedBefore}\n"
                        ."Differentiator: {$differentiator}\nTop objections: {$objections}\nTone: {$tone}\n\n"
                        ."Write the complete long-form sales page. Include:\n"
                        ."1. Headline (biggest promise)\n2. Subheadline (who this is for + transformation)\n"
                        ."3. Opening story (3-5 paragraphs — the reader's story told back to them)\n"
                        ."4. Problem section (headline + 3-4 paragraphs agitating the problem)\n"
                        ."5. Solution introduction (headline + 2-3 paragraphs)\n"
                        ."6. What's included (5 deliverables with values)\n"
                        ."7. Who it's for / who it's not for\n"
                        ."8. Testimonials placeholder\n"
                        ."9. Objection handling (5 responses)\n"
                        ."10. Guarantee / risk reversal\n"
                        ."11. Pricing section with context\n"
                        ."12. Final CTA\n"
                        ."13. Confirmation email (subject + full body)\n\nNo preamble.";
                    break;

                case 'upsell':
                    $user = "Main offer: {$p->niche}\nPrice: {$pricePoint}\nBuyer: {$p->buyer_description}\nTone: {$tone}\n\n"
                        ."Write a complete upsell page for a complementary higher-ticket offer. Include:\n"
                        ."1. Congratulations headline (they just bought)\n2. Upsell offer name and description\n"
                        ."3. Why they need this now (3 reasons)\n4. What's included\n"
                        ."5. Price and value justification\n6. One-time offer urgency\n7. Yes/No CTA buttons\n\nNo preamble.";
                    break;

                default:
                    $user = "Write content for: {$component['title']}\nOffer: {$p->niche}";
            }

            $text = $claude->complete($system, $user);
            $sections[] = ['title' => $component['title'], 'body' => $text];
        }

        $this->saveJsonContent($p, $sections);
    }

    private function writeNicheResearch(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $opts = $p->brief_options ?? [];
        $geography = $opts['geography'] ?? 'Nigeria';
        $specificQs = $opts['specific_questions'] ?? '';
        $competitors = $opts['competitors'] ?? '';
        $sections_list = $structure['sections'] ?? [
            'Executive Summary', 'Market Overview', 'Buyer Analysis',
            'Competitor Landscape', 'Market Gaps & Opportunities',
            'Pricing & Revenue Analysis', 'Marketing & Distribution',
            'Trend Analysis', '90-Day Action Plan',
        ];

        $system = "You are a senior market research analyst writing a niche research report on: {$p->niche}. "
            ."Geographic focus: {$geography}. Write with the precision of someone who has analysed 50+ markets.{$voice}";

        $sections = [];
        $previousSections = [];
        $total = count($sections_list);

        // Usage guide first
        $p->update(['progress_note' => 'Writing usage guide...']);
        $usageUser = "Report: {$p->niche} Market Research Report\nDecision: {$p->buyer_problem}\n\n"
            ."Write a usage guide for this report. Cover:\n"
            ."1. What this report contains and what it does\n"
            ."2. How to read it (start with Executive Summary)\n"
            ."3. How to use the findings to make the decision described\n"
            ."4. How to share it with stakeholders\n"
            ."5. When to update the research (every 6-12 months)\n\nBe concise and direct.";
        $usageText = $claude->complete("You write usage guides for business research reports.{$voice}", $usageUser);
        $sections[] = ['title' => 'How to Use This Report', 'body' => $usageText];

        foreach ($sections_list as $i => $sectionTitle) {
            $p->update(['progress_note' => 'Writing section '.($i + 1)." of {$total}: {$sectionTitle}..."]);

            $prevContext = $previousSections ? 'Previous sections summary: '.implode(' | ', array_slice($previousSections, -3)) : '';
            $competitorNote = $competitors ? "Specific competitors to include: {$competitors}." : '';
            $specificNote = $specificQs ? "Specific questions to answer: {$specificQs}." : '';

            $sectionUser = "Niche: {$p->niche}\nGeographic focus: {$geography}\n"
                ."Target user: {$p->buyer_description}\nDecision to inform: {$p->buyer_problem}\n"
                ."{$prevContext}\n{$competitorNote}\n{$specificNote}\n\n"
                ."Write the complete '{$sectionTitle}' section of this research report.\n"
                ."Be specific, data-informed, and actionable. Name real patterns and opportunities.\n"
                ."This section should be 400-600 words of dense, useful analysis.\n"
                ."End with a '⚡ Key Insight' callout summarising the most important finding.\nNo preamble.";

            $sectionText = $claude->complete($system, $sectionUser);
            $sections[] = ['title' => $sectionTitle, 'body' => $sectionText];
            $previousSections[] = $sectionTitle;
        }

        $this->saveJsonContent($p, $sections);
    }

    private function writeBuyerPersona(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $opts = $p->brief_options ?? [];
        $personaCount = (int) ($opts['persona_count'] ?? 3);
        $pricePoint = $opts['price_point'] ?? '';
        $geography = $opts['geography'] ?? 'Nigeria';
        $personas = $structure['personas'] ?? array_map(fn ($i) => ['number' => $i, 'title' => "Persona {$i}", 'archetype' => '', 'focus' => ''], range(1, $personaCount));

        $system = "You are a consumer psychologist writing deeply researched buyer personas for: {$p->niche}. "
            ."Make each persona feel like a real person the buyer already knows.{$voice}";

        $sections = [];

        // Usage guide first
        $p->update(['progress_note' => 'Writing usage guide...']);
        $usageText = "HOW TO USE YOUR BUYER PERSONA PACK\n\n"
            ."Your personas are research-backed profiles of your ideal customers. Here is exactly how to use them:\n\n"
            ."WRITING COPY\n"
            ."Before writing any headline, email, or social post: Open the 'Exact Language' section of your primary persona. "
            .'Find the phrases they use to describe their problem. Use those exact words in your headline. '
            ."Your copy will immediately feel like it was written for them.\n\n"
            ."CREATING CONTENT\n"
            ."Look at the 'Online Behaviour' section. See what platforms they use and what content they consume. "
            ."Create content in those formats on those platforms. Speak to their pain points directly.\n\n"
            ."BUILDING PRODUCTS\n"
            ."Look at 'Previous Attempts' and 'Why It Failed.' Your product should solve what other solutions missed. "
            ."This is your key differentiator — name it explicitly.\n\n"
            ."HANDLING OBJECTIONS\n"
            ."Look at 'Buying Blockers.' Address each blocker directly in your sales page or conversations. "
            ."Understanding the root cause helps you respond with empathy.\n\n"
            ."BRIEFING YOUR TEAM\n"
            .'Share this document with anyone who writes, designs, or communicates on behalf of your business. '
            ."Tell them: write for [Persona Name]. Every piece of content should speak to this person.\n\n"
            ."QUARTERLY REVIEW\n"
            .'Every 90 days, talk to 3-5 real customers. Ask them about their goals, frustrations, and why they chose you. '
            .'Update the personas based on what you learn.';
        $sections[] = ['title' => 'How to Use Your Buyer Persona Pack', 'body' => $usageText];

        // Write each persona
        $allPersonasJson = [];
        foreach ($personas as $i => $personaInfo) {
            $personaNum = $personaInfo['number'] ?? ($i + 1);
            $archetype = $personaInfo['archetype'] ?? "Persona {$personaNum}";
            $p->update(['progress_note' => "Writing persona {$personaNum} of {$personaCount}: {$archetype}..."]);

            $personaFocus = $personaInfo['focus'] ?? '';
            $personaUser = "Business: {$p->niche}\nOffer: {$p->buyer_problem}\n"
                ."Ideal customer: {$p->buyer_description}\nPrice point: {$pricePoint}\n"
                ."Geographic focus: {$geography}\nPersona {$personaNum} of {$personaCount}\n"
                ."Archetype hint: {$archetype}\nFocus: {$personaFocus}\n\n"
                ."Create a complete, deeply researched buyer persona. Include:\n"
                ."1. Persona name and title (e.g. The Overwhelmed Freelancer)\n"
                ."2. Demographics (age, location, job title, income, family)\n"
                .'3. Day in life — vivid 3-4 paragraph narrative of their typical Tuesday. '
                ."Real details. Real frustrations. Make the buyer reading this say: that is exactly me.\n"
                ."4. Professional life (biggest work challenge, how they measure success, tools, skills)\n"
                ."5. Goals (primary, secondary, dream outcome, immediate need)\n"
                ."6. Pain points (primary pain, emotional impact, how stress shows up)\n"
                ."7. Previous attempts — what they tried and why it failed\n"
                ."8. Online behaviour (platforms, search behaviour, trusted sources)\n"
                ."9. Buying psychology (decision style, buying triggers with examples, blockers with root causes, trust builders)\n"
                ."10. Exact language — 5 phrases they use to describe their problem, 3 for their goal\n"
                ."11. Persona quote (1 quote, first person, 2-3 sentences — exactly how they feel)\n"
                ."12. How to market to them (best channel, message, format, CTA, what to avoid)\n\n"
                .'Be specific. This should feel like a real person. No preamble.';

            $personaText = $claude->complete($system, $personaUser);
            $sections[] = ['title' => "Persona {$personaNum}: {$archetype}", 'body' => $personaText];
            $allPersonasJson[] = $archetype.': '.$personaText;
        }

        // Personas summary / usage guide
        $p->update(['progress_note' => 'Writing persona comparison guide...']);
        $summaryUser = 'All personas: '.implode("\n\n---\n\n", array_slice($allPersonasJson, 0, 3))."\n\n"
            ."Business: {$p->niche}\nOffer: {$p->buyer_problem}\n\n"
            ."Write a personas comparison guide. Include:\n"
            ."1. Overview — how these personas relate to each other\n"
            ."2. Primary persona recommendation — which to focus on first and why\n"
            ."3. Comparison table (3-5 factors across all personas)\n"
            ."4. Research methods — how to validate these with real customers\n\nNo preamble.";
        $summaryText = $claude->complete($system, $summaryUser);
        $sections[] = ['title' => 'Persona Comparison & Application Guide', 'body' => $summaryText];

        $this->saveJsonContent($p, $sections);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Save sections as JSON-encoded string in content_output.
     * Sections format: [{"title":"...", "body":"..."}]
     * The usage guide section (index 0) is always first per spec.
     */
    private function saveJsonContent(DigitalProduct $p, array $sections): void
    {
        $p->update([
            'content_output' => json_encode(['sections' => $sections], JSON_UNESCAPED_UNICODE),
            'current_stage' => 4,
            'status' => 'draft',
            'progress_note' => null,
        ]);
    }

    private function header(DigitalProduct $p): string
    {
        return "PRODUCT: {$p->product_title}\nGENERATED: ".now()->toDateTimeString()."\n".str_repeat('=', 60)."\n\n";
    }

    private function voiceSuffix(DigitalProduct $product): string
    {
        $profile = $product->voiceProfile;
        if (! $profile || ! $profile->extracted_style) {
            return '';
        }

        return "\n\n--- AUTHOR VOICE PROFILE: {$profile->name} ---\n"
            .$profile->extracted_style
            ."\n--- END VOICE PROFILE ---\n";
    }
}
