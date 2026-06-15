<?php

namespace App\Jobs\DigitalProducts;

use App\Models\DigitalProduct;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class StructureJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 240;

    public int $tries = 2;

    public function __construct(public int $productId) {}

    public function handle(): void
    {
        $product = DigitalProduct::find($this->productId);
        if (! $product) {
            return;
        }

        $product->update(['status' => 'structuring', 'progress_note' => 'Building the structure...', 'error_message' => null]);

        $research = $product->research_output ?? [];
        $titleSuggestions = $research['product_title_suggestions'] ?? [];
        $titleHint = $titleSuggestions ? 'Pick a strong title from: '.implode(' | ', $titleSuggestions) : 'Generate a strong product title.';

        $opts = $product->brief_options ?? [];
        $voice = $this->voiceSuffix($product);

        [$system, $user] = match ($product->product_type) {
            'prompt_library' => $this->promptLibrary($product, $opts, $titleHint, $voice),
            'sop_pack' => $this->sopPack($product, $opts, $titleHint, $voice),
            'email_sequence_vault' => $this->emailVault($product, $opts, $titleHint, $voice),
            'content_calendar_system' => $this->contentCalendar($product, $opts, $research, $titleHint, $voice),
            'excel_tracker' => $this->excelTracker($product, $opts, $research, $titleHint, $voice),
            'notion_business_os' => $this->notionOs($product, $opts, $research, $titleHint, $voice),
            'website_copy_pack' => $this->websiteCopyPack($product, $opts, $research, $titleHint, $voice),
            'brand_messaging_system' => $this->brandMessaging($product, $opts, $research, $titleHint, $voice),
            'sales_funnel_copy' => $this->salesFunnel($product, $opts, $research, $titleHint, $voice),
            'niche_research_report' => $this->nicheResearch($product, $opts, $research, $titleHint, $voice),
            'buyer_persona_pack' => $this->buyerPersona($product, $opts, $research, $titleHint, $voice),
        };

        try {
            $settings = $product->user->getSettings();
            $claude = new ClaudeService($settings);
            $raw = $claude->complete($system, $user);
            $json = $this->extractJson($raw);

            $product->update([
                'structure_output' => $json,
                'product_title' => $json['product_title'] ?? $product->product_title,
                'current_stage' => 3,
                'status' => 'draft',
                'progress_note' => null,
            ]);
        } catch (Throwable $e) {
            $product->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'progress_note' => null]);
            throw $e;
        }
    }

    // ── Original 3 types ─────────────────────────────────────────────────────

    private function promptLibrary(DigitalProduct $p, array $opts, string $titleHint, string $voice): array
    {
        $count = $opts['prompt_count'] ?? 50;
        $categoriesHint = $opts['categories'] ?? '';
        $system = "You are a digital product strategist. You design prompt libraries that sell on Gumroad.{$voice}";
        $user = "Niche: {$p->niche}\nBuyer: {$p->buyer_description}\nProblem: {$p->buyer_problem}\n"
            ."Total prompts: {$count}\nCategory hints: {$categoriesHint}\n{$titleHint}\n\n"
            ."Design the structure. Return JSON:\n"
            .'{"product_title":"Specific outcome-focused title","tagline":"One-line subtitle",'
            .'"categories":[{"name":"Category name","prompt_count":10,"what_it_covers":"One sentence"},...]}'."\n\n"
            ."Split the {$count} prompts across 4-8 categories. Return only the JSON.";

        return [$system, $user];
    }

    private function sopPack(DigitalProduct $p, array $opts, string $titleHint, string $voice): array
    {
        $count = $opts['sop_count'] ?? 10;
        $bizType = $opts['business_type'] ?? $p->niche;
        $areas = $opts['key_areas'] ?? '';
        $system = "You are a business operations designer. You produce SOP packs that solo founders and small teams actually use.{$voice}";
        $user = "Business type: {$bizType}\nNiche: {$p->niche}\nBuyer: {$p->buyer_description}\n"
            ."Problem: {$p->buyer_problem}\nTotal SOPs: {$count}\nKey areas: {$areas}\n{$titleHint}\n\n"
            ."Design the structure. Return JSON:\n"
            .'{"product_title":"Title","business_type":"'.$bizType.'",'
            .'"sops":[{"title":"SOP title","covers":"What process it covers","estimated_steps":8},...]}'."\n\n"
            ."Return exactly {$count} SOPs. Only the JSON.";

        return [$system, $user];
    }

    private function emailVault(DigitalProduct $p, array $opts, string $titleHint, string $voice): array
    {
        $sequences = $opts['sequence_count'] ?? 5;
        $emailsPer = $opts['emails_per_sequence'] ?? 5;
        $types = $opts['sequence_types'] ?? '';
        $system = "You are an email marketing strategist for digital product creators.{$voice}";
        $user = "Niche: {$p->niche}\nBuyer: {$p->buyer_description}\nProblem: {$p->buyer_problem}\n"
            ."Sequences: {$sequences}\nEmails per sequence: {$emailsPer}\nSequence type hints: {$types}\n{$titleHint}\n\n"
            ."Design the structure. Return JSON:\n"
            .'{"product_title":"Title","sequences":[{"name":"Sequence name","trigger":"When to send",'
            .'"email_count":'.$emailsPer.',"goal":"What this sequence achieves"},...]}'."\n\n"
            ."Return exactly {$sequences} sequences. Only the JSON.";

        return [$system, $user];
    }

    // ── New 8 types ───────────────────────────────────────────────────────────

    private function contentCalendar(DigitalProduct $p, array $opts, array $research, string $titleHint, string $voice): array
    {
        $platforms = implode(', ', $opts['platforms'] ?? ['Instagram', 'LinkedIn']);
        $freq = $opts['posting_frequency'] ?? '5';
        $goals = implode(', ', $opts['content_goals'] ?? ['Brand awareness', 'Authority']);
        $tone = $opts['tone'] ?? 'Educational';
        $pillars = json_encode($research['content_pillars'] ?? []);

        $system = 'You are an expert content strategist who builds 90-day content calendar systems. '
            ."You create calendars that are specific, realistic to execute, and strategically designed to build authority.{$voice}";

        $user = "Niche: {$p->niche}\nTarget audience: {$p->buyer_description}\nPlatforms: {$platforms}\n"
            ."Posting frequency: {$freq} per week\nContent goals: {$goals}\nTone: {$tone}\n"
            ."Research pillars: {$pillars}\n{$titleHint}\n\n"
            ."Generate the structure for a 90-day content calendar system. Return JSON:\n"
            .'{"product_title":"The [Niche] 90-Day Content Calendar System","tagline":"90 days of content planned, written, and ready to post",'
            .'"monthly_themes":[{"month":1,"theme":"Theme name","focus":"What this month builds"},{"month":2,"theme":"...","focus":"..."},{"month":3,"theme":"...","focus":"..."}],'
            .'"weekly_breakdown":{"posts_per_week":'.$freq.',"platform_distribution":{"instagram":2,"linkedin":2}},'
            .'"content_pillars":[{"pillar":"Name","description":"What it covers","percentage":25},{"pillar":"...","description":"...","percentage":25},{"pillar":"...","description":"...","percentage":25},{"pillar":"...","description":"...","percentage":25}],'
            .'"sections":["How to use this calendar","Your content pillars","Month 1 calendar","Month 2 calendar","Month 3 calendar","Caption frameworks","Hashtag strategy","Repurposing guide"]}'
            ."\n\nReturn only the JSON.";

        return [$system, $user];
    }

    private function excelTracker(DigitalProduct $p, array $opts, array $research, string $titleHint, string $voice): array
    {
        $trackerType = $opts['tracker_type'] ?? 'Client tracker';
        $teamSize = $opts['team_size'] ?? 'Just me';
        $metrics = json_encode($research['key_metrics'] ?? []);
        $features = json_encode($research['must_have_features'] ?? []);

        $system = 'You are an expert spreadsheet designer and business systems consultant. '
            ."You create trackers that are immediately usable with zero setup.{$voice}";

        $user = "Tracker type: {$trackerType}\nBusiness type: {$p->niche}\n"
            ."Target user: {$p->buyer_description}\nTeam size: {$teamSize}\n"
            ."Key metrics from research: {$metrics}\nMust-have features: {$features}\n{$titleHint}\n\n"
            ."Design the complete spreadsheet structure. Return JSON:\n"
            .'{"product_title":"The [Business] [Tracker Type] Tracker","tagline":"Track everything without expensive software",'
            .'"sheets":['
            .'{"sheet_name":"How To Use","purpose":"Usage guide — first thing buyer reads","columns":[]},'
            .'{"sheet_name":"Dashboard","purpose":"Overview of all key metrics at a glance","columns":[{"column":"A","header":"Metric","data_type":"text","formula":null,"description":"What to track"}]},'
            .'{"sheet_name":"[Main Tracker]","purpose":"Primary tracking sheet","columns":[{"column":"A","header":"Column name","data_type":"text/number/date/dropdown","dropdown_options":[],"formula":null,"description":"What goes here"}],"example_rows":3}'
            .']}'."\n\nDesign every column completely. Include real formulas where applicable. Return only the JSON.";

        return [$system, $user];
    }

    private function notionOs(DigitalProduct $p, array $opts, array $research, string $titleHint, string $voice): array
    {
        $teamSize = $opts['team_size'] ?? 'Solo';
        $clientCount = $opts['client_count'] ?? '1-5';
        $areas = implode(', ', $opts['organise_areas'] ?? ['Clients', 'Projects', 'Finance']);
        $currentTools = $opts['current_tools'] ?? '';
        $databases = json_encode($research['core_databases_needed'] ?? []);

        $system = 'You are a Notion expert and business systems designer. '
            ."You build Notion workspaces that replace 5-10 different apps with one organised system.{$voice}";

        $user = "Business type: {$p->niche}\nTeam size: {$teamSize}\nClients at once: {$clientCount}\n"
            ."Areas to organise: {$areas}\nCurrent tools to replace: {$currentTools}\n"
            ."Core databases from research: {$databases}\n{$titleHint}\n\n"
            ."Design the complete Notion workspace structure. Return JSON:\n"
            .'{"product_title":"The [Business Type] Notion Business OS","tagline":"Your complete business in one Notion workspace",'
            .'"workspace_sections":['
            .'{"section_name":"🏠 Home Dashboard","purpose":"Command centre — everything at a glance","databases":[],"pages":["Daily overview","This week","Goals dashboard"]},'
            .'{"section_name":"👥 Clients","purpose":"All client info and relationship management","databases":[{"database_name":"Client Database","properties":[{"name":"Name","type":"title"},{"name":"Status","type":"select","options":["Active","Prospect","Completed","Paused"]}],"views":["Table view","Board view"],"template_pages":["New client template"]}],"pages":[]},'
            .'{"section_name":"📁 Projects","purpose":"Project and task management","databases":[{"database_name":"Projects","properties":[{"name":"Project","type":"title"},{"name":"Client","type":"relation"},{"name":"Status","type":"select","options":["Planning","In Progress","Review","Done"]}],"views":["Table view","Board view","Timeline view"],"template_pages":["New project template"]}],"pages":[]}'
            .']}'."\n\nDesign every section and database completely. Return only the JSON.";

        return [$system, $user];
    }

    private function websiteCopyPack(DigitalProduct $p, array $opts, array $research, string $titleHint, string $voice): array
    {
        $tone = $opts['tone'] ?? 'Professional';
        $serviceCount = (int) ($opts['service_count'] ?? 2);
        $serviceNames = array_filter(array_map('trim', explode("\n", $opts['service_names'] ?? '')));
        $serviceList = $serviceNames ?: array_fill(0, $serviceCount, '[Service Name]');
        $primaryMessage = $research['primary_message'] ?? '';

        $pages = array_merge(['Home', 'About'], array_slice($serviceList, 0, $serviceCount), ['Contact & SEO']);
        $pagesJson = json_encode($pages);

        $system = 'You are a professional website copywriter creating a niche website copy template pack. '
            .'This pack is bought by many people in the same niche — it uses [BRACKETS] for specific details buyers fill in. '
            ."Tone: {$tone}.{$voice}";

        $user = "Niche: {$p->niche}\nTypical buyer: {$p->buyer_description}\n"
            ."Their biggest problem: {$p->buyer_problem}\nTone: {$tone}\n"
            ."Primary message: {$primaryMessage}\nService types in this niche: ".implode(', ', $serviceList)."\n{$titleHint}\n\n"
            ."IMPORTANT: This is a niche template — use [YOUR BUSINESS NAME], [YOUR SERVICE NAME], [YOUR TAGLINE], [YOUR CTA TEXT], [ADD YOUR TESTIMONIAL HERE] throughout.\n\n"
            ."Design the website copy pack structure. Return JSON:\n"
            .'{"product_title":"The '.$p->niche.' Website Copy Pack","tagline":"Every word for your website — customise and launch in one day",'
            .'"pages":'.$pagesJson.','
            .'"tone_direction":"How to apply the '.$tone.' tone throughout — with before/after examples",'
            .'"key_messages":["Primary message for this niche","Supporting message 1","Supporting message 2"],'
            .'"placeholder_guide":{"business_name":"[YOUR BUSINESS NAME]","service":"[YOUR SERVICE NAME]","tagline":"[YOUR TAGLINE]","cta":"[YOUR CTA TEXT]","testimonial":"[ADD YOUR TESTIMONIAL HERE]"},'
            .'"seo_keywords":["keyword 1","keyword 2","keyword 3","keyword 4","keyword 5"]}'
            ."\n\nReturn only the JSON.";

        return [$system, $user];
    }

    private function brandMessaging(DigitalProduct $p, array $opts, array $research, string $titleHint, string $voice): array
    {
        $tone = $opts['tone'] ?? 'Professional';
        $differentiator = $opts['differentiator'] ?? '';
        $pricePoint = $opts['price_point'] ?? '';
        $archetype = $research['brand_archetype'] ?? '';
        $whiteSpace = $research['white_space'] ?? '';

        $system = 'You are a senior brand strategist creating a niche brand messaging template system. '
            .'This pack is bought by many practitioners in the same niche — it uses [BRACKETS] for specific details buyers fill in. '
            ."Tone: {$tone}.{$voice}";

        $user = "Niche: {$p->niche}\nTypical practitioner: {$p->buyer_description}\n"
            ."Their problem: {$p->buyer_problem}\nWhat makes the best ones different: {$differentiator}\n"
            ."Typical price point: {$pricePoint}\nBrand archetype for this niche: {$archetype}\n"
            ."White space opportunity: {$whiteSpace}\nTone: {$tone}\n{$titleHint}\n\n"
            ."IMPORTANT: Use [YOUR NAME], [YOUR BUSINESS NAME], [YOUR SPECIFIC EXAMPLE], [YOUR NICHE] as placeholders throughout.\n\n"
            ."Design the brand messaging system deliverables. Return JSON:\n"
            .'{"product_title":"The '.$p->niche.' Brand Messaging System","tagline":"Your complete brand voice — from mission to tagline to scripts",'
            .'"deliverables":["Brand Foundation (mission, vision, promise, values)","3 Buyer Personas","Brand Voice Guide","10 Taglines + Recommendation","Elevator Pitches (10s, 30s, 2min)","Objection Responses","Platform Bios (all 7 platforms)","Conversion Scripts (7 scripts)","Brand Application Guide"],'
            .'"brand_archetype":"'.$archetype.'",'
            .'"placeholder_guide":{"name":"[YOUR NAME]","business":"[YOUR BUSINESS NAME]","example":"[YOUR SPECIFIC EXAMPLE]"},'
            .'"primary_tone":"'.$tone.'"}'
            ."\n\nReturn only the JSON.";

        return [$system, $user];
    }

    private function salesFunnel(DigitalProduct $p, array $opts, array $research, string $titleHint, string $voice): array
    {
        $tone = $opts['tone'] ?? 'Professional';
        $pricePoint = $opts['price_point'] ?? '';
        $needsUpsell = $opts['needs_upsell'] ?? 'No';
        $needsWebinar = $opts['needs_webinar'] ?? 'No';
        $funnelLength = $research['funnel_length'] ?? 'Medium (7 emails)';
        $leadMagnetType = $research['best_lead_magnet_type'] ?? 'Checklist';
        $awarenessLevel = $research['awareness_level'] ?? 'Problem aware';

        $emailCount = str_contains($funnelLength, '10') ? 10 : (str_contains($funnelLength, '5') ? 5 : 7);

        $components = ['Lead Magnet ('.$leadMagnetType.')', 'Landing Page', "Email Sequence ({$emailCount} emails)", 'Sales Page'];
        if ($needsUpsell === 'Yes') {
            $components[] = 'Upsell Page';
        }
        if ($needsWebinar === 'Yes') {
            $components[] = 'Webinar Invitation Email';
        }

        $system = 'You are an expert direct response copywriter creating a niche funnel copy template pack. '
            .'This pack is bought by many people in the same niche — it uses [BRACKETS] for specific details buyers fill in. '
            ."Tone: {$tone}.{$voice}";

        $user = "Niche: {$p->niche}\nTypical offer price range: {$pricePoint}\n"
            ."Typical buyer: {$p->buyer_description}\nTheir biggest problem: {$p->buyer_problem}\n"
            .'What they typically try first: '.($opts['tried_before'] ?? '')."\n"
            .'What makes great offers in this niche different: '.($opts['differentiator'] ?? '')."\n"
            ."Tone: {$tone}\nAwareness level: {$awarenessLevel}\n"
            ."Lead magnet type: {$leadMagnetType}\nFunnel length: {$funnelLength}\n{$titleHint}\n\n"
            ."IMPORTANT: Use [YOUR OFFER NAME], [YOUR PRICE], [YOUR BONUS NAME], [YOUR NAME] as placeholders throughout.\n\n"
            ."Design the sales funnel copy pack structure. Return JSON:\n"
            .'{"product_title":"The '.$p->niche.' Sales Funnel Copy Pack","tagline":"Your complete funnel — landing page, emails, and sales page",'
            .'"funnel_components":'.json_encode($components).','
            .'"email_count":'.$emailCount.','
            .'"awareness_level":"'.$awarenessLevel.'",'
            .'"lead_magnet_type":"'.$leadMagnetType.'",'
            .'"placeholder_guide":{"offer":"[YOUR OFFER NAME]","price":"[YOUR PRICE]","bonus":"[YOUR BONUS NAME]","name":"[YOUR NAME]"}}'
            ."\n\nReturn only the JSON.";

        return [$system, $user];
    }

    private function nicheResearch(DigitalProduct $p, array $opts, array $research, string $titleHint, string $voice): array
    {
        $geography = $opts['geography'] ?? 'Nigeria';
        $reportDepth = $opts['report_depth'] ?? 'Standard (20 pages)';
        $competitors = $opts['competitors'] ?? '';
        $specificQs = $opts['specific_questions'] ?? '';

        $sections = [
            'Executive Summary',
            'Market Overview',
            'Buyer Analysis',
            'Competitor Landscape',
            'Market Gaps & Opportunities',
            'Pricing & Revenue Analysis',
            'Marketing & Distribution',
            'Trend Analysis',
            '90-Day Action Plan',
        ];

        $system = 'You are a senior market research analyst. '
            ."You produce niche research reports that give decision-makers the insight they need to act.{$voice}";

        $user = "Niche: {$p->niche}\nGeographic focus: {$geography}\n"
            ."Who uses this report: {$p->buyer_description}\nDecision to inform: {$p->buyer_problem}\n"
            ."Report depth: {$reportDepth}\nSpecific competitors: {$competitors}\n"
            ."Specific questions to answer: {$specificQs}\n{$titleHint}\n\n"
            ."Design the research report structure. Return JSON:\n"
            .'{"product_title":"The '.$p->niche.' Market Research Report","tagline":"Deep market intelligence — researched and ready to act on",'
            .'"sections":'.json_encode($sections).','
            .'"report_depth":"'.$reportDepth.'",'
            .'"geography":"'.$geography.'",'
            .'"page_count":'.($reportDepth === 'Overview (10 pages)' ? 10 : ($reportDepth === 'Deep dive (30 pages)' ? 30 : 20)).'}'
            ."\n\nReturn only the JSON.";

        return [$system, $user];
    }

    private function buyerPersona(DigitalProduct $p, array $opts, array $research, string $titleHint, string $voice): array
    {
        $personaCount = (int) ($opts['persona_count'] ?? 3);
        $pricePoint = $opts['price_point'] ?? '';
        $geography = $opts['geography'] ?? 'Nigeria';
        $personaTypeHints = $opts['persona_types'] ?? '';

        $system = 'You are a consumer psychologist and customer research specialist. '
            ."You create buyer personas specific enough to feel like real people.{$voice}";

        $user = "Business type: {$p->niche}\nWhat is being sold: {$p->buyer_problem}\n"
            ."Ideal customer: {$p->buyer_description}\nPrice point: {$pricePoint}\n"
            ."Geographic focus: {$geography}\nPersona count: {$personaCount}\n"
            ."Persona type hints: {$personaTypeHints}\n{$titleHint}\n\n"
            ."Design the buyer persona pack structure. Return JSON:\n"
            .'{"product_title":"The '.$p->niche.' Buyer Persona Pack","tagline":"Know your buyer completely — '.$personaCount.' detailed personas",'
            .'"personas":'.json_encode(array_map(fn ($i) => [
                'number' => $i,
                'title' => "Persona {$i}",
                'archetype' => 'To be determined by Claude',
                'focus' => 'Core buyer type '.$i,
            ], range(1, $personaCount))).','
            .'"persona_count":'.$personaCount.'}'
            ."\n\nGenerate meaningful persona archetypes based on the brief. Return only the JSON.";

        return [$system, $user];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function extractJson(string $raw): array
    {
        $raw = preg_replace('/```json|```/', '', $raw);
        $raw = trim($raw);
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            $start = strpos($raw, '{');
            $end = strrpos($raw, '}');
            if ($start !== false && $end !== false) {
                $decoded = json_decode(substr($raw, $start, $end - $start + 1), true);
            }
        }

        return $decoded ?: ['raw' => $raw];
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
