<?php

namespace App\Services\StageHandlers;

use App\Models\PipelineRun;
use App\Models\PipelineStage;

class Stage2Handler extends BaseStageHandler
{
    public function handle(PipelineRun $run, PipelineStage $stage): void
    {
        $this->setProgress($stage, 'Generating ebook outline...');

        $settings = $run->user->getSettings();
        $tone = $settings->default_tone ?: 'clear, practical, and warm';
        $chapters = $settings->chapter_count;

        $system = 'You are an expert non-fiction ebook author and content strategist. '
            .'You write for a general adult audience across any subject matter. '
            ."Adapt your tone to match the topic. Default tone: {$tone}.";

        $user = "Generate a complete ebook outline for the following topic: {$run->topic}\n\n"
            ."Return exactly:\n"
            ."- A compelling title and subtitle\n"
            ."- {$chapters} chapters, each with a title and 3 key takeaways\n"
            ."- 10 printable worksheet ideas derived from the content\n"
            .'- 8 workbook exercise ideas derived from the content';

        $output = $this->claude->complete($system, $user);

        $this->pipeline->writeOutput($run, $stage->output_filename, $output);
    }
}
