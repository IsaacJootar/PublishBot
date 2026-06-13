<?php

namespace App\Services\StageHandlers;

use App\Models\PipelineRun;
use App\Models\PipelineStage;

class Stage3aHandler extends BaseStageHandler
{
    public function handle(PipelineRun $run, PipelineStage $stage): void
    {
        $settings = $run->user->getSettings();
        $chapterCount = $settings->chapter_count;
        $wordTarget = $settings->words_per_chapter;
        $tone = $settings->default_tone ?: 'practical, warm, and direct';

        // Parse outline to extract chapter titles
        $outline = $this->pipeline->readOutput($run, '02-outline.txt') ?? '';
        $chapters = $this->extractChapters($outline, $chapterCount);

        $system = 'You are writing a chapter of a non-fiction ebook. '
            ."Match the tone to the topic. Be {$tone}. "
            .'Write in short paragraphs. Use subheadings. End with one actionable tip.'
            .$this->voiceSuffix($run);

        $fullDraft = "TOPIC: {$run->topic}\n";
        $fullDraft .= 'GENERATED: '.now()->toDateTimeString()."\n";
        $fullDraft .= str_repeat('=', 60)."\n\n";

        foreach ($chapters as $i => $chapter) {
            $num = $i + 1;
            $this->setProgress($stage, "Writing chapter {$num} of {$chapterCount}...");

            $user = "Write Chapter {$num}: {$chapter}\n"
                ."Topic: {$run->topic}\n"
                ."Length: {$wordTarget} words.\n\n"
                .'Write the chapter now. No preamble. Just the chapter content.';

            $chapterText = $this->claude->complete($system, $user);

            $fullDraft .= "CHAPTER {$num}: {$chapter}\n";
            $fullDraft .= str_repeat('-', 40)."\n";
            $fullDraft .= $chapterText."\n\n";
        }

        $this->pipeline->writeOutput($run, $stage->output_filename, $fullDraft);
    }

    private function extractChapters(string $outline, int $count): array
    {
        // Try to extract chapter titles from outline text
        preg_match_all('/chapter\s+\d+[:\s]+([^\n]+)/i', $outline, $matches);
        $found = array_map('trim', $matches[1] ?? []);

        // If extraction found enough chapters, use them
        if (count($found) >= $count) {
            return array_slice($found, 0, $count);
        }

        // Fallback: generic chapter titles
        $chapters = [];
        for ($i = 1; $i <= $count; $i++) {
            $chapters[] = "Chapter {$i}";
        }

        return $chapters;
    }
}
