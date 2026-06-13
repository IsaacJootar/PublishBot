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

            $output = "PRODUCT: {$product->product_title}\n"
                .'GENERATED: '.now()->toDateTimeString()."\n"
                .str_repeat('=', 60)."\n\n";

            match ($product->product_type) {
                'prompt_library' => $this->writePromptLibrary($product, $structure, $claude, $voice, $output),
                'sop_pack' => $this->writeSopPack($product, $structure, $claude, $voice, $output),
                'email_sequence_vault' => $this->writeEmailVault($product, $structure, $claude, $voice, $output),
            };
        } catch (Throwable $e) {
            $product->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'progress_note' => null]);
            throw $e;
        }
    }

    private function writePromptLibrary(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice, string $output): void
    {
        $categories = $structure['categories'] ?? [];
        $total = count($categories);
        $system = "You are writing a premium prompt library for {$p->niche}. Every prompt must be immediately usable, niche-specific, and use [BRACKETS] for variables.{$voice}";

        foreach ($categories as $i => $cat) {
            $n = $i + 1;
            $p->update(['progress_note' => "Writing category {$n} of {$total}: {$cat['name']}"]);

            $count = $cat['prompt_count'] ?? 5;
            $user = "Product: {$p->product_title}\n"
                ."Niche: {$p->niche}\n"
                ."Buyer: {$p->buyer_description}\n"
                ."Category {$n} of {$total}: {$cat['name']}\n"
                ."Number of prompts: {$count}\n\n"
                ."Write {$count} prompts for this category. Format each as:\n\n"
                ."PROMPT N: [title]\nUSE WHEN: [trigger]\nTHE PROMPT:\n[full prompt with [BRACKETS]]\nTIP: [one sentence]\n\n"
                .'No preamble.';

            $text = $claude->complete($system, $user);
            $output .= "CATEGORY {$n}: {$cat['name']}\n".str_repeat('-', 40)."\n{$text}\n\n";
            $p->update(['content_output' => $output]);
        }

        $p->update(['current_stage' => 4, 'status' => 'draft', 'progress_note' => null]);
    }

    private function writeSopPack(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice, string $output): void
    {
        $sops = $structure['sops'] ?? [];
        $bizType = $structure['business_type'] ?? $p->niche;
        $total = count($sops);
        $system = "You are writing a professional SOP pack for {$bizType}. Numbered steps, clear, complete.{$voice}";

        foreach ($sops as $i => $sop) {
            $n = $i + 1;
            $p->update(['progress_note' => "Writing SOP {$n} of {$total}: {$sop['title']}"]);

            $user = "Product: {$p->product_title}\n"
                ."Business type: {$bizType}\n"
                ."SOP {$n} of {$total}: {$sop['title']}\n"
                ."Covers: {$sop['covers']}\n\n"
                ."Write the complete SOP. Format:\n\n"
                ."SOP: {$sop['title']}\nPURPOSE:\n[one sentence]\nWHEN TO USE THIS SOP:\n[trigger]\nWHAT YOU NEED BEFORE STARTING:\n[list]\nSTEPS:\n1. ...\n2. ...\nCOMMON MISTAKES TO AVOID:\n- ...\nNOTES:\n[edge cases]\n\n"
                .'No preamble.';

            $text = $claude->complete($system, $user);
            $output .= "SOP {$n}: {$sop['title']}\n".str_repeat('-', 40)."\n{$text}\n\n";
            $p->update(['content_output' => $output]);
        }

        $p->update(['current_stage' => 4, 'status' => 'draft', 'progress_note' => null]);
    }

    private function writeEmailVault(DigitalProduct $p, array $structure, ClaudeService $claude, string $voice, string $output): void
    {
        $sequences = $structure['sequences'] ?? [];
        $total = count($sequences);
        $system = "You are writing a premium email vault for {$p->niche}. Real person voice. Buyer's language. One job per email.{$voice}";

        foreach ($sequences as $i => $seq) {
            $n = $i + 1;
            $emailCount = $seq['email_count'] ?? 5;
            $p->update(['progress_note' => "Writing sequence {$n} of {$total}: {$seq['name']}"]);

            $user = "Product: {$p->product_title}\n"
                ."Niche: {$p->niche}\n"
                ."Buyer: {$p->buyer_description}\n"
                ."Sequence {$n} of {$total}: {$seq['name']}\n"
                ."Trigger: {$seq['trigger']}\n"
                ."Number of emails: {$emailCount}\n"
                ."Goal: {$seq['goal']}\n\n"
                ."Write all {$emailCount} emails. Format each as:\n\n"
                ."{$seq['name']} — Email N of {$emailCount}\nSEND TIMING: [when]\nSUBJECT LINE: [subject]\nPREVIEW TEXT: [preview]\nBODY:\n[full body]\nCTA: [one action]\n\n"
                .'No preamble.';

            $text = $claude->complete($system, $user);
            $output .= "SEQUENCE {$n}: {$seq['name']}\n".str_repeat('-', 40)."\n{$text}\n\n";
            $p->update(['content_output' => $output]);
        }

        $p->update(['current_stage' => 4, 'status' => 'draft', 'progress_note' => null]);
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
