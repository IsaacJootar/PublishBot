<?php

namespace App\Jobs\DigitalProducts;

use App\Models\DigitalProduct;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class PublishPackJob implements ShouldQueue
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

        $product->update(['status' => 'publishing', 'progress_note' => 'Building the launch pack...', 'error_message' => null]);

        $research = $product->research_output ?? [];
        $voice = $this->voiceSuffix($product);
        $platform = $product->recommended_platform ?: 'gumroad';
        $price = $product->recommended_price ?: 27;
        $contentSummary = mb_substr((string) $product->content_output, 0, 1500);

        $system = 'You are a launch copywriter for digital products. You write copy that sells without feeling like a pitch.'.$voice;

        $user = "Product title: {$product->product_title}\n"
            ."Product type: {$product->product_type}\n"
            ."Niche: {$product->niche}\n"
            ."Buyer: {$product->buyer_description}\n"
            ."Core problem: {$product->buyer_problem}\n"
            ."Platform: {$platform}\n"
            ."Price: \${$price}\n"
            .'Market gap: '.($research['market_gap'] ?? '')."\n"
            ."Content summary: {$contentSummary}\n\n"
            ."Return JSON only:\n"
            ."{\n"
            .'  "sales_page": {'."\n"
            .'    "headline": "...", "subheadline": "...", "hook": "...",'."\n"
            .'    "whats_inside": ["..","..","..","..",".."],'."\n"
            .'    "who_its_for": ["..","..",".."],'."\n"
            .'    "who_its_not_for": [".."],'."\n"
            .'    "price_justification": "...", "cta": "..."'."\n"
            ."  },\n"
            .'  "social_posts": {'."\n"
            .'    "linkedin": "...", "twitter": "...", "instagram": "...",'."\n"
            .'    "pinterest": "...", "whatsapp": "..."'."\n"
            ."  },\n"
            .'  "launch_emails": {'."\n"
            .'    "email_1": {"send_timing":"Launch day","subject":"...","body":"..."},'."\n"
            .'    "email_2": {"send_timing":"Day 3","subject":"...","body":"..."},'."\n"
            .'    "email_3": {"send_timing":"Day 7","subject":"...","body":"..."}'."\n"
            ."  },\n"
            .'  "platform_upload_steps": ["step1","step2","step3","step4","step5"]'."\n"
            ."}\n\n"
            .'Return only the JSON.';

        try {
            $settings = $product->user->getSettings();
            $claude = new ClaudeService($settings);
            $raw = $claude->complete($system, $user);
            $json = $this->extractJson($raw);

            $product->update([
                'publish_pack' => $json,
                'current_stage' => 5,
                'status' => 'complete',
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
