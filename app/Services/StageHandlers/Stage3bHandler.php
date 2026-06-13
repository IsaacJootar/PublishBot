<?php

namespace App\Services\StageHandlers;

use App\Models\PipelineRun;
use App\Models\PipelineStage;

class Stage3bHandler extends BaseStageHandler
{
    public function handle(PipelineRun $run, PipelineStage $stage): void
    {
        $ebook = $this->pipeline->readOutput($run, '03-ebook-draft.txt') ?? '';
        $chapters = $this->splitChapters($ebook);
        $total = count($chapters);

        $system = 'You are converting ebook chapters into structured workbook exercise pages. '
            .'Each page should be practical, actionable, and formatted for a printable PDF.'
            .$this->voiceSuffix($run);

        $workbook = "WORKBOOK: {$run->topic}\n";
        $workbook .= 'GENERATED: '.now()->toDateTimeString()."\n";
        $workbook .= str_repeat('=', 60)."\n\n";

        foreach ($chapters as $i => $chapterText) {
            $num = $i + 1;
            $this->setProgress($stage, "Converting chapter {$num} of {$total} to workbook page...");

            $user = "Convert the following chapter into a workbook exercise page.\n"
                ."Include exactly:\n"
                ."1. A short intro paragraph (2 to 3 sentences)\n"
                ."2. One reflection question for the reader\n"
                ."3. One fill-in activity\n"
                ."4. A weekly challenge\n"
                ."5. A 7-day progress tracker (label each day)\n\n"
                ."Chapter content:\n".substr($chapterText, 0, 2000);

            $page = $this->claude->complete($system, $user);
            $workbook .= "WORKBOOK PAGE {$num}\n";
            $workbook .= str_repeat('-', 40)."\n";
            $workbook .= $page."\n\n";
        }

        $this->pipeline->writeOutput($run, $stage->output_filename, $workbook);
    }

    private function splitChapters(string $text): array
    {
        // Split by "CHAPTER N:" markers written by Stage3a
        $parts = preg_split('/CHAPTER\s+\d+:/i', $text);
        $parts = array_filter(array_map('trim', $parts), fn ($p) => strlen($p) > 100);

        return array_values($parts) ?: [substr($text, 0, 3000)];
    }
}
