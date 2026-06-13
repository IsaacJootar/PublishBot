<?php

namespace App\Jobs\DigitalProducts;

use App\Models\DigitalProduct;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ResearchJob implements ShouldQueue
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

        $product->update(['status' => 'researching', 'progress_note' => 'Researching your niche...', 'error_message' => null]);

        $voice = $this->voiceSuffix($product);

        $system = 'You are a digital product market researcher. You understand buyer '
            .'psychology, what sells on Gumroad and Selar, and what makes someone '
            ."pull out their card for a digital download.\n\n"
            .'You research with the precision of someone who has launched 50+ products. '
            .'You find the gaps, the pain, and the price point.'
            .$voice;

        $user = "Product type: {$product->product_type}\n"
            ."Topic / niche: {$product->niche}\n"
            ."Target buyer: {$product->buyer_description}\n"
            ."Their biggest problem: {$product->buyer_problem}\n\n"
            ."Research this market. Return as JSON:\n\n"
            ."{\n"
            .'  "buyer_profile": "2-3 sentence description of the exact buyer",'."\n"
            .'  "pain_points": ['."\n"
            .'    {"rank": 1, "pain": "...", "why_it_hurts": "..."},'."\n"
            .'    {"rank": 2, "pain": "...", "why_it_hurts": "..."},'."\n"
            .'    {"rank": 3, "pain": "...", "why_it_hurts": "..."},'."\n"
            .'    {"rank": 4, "pain": "...", "why_it_hurts": "..."},'."\n"
            .'    {"rank": 5, "pain": "...", "why_it_hurts": "..."}'."\n"
            ."  ],\n"
            .'  "market_gap": "What is missing in what already exists for this buyer?",'."\n"
            .'  "buyer_language": ["phrase 1", "phrase 2", "phrase 3", "phrase 4", "phrase 5"],'."\n"
            .'  "recommended_platform": "gumroad OR selar OR payhip — with one sentence reason",'."\n"
            .'  "recommended_price": 27,'."\n"
            .'  "price_justification": "Why this price works for this buyer and product type",'."\n"
            .'  "product_title_suggestions": ["Title option 1", "Title option 2", "Title option 3"]'."\n"
            ."}\n\n"
            .'Return only the JSON object. No markdown code fences.';

        try {
            $settings = $product->user->getSettings();
            $claude = new ClaudeService($settings);
            $raw = $claude->complete($system, $user);
            $json = $this->extractJson($raw);

            $product->update([
                'research_output' => $json,
                'recommended_platform' => $this->extractPlatform($json['recommended_platform'] ?? ''),
                'recommended_price' => $json['recommended_price'] ?? null,
                'current_stage' => 2,
                'status' => 'draft',
                'progress_note' => null,
            ]);
        } catch (Throwable $e) {
            $product->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'progress_note' => null]);
            throw $e;
        }
    }

    private function extractJson(string $raw): array
    {
        $raw = preg_replace('/```json|```/', '', $raw);
        $raw = trim($raw);
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            // Best-effort: find first { to last }
            $start = strpos($raw, '{');
            $end = strrpos($raw, '}');
            if ($start !== false && $end !== false) {
                $decoded = json_decode(substr($raw, $start, $end - $start + 1), true);
            }
        }

        return $decoded ?: ['raw' => $raw];
    }

    private function extractPlatform(string $text): string
    {
        $text = strtolower($text);
        foreach (['gumroad', 'selar', 'payhip'] as $p) {
            if (str_contains($text, $p)) {
                return $p;
            }
        }

        return 'gumroad';
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
