<?php

namespace App\Services\StageHandlers;

use App\Models\PipelineRun;
use App\Models\PipelineStage;
use App\Services\ClaudeService;
use App\Services\PipelineService;

abstract class BaseStageHandler
{
    public function __construct(
        protected ClaudeService $claude,
        protected PipelineService $pipeline
    ) {}

    abstract public function handle(PipelineRun $run, PipelineStage $stage): void;

    protected function setProgress(PipelineStage $stage, string $note): void
    {
        $stage->update(['progress_note' => $note]);
    }
}
