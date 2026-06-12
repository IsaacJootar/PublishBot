<?php

namespace App\Services\StageHandlers;

use App\Models\PipelineRun;
use App\Models\PipelineStage;

class Stage5Handler extends BaseStageHandler
{
    public function handle(PipelineRun $run, PipelineStage $stage): void
    {
        $this->setProgress($stage, 'Writing Pinterest pin captions...');

        $pinCount = $run->user->getSettings()->pin_count;

        $system = 'You are a Pinterest content strategist who creates pin captions that drive clicks and saves.';

        $user = "Generate {$pinCount} Pinterest pin captions for the following topic: {$run->topic}\n\n"
            ."Create exactly:\n"
            ."- 3 quote pins: an inspiring or thought-provoking quote related to the topic\n"
            ."- 3 tip list pins: formatted as '5 ways to...' or '3 things that...'\n"
            ."- 3 question pins: a question that makes the reader want to click\n"
            ."- 3 challenge pins: a short challenge prompt related to the topic\n"
            .'- '.($pinCount - 12)." product preview pins: a caption describing the ebook or workbook\n\n"
            ."Each caption must be under 100 characters. Number each caption 1 through {$pinCount}. Label each with its type.";

        $output = "PINTEREST PINS: {$run->topic}\n";
        $output .= 'GENERATED: '.now()->toDateTimeString()."\n";
        $output .= str_repeat('=', 60)."\n\n";
        $output .= $this->claude->complete($system, $user);

        $this->pipeline->writeOutput($run, $stage->output_filename, $output);
    }
}
