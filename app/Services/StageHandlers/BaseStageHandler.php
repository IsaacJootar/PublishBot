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

    /** Build a voice-profile suffix appended to system prompts when run has one. */
    protected function voiceSuffix(PipelineRun $run): string
    {
        $profile = $run->voiceProfile;

        if (! $profile || ! $profile->extracted_style) {
            return '';
        }

        $domainContext = $profile->description
            ? "This content is in the domain of: {$profile->description}.\n"
            : '';

        return "\n\n--- AUTHOR VOICE PROFILE: {$profile->name} ---\n"
            .$domainContext
            .$profile->extracted_style
            ."\n--- END VOICE PROFILE ---\n\n"
            ."Write in this author's exact voice for this domain. "
            .'Sound like they personally wrote this. Not like an AI.';
    }
}
