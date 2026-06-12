<?php

namespace App\Services\StageHandlers;

use App\Models\PipelineRun;
use App\Models\PipelineStage;

class Stage4Handler extends BaseStageHandler
{
    public function handle(PipelineRun $run, PipelineStage $stage): void
    {
        $outline = $this->pipeline->readOutput($run, '02-outline.txt') ?? '';
        $authorName = $run->user->getSettings()->author_name ?: 'the author';

        // Extract title from outline
        preg_match('/title[:\s]+([^\n]+)/i', $outline, $m);
        $ebookTitle = trim($m[1] ?? $run->topic.' — Complete Guide');

        $output = "LISTINGS: {$run->topic}\n";
        $output .= 'GENERATED: '.now()->toDateTimeString()."\n";
        $output .= str_repeat('=', 60)."\n\n";

        // Amazon KDP
        $this->setProgress($stage, 'Writing Amazon KDP listing...');
        $kdpSystem = 'You are an Amazon KDP listing specialist who writes descriptions that rank well and convert browsers into buyers.';
        $kdpUser = "Write an Amazon KDP ebook description for a book called: {$ebookTitle}\n"
            ."Author: {$authorName}\n"
            ."Topic: {$run->topic}\n"
            .'150 to 200 words. Open with a reader pain point. Present the book as the solution. '
            .'Close with a clear call to action to purchase. Do not use markdown formatting.';

        $output .= "=== AMAZON KDP LISTING ===\n";
        $output .= $this->claude->complete($kdpSystem, $kdpUser)."\n\n";

        // Etsy
        $this->setProgress($stage, 'Writing Etsy listing...');
        $etsy = $this->claude->complete(
            'You are an expert Etsy seller who writes listings that rank in search and convert shoppers.',
            "Write an Etsy digital product listing for a printable workbook called: {$ebookTitle} Workbook\n"
            ."Topic: {$run->topic}\n"
            ."Return:\n"
            ."- Title: format is [{$run->topic} topic] Workbook | Printable PDF | Instant Download\n"
            ."- Description: 5 paragraphs covering the problem, what is inside, who it is for, what the buyer receives, and a note about instant digital delivery\n"
            .'- Tags: exactly 13 comma-separated tags relevant to the product and topic'
        );

        // Validate tag count
        preg_match('/Tags?:(.+)/is', $etsy, $tm);
        $tags = array_filter(array_map('trim', explode(',', $tm[1] ?? '')));
        $tagCount = count($tags);
        $tagNote = $tagCount !== 13 ? " ⚠️ TAG COUNT: {$tagCount} (should be 13 — please adjust)" : ' ✓ 13 tags';

        $output .= "=== ETSY LISTING{$tagNote} ===\n";
        $output .= $etsy."\n\n";

        // Gumroad
        $this->setProgress($stage, 'Writing Gumroad listing...');
        $gumroad = $this->claude->complete(
            'You write compelling Gumroad product pages in a direct, conversational style.',
            "Write a Gumroad product page description for a digital bundle called: {$run->topic} Complete Pack\n"
            ."The bundle includes an ebook, workbook, and printable pack.\n"
            .'100 words, conversational and direct. No markdown.'
        );

        $output .= "=== GUMROAD LISTING ===\n";
        $output .= $gumroad."\n\n";

        $this->pipeline->writeOutput($run, $stage->output_filename, $output);
    }
}
