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

    private function promptLibrary(DigitalProduct $p, array $opts, string $titleHint, string $voice): array
    {
        $count = $opts['prompt_count'] ?? 30;
        $categoriesHint = $opts['categories'] ?? '';

        $system = "You are a digital product strategist. You design prompt libraries that sell on Gumroad.{$voice}";

        $user = "Niche: {$p->niche}\n"
            ."Buyer: {$p->buyer_description}\n"
            ."Problem: {$p->buyer_problem}\n"
            ."Total prompts: {$count}\n"
            ."Category hints: {$categoriesHint}\n"
            ."{$titleHint}\n\n"
            ."Design the structure. Return JSON:\n"
            ."{\n"
            .'  "product_title": "Specific outcome-focused title",'."\n"
            .'  "tagline": "One-line subtitle that says who it is for + what it does",'."\n"
            .'  "categories": ['."\n"
            .'    {"name": "Category name", "prompt_count": 10, "what_it_covers": "One sentence"},'."\n"
            ."    ...\n"
            ."  ]\n"
            ."}\n\n"
            ."Split the {$count} prompts across 4–8 categories. Return only the JSON.";

        return [$system, $user];
    }

    private function sopPack(DigitalProduct $p, array $opts, string $titleHint, string $voice): array
    {
        $count = $opts['sop_count'] ?? 10;
        $bizType = $opts['business_type'] ?? $p->niche;
        $areas = $opts['key_areas'] ?? '';

        $system = "You are a business operations designer. You produce SOP packs that solo founders and small teams actually use.{$voice}";

        $user = "Business type: {$bizType}\n"
            ."Niche: {$p->niche}\n"
            ."Buyer: {$p->buyer_description}\n"
            ."Problem: {$p->buyer_problem}\n"
            ."Total SOPs: {$count}\n"
            ."Key areas: {$areas}\n"
            ."{$titleHint}\n\n"
            ."Design the structure. Return JSON:\n"
            ."{\n"
            .'  "product_title": "Title",'."\n"
            .'  "business_type": "'.$bizType.'",'."\n"
            .'  "sops": ['."\n"
            .'    {"title": "SOP title", "covers": "What process it covers", "estimated_steps": 8},'."\n"
            ."    ...\n"
            ."  ]\n"
            ."}\n\n"
            ."Return exactly {$count} SOPs. Only the JSON.";

        return [$system, $user];
    }

    private function emailVault(DigitalProduct $p, array $opts, string $titleHint, string $voice): array
    {
        $sequences = $opts['sequence_count'] ?? 5;
        $emailsPer = $opts['emails_per_sequence'] ?? 5;
        $types = $opts['sequence_types'] ?? '';

        $system = "You are an email marketing strategist for digital product creators.{$voice}";

        $user = "Niche: {$p->niche}\n"
            ."Buyer: {$p->buyer_description}\n"
            ."Problem: {$p->buyer_problem}\n"
            ."Sequences: {$sequences}\n"
            ."Emails per sequence: {$emailsPer}\n"
            ."Sequence type hints: {$types}\n"
            ."{$titleHint}\n\n"
            ."Design the structure. Return JSON:\n"
            ."{\n"
            .'  "product_title": "Title",'."\n"
            .'  "sequences": ['."\n"
            .'    {"name": "Sequence name", "trigger": "When to send", "email_count": '.$emailsPer.', "goal": "What this sequence achieves"},'."\n"
            ."    ...\n"
            ."  ]\n"
            ."}\n\n"
            ."Return exactly {$sequences} sequences. Only the JSON.";

        return [$system, $user];
    }

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
