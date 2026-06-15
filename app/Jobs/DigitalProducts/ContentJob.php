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
        $tone = $opts['tone'] ?? 'Professional';
        $pages = $structure['pages'] ?? ['Home', 'About', 'Contact & SEO'];
        $research = $p->research_output ?? [];
        $placeholders = $structure['placeholder_guide'] ?? [
            'business_name' => '[YOUR BUSINESS NAME]',
            'service' => '[YOUR SERVICE NAME]',
            'tagline' => '[YOUR TAGLINE]',
            'cta' => '[YOUR CTA TEXT]',
            'testimonial' => '[ADD YOUR TESTIMONIAL HERE]',
        ];

        $system = 'You are a professional website copywriter creating a niche website copy template pack for '.$p->niche.'. '
            .'This is a template bought by many people in this niche — NOT written for one specific business. '
            .'Use '.($placeholders['business_name'] ?? '[YOUR BUSINESS NAME]').' wherever the business name goes. '
            .'Use '.($placeholders['service'] ?? '[YOUR SERVICE NAME]').' wherever a service name goes. '
            .'Use '.($placeholders['testimonial'] ?? '[ADD YOUR TESTIMONIAL HERE]').' for testimonials. '
            .'Every [BRACKET] is a clear instruction to the buyer about what to fill in. '
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

            $nicheContext = "Niche: {$p->niche}\nTypical buyer: {$p->buyer_description}\n"
                ."Their biggest problem: {$p->buyer_problem}\nTone: {$tone}\n"
                .'Primary message: '.($research['primary_message'] ?? '')."\n"
                ."REMINDER: Use [YOUR BUSINESS NAME], [YOUR SERVICE NAME], [YOUR TAGLINE], [YOUR CTA TEXT], [ADD YOUR TESTIMONIAL HERE] as placeholders.\n\n";

            if (str_contains(strtolower($page), 'home')) {
                $pageUser = $nicheContext
                    .'Write the complete home page copy for a '.$p->niche.' professional. '
                    .'Include: hero (headline with [YOUR BUSINESS NAME], subheadline, 2 CTAs using [YOUR CTA TEXT]), '
                    .'social proof bar (3 stats with [YOUR NUMBER] placeholders), '
                    .'problem section (3 pain points specific to '.$p->niche.'), '
                    .'solution section (3 benefits), how it works (3 steps), '
                    .'testimonials section ([ADD YOUR TESTIMONIAL HERE] × 3), final CTA. '
                    .'Format each section clearly with labels. No preamble.';
            } elseif (str_contains(strtolower($page), 'about')) {
                $pageUser = $nicheContext
                    .'Write the complete about page copy for a '.$p->niche.' professional. '
                    .'Use [YOUR NAME] for the founder name. '
                    .'Include: opening hook (speaks to the niche), story structure (3-4 paragraphs using [YOUR NAME] and [YOUR SPECIFIC STORY]), '
                    .'mission statement template with [YOUR MISSION], 3 core values with descriptions, '
                    .'why choose us section, closing CTA. '
                    .'Format each section clearly with labels. No preamble.';
            } elseif (str_contains(strtolower($page), 'contact') || str_contains(strtolower($page), 'seo')) {
                $pageUser = $nicheContext
                    .'Write: 1) Contact page copy for a '.$p->niche.' professional '
                    .'(headline, intro using [YOUR BUSINESS NAME], form labels, confirmation message) '
                    .'2) Meta copy templates for all pages (title + description under 60/160 chars — use [YOUR BUSINESS NAME] and [YOUR SERVICE NAME]) '
                    .'3) Social media bio templates for all platforms (Instagram 150 chars, LinkedIn headline 220 chars, '
                    .'LinkedIn about section, Twitter 160 chars, Facebook 255 chars, Google Business 750 chars — '
                    .'use [YOUR NAME] and [YOUR BUSINESS NAME] throughout) '
                    .'4) 10 tagline options for this niche with recommended one. '
                    .'Format each section clearly. No preamble.';
            } else {
                // Service page
                $pageUser = $nicheContext
                    ."Service type: {$page}\n\n"
                    .'Write the complete service page copy for a '.$p->niche.' offering '.$page.'. '
                    .'Use [YOUR SERVICE NAME] for the service name, [YOUR PRICE] for pricing, [YOUR CTA TEXT] for buttons. '
                    .'Include: hero headline + subheadline, '
                    ."3-4 paragraph description, 5 what's included items (use [YOUR DELIVERABLE] where needed), "
                    ."who it's for (3 bullets), who it's not for, how it works (3 steps), "
                    ."3 FAQs common in {$p->niche}, CTA section. "
                    .'Format each section clearly. No preamble.';
            }

            $pageText = $claude->complete($system, $pageUser);
            $sections[] = ['title' => $page, 'body' => $pageText];
        }

        $this->saveJsonContent($p, $sections);
    }

    private function writeBrandMessaging(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice): void
    {
        $opts = $p->brief_options ?? [];
        $tone = $opts['tone'] ?? 'Professional';
        $differentiator = $opts['differentiator'] ?? '';
        $pricePoint = $opts['price_point'] ?? '';
        $research = $p->research_output ?? [];
        $archetype = $research['brand_archetype'] ?? '';
        $placeholders = $structure['placeholder_guide'] ?? [
            'name' => '[YOUR NAME]',
            'business' => '[YOUR BUSINESS NAME]',
            'example' => '[YOUR SPECIFIC EXAMPLE]',
        ];

        $system = 'You are a senior brand strategist creating a niche brand messaging template system for '.$p->niche.'. '
            .'This is bought by many practitioners in this niche — NOT written for one specific person. '
            .'Use '.($placeholders['name'] ?? '[YOUR NAME]').' wherever a personal name goes. '
            .'Use '.($placeholders['business'] ?? '[YOUR BUSINESS NAME]').' wherever a business name goes. '
            .'Use '.($placeholders['example'] ?? '[YOUR SPECIFIC EXAMPLE]').' for personal examples. '
            ."Every [BRACKET] is a clear instruction to the buyer about what to personalise. Tone: {$tone}.{$voice}";

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
                    $user = "Niche: {$p->niche}\nTypical buyer: {$p->buyer_description}\n"
                        ."Their problem: {$p->buyer_problem}\nDifferentiator for top practitioners: {$differentiator}\n"
                        ."Brand archetype for this niche: {$archetype}\nTone: {$tone}\n\n"
                        ."Write the complete brand foundation TEMPLATE for {$p->niche} professionals.\n"
                        ."Use [YOUR NAME] and [YOUR BUSINESS NAME] throughout.\n"
                        ."Include:\n"
                        ."1. Positioning statement template — fill-in-the-blank with [BRACKETS]\n"
                        ."2. Mission statement template — fill-in-the-blank with [BRACKETS]\n"
                        ."3. Vision statement template — fill-in-the-blank with [BRACKETS]\n"
                        ."4. Brand promise — write 3 options for {$p->niche} practitioners to choose from\n"
                        ."5. Core values — 5 values common in great {$p->niche} brands, each with definition and what it looks like in practice\n\n"
                        .'Be specific to the niche. Avoid generic statements. '
                        .'Every item must feel written for a '.$p->niche.' professional. No preamble.';
                    $text = $claude->complete($system, $user);
                    break;

                case 'personas':
                    $user = "Niche: {$p->niche}\nIdeal customer: {$p->buyer_description}\n"
                        ."Their problem: {$p->buyer_problem}\nTypical price point: {$pricePoint}\n\n"
                        ."Create 3 detailed buyer personas for {$p->niche} professionals to target. "
                        .'These are the typical buyers of services/products in this niche. '
                        .'For each persona include: name, age, location, job title, income, '
                        .'day in life (vivid paragraph), goals and dream outcome, pain points, '
                        .'what they tried before, where they are online, buying triggers and blockers, '
                        ."5 exact phrases they use, one persona quote.\n\n"
                        ."Make them feel like real recognisable people in {$p->niche}. No preamble.";
                    $text = $claude->complete($system, $user);
                    break;

                case 'voice':
                    $user = "Niche: {$p->niche}\nIdeal customer: {$p->buyer_description}\n"
                        ."Brand archetype for this niche: {$archetype}\nTone: {$tone}\n"
                        ."Typical price point: {$pricePoint}\n\n"
                        ."Write a brand voice guide template for {$p->niche} professionals. "
                        ."Use [YOUR BUSINESS NAME] and [YOUR SPECIFIC EXAMPLE] throughout. Include:\n"
                        ."1. Voice overview for this niche (2-3 sentences)\n"
                        ."2. Tone attributes (4-5) suited to {$p->niche} — each with example ON and example OFF\n"
                        ."3. Writing rules specific to {$p->niche} (5-7 with before/after examples)\n"
                        ."4. Words that work in {$p->niche} (10) — with why each works\n"
                        ."5. Words to avoid in {$p->niche} (10) — with why they hurt\n"
                        ."6. How to open and close content in this niche\n"
                        ."7. Before/after rewrites (3 examples — generic vs niche-specific)\n\nNo preamble.";
                    $text = $claude->complete($system, $user);
                    break;

                case 'taglines':
                    $user = "Niche: {$p->niche}\nIdeal customer: {$p->buyer_description}\n"
                        ."What makes the best practitioners different: {$differentiator}\nTone: {$tone}\n\n"
                        ."Generate a tagline and messaging toolkit for {$p->niche} professionals. Include:\n"
                        .'1. 10 tagline options (outcome / problem / question / statement types) with reasoning — '
                        ."each should work for any {$p->niche} professional who fills in [YOUR BUSINESS NAME]\n"
                        ."2. RECOMMENDED tagline with full explanation of why it wins for this niche\n"
                        .'3. Elevator pitch templates: 10-second, 30-second, 2-minute, written/email — '
                        ."use [YOUR NAME], [YOUR SPECIFIC RESULT] as placeholders\n"
                        ."4. Key messages: 1 primary + 3 supporting (specific to {$p->niche})\n"
                        ."5. Objection responses (5 common objections in {$p->niche} with honest, conversational responses)\n\nNo preamble.";
                    $text = $claude->complete($system, $user);
                    break;

                case 'platform':
                    $user = "Niche: {$p->niche}\nIdeal customer: {$p->buyer_description}\nTone: {$tone}\n\n"
                        ."Write platform copy and script templates for {$p->niche} professionals. "
                        ."Use [YOUR NAME] and [YOUR BUSINESS NAME] throughout. Include:\n"
                        .'1. Social media bio templates: Instagram (150 chars), LinkedIn headline (220 chars), '
                        .'LinkedIn About (full section), Twitter (160 chars), Facebook (255 chars), '
                        ."TikTok (80 chars), Pinterest (160 chars), Google Business (750 chars)\n"
                        ."2. Script templates for {$p->niche}: networking introduction, discovery call opening, "
                        .'proposal intro, testimonial request, referral request, cold outreach, partnership pitch — '
                        ."use [YOUR SPECIFIC RESULT] and [YOUR SPECIFIC OFFER] as placeholders\n"
                        .'3. Brand application guide: before-publishing checklist (5 items), '
                        ."how to brief designers on your brand, how to onboard a VA or team member\n\nNo preamble.";
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

        $placeholders = $structure['placeholder_guide'] ?? [
            'offer' => '[YOUR OFFER NAME]',
            'price' => '[YOUR PRICE]',
            'bonus' => '[YOUR BONUS NAME]',
            'name' => '[YOUR NAME]',
        ];

        $system = 'You are an expert direct response copywriter creating a niche sales funnel template pack for '.$p->niche.'. '
            .'This is bought by many people in this niche — NOT written for one specific offer. '
            .'Use '.($placeholders['offer'] ?? '[YOUR OFFER NAME]').' wherever the offer name goes. '
            .'Use '.($placeholders['price'] ?? '[YOUR PRICE]').' wherever pricing goes. '
            .'Use '.($placeholders['bonus'] ?? '[YOUR BONUS NAME]').' wherever bonus names go. '
            .'Use '.($placeholders['name'] ?? '[YOUR NAME]').' wherever a personal name goes. '
            ."Every [BRACKET] is a clear instruction to the buyer about what to personalise. Tone: {$tone}.{$voice}";

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
                    $user = "Niche: {$p->niche}\nBuyer: {$p->buyer_description}\nProblem: {$p->buyer_problem}\n"
                        ."Lead magnet type: {$leadMagnetType}\nTone: {$tone}\n\n"
                        ."Write a lead magnet copy template for a {$p->niche} offer. "
                        ."Use [YOUR OFFER NAME] as the product name. Include:\n"
                        ."1. Title template (use [YOUR RESULT] for the specific outcome)\n"
                        ."2. Subtitle template\n3. Description (2-3 sentences)\n"
                        ."4. 5 what's inside bullets (specific to {$p->niche})\n"
                        ."5. CTA button text options\n\nNo preamble.";
                    break;

                case 'landing_page':
                    $user = "Niche: {$p->niche}\nBuyer: {$p->buyer_description}\nProblem: {$p->buyer_problem}\n"
                        ."Awareness level: {$awarenessLevel}\nTone: {$tone}\n\n"
                        ."Write a landing page copy template for a {$p->niche} offer. "
                        ."Use [YOUR OFFER NAME], [YOUR NAME], [YOUR RESULT] as placeholders. Include:\n"
                        ."1. Headline template (outcome-focused, under 12 words)\n"
                        ."2. Subheadline template\n3. Social proof line template (use [YOUR NUMBER])\n"
                        ."4. Body copy (3-4 paragraphs specific to {$p->niche}: problem → agitation → solution)\n"
                        ."5. 3 what's included bullets\n6. Form headline and CTA options\n"
                        ."7. Privacy note\n8. Thank you page template\n\nNo preamble.";
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

                        $emailUser = "Niche: {$p->niche}\nOffer price range: {$pricePoint}\nBuyer: {$p->buyer_description}\n"
                            ."Tone: {$tone}\nEmail {$e} of {$emailCount}\nSend timing: {$timing}\n"
                            ."Email goal: {$goal}\nPrevious emails covered: {$prevSummary}\n\n"
                            ."Write Email {$e} as a template for {$p->niche} practitioners. "
                            ."Use [YOUR NAME], [YOUR OFFER NAME], [YOUR SPECIFIC STORY] as placeholders. Include:\n"
                            ."- Send timing: {$timing}\n- Subject line template (under 50 chars)\n"
                            ."- Preview text template (under 90 chars)\n"
                            ."- Full email body (specific to {$p->niche}, one clear job, use [BRACKETS] for personal details)\n"
                            ."- CTA text\n- Optional P.S.\n\nNo preamble.";

                        $emailText = $claude->complete($system, $emailUser);
                        $allEmails .= "EMAIL {$e} OF {$emailCount} — {$timing}\n".str_repeat('-', 40)."\n{$emailText}\n\n";
                        $prevSummary .= "Email {$e}: {$goal} | ";
                    }
                    $text = $allEmails;
                    $sections[] = ['title' => "Email Sequence ({$emailCount} emails)", 'body' => $text];

                    continue 2;

                case 'sales_page':
                    $objections = json_encode($research['top_objections'] ?? []);
                    $user = "Niche: {$p->niche}\nOffer price range: {$pricePoint}\nBuyer: {$p->buyer_description}\n"
                        ."Their problem: {$p->buyer_problem}\nWhat they try first: {$triedBefore}\n"
                        ."What makes great offers different: {$differentiator}\nTop objections: {$objections}\nTone: {$tone}\n\n"
                        ."Write a complete long-form sales page TEMPLATE for {$p->niche} practitioners. "
                        ."Use [YOUR OFFER NAME], [YOUR PRICE], [YOUR BONUS NAME], [YOUR NAME], [ADD YOUR TESTIMONIAL HERE] throughout. Include:\n"
                        ."1. Headline template (biggest promise for {$p->niche})\n"
                        ."2. Subheadline template\n3. Opening story structure (use [YOUR STORY] — guide the buyer)\n"
                        ."4. Problem section (3-4 paragraphs specific to {$p->niche})\n"
                        ."5. Solution introduction ([YOUR OFFER NAME])\n"
                        ."6. What's included (5 deliverables — use [YOUR DELIVERABLE] where needed)\n"
                        ."7. Who it's for / who it's not for (specific to {$p->niche})\n"
                        ."8. [ADD YOUR TESTIMONIAL HERE] × 3 placeholders with guidance on what to put\n"
                        ."9. Objection handling (5 responses to common {$p->niche} objections)\n"
                        ."10. Guarantee template\n11. Pricing section ([YOUR PRICE])\n"
                        ."12. Final CTA\n13. Confirmation email template\n\nNo preamble.";
                    break;

                case 'upsell':
                    $user = "Niche: {$p->niche}\nOffer price range: {$pricePoint}\nBuyer: {$p->buyer_description}\nTone: {$tone}\n\n"
                        ."Write an upsell page TEMPLATE for a {$p->niche} complementary offer. "
                        ."Use [YOUR UPSELL OFFER NAME], [YOUR UPSELL PRICE] as placeholders. Include:\n"
                        ."1. Congratulations headline template\n2. Upsell offer description template\n"
                        ."3. 3 reasons they need this now (specific to {$p->niche})\n4. What's included template\n"
                        ."5. Price justification\n6. One-time offer urgency line\n7. Yes/No CTA buttons\n\nNo preamble.";
                    break;

                default:
                    $user = "Write content for: {$component['title']}\nNiche: {$p->niche}";
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
