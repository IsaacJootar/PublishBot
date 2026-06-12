<?php

namespace App\Services;

use App\Jobs\RunPipelineStageJob;
use App\Models\PipelineRun;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Storage;

class PipelineService
{
    /**
     * Stage definitions — order and metadata.
     */
    public const STAGES = [
        ['number' => '1',  'name' => 'Topic validation',   'file' => '01-validation.txt'],
        ['number' => '2',  'name' => 'Outline generation',  'file' => '02-outline.txt'],
        ['number' => '3a', 'name' => 'Ebook draft',         'file' => '03-ebook-draft.txt'],
        ['number' => '3b', 'name' => 'Workbook extraction', 'file' => '03-workbook-draft.txt'],
        ['number' => '4',  'name' => 'Listings generation', 'file' => '04-listings.txt'],
        ['number' => '5',  'name' => 'Pinterest pin copy',  'file' => '05-pins.txt'],
    ];

    /**
     * Initialise all stage records and dispatch Stage 1.
     */
    public function start(PipelineRun $run): void
    {
        // Create all stage records as pending
        foreach (self::STAGES as $def) {
            $run->stages()->create([
                'stage_number' => $def['number'],
                'stage_name' => $def['name'],
                'status' => 'pending',
                'output_filename' => $def['file'],
            ]);
        }

        $run->update(['status' => 'running', 'started_at' => now()]);

        // Dispatch Stage 1 immediately
        RunPipelineStageJob::dispatch($run, '1');
    }

    /**
     * Called after user clicks Continue following Stage 1 review.
     */
    public function continue(PipelineRun $run): void
    {
        $run->update([
            'user_confirmed_continue' => true,
            'status' => 'running',
        ]);

        RunPipelineStageJob::dispatch($run, '2');
    }

    /**
     * Dispatch the next stage after a completed stage.
     */
    public function dispatchNext(PipelineRun $run, string $completedStage): void
    {
        $next = match ($completedStage) {
            '1' => null,  // Pause — wait for user Continue
            '2' => '3a',
            '3a' => '3b',
            '3b' => '4',
            '4' => '5',
            '5' => null,  // Pipeline complete
            default => null,
        };

        if ($next === null) {
            if ($completedStage === '1') {
                $run->update(['status' => 'paused']);
            } else {
                $run->update(['status' => 'completed', 'completed_at' => now()]);
            }

            return;
        }

        RunPipelineStageJob::dispatch($run, $next);
    }

    /**
     * Write output file for a stage.
     */
    public function writeOutput(PipelineRun $run, string $filename, string $content): void
    {
        $path = "outputs/{$run->user_id}/{$run->output_path}/{$filename}";
        Storage::disk('local')->put($path, $content);
    }

    /**
     * Read output file for a stage.
     */
    public function readOutput(PipelineRun $run, string $filename): ?string
    {
        $path = "outputs/{$run->user_id}/{$run->output_path}/{$filename}";

        return Storage::disk('local')->exists($path)
            ? Storage::disk('local')->get($path)
            : null;
    }

    /**
     * Get all output files for a run (only those that exist).
     */
    public function getOutputFiles(PipelineRun $run): array
    {
        $files = [];
        foreach (self::STAGES as $def) {
            $path = "outputs/{$run->user_id}/{$run->output_path}/{$def['file']}";
            if (Storage::disk('local')->exists($path)) {
                $files[] = [
                    'filename' => $def['file'],
                    'name' => $def['name'],
                    'size' => Storage::disk('local')->size($path),
                ];
            }
        }

        return $files;
    }

    /**
     * Get stage by number from a run.
     */
    public function getStage(PipelineRun $run, string $stageNumber): ?PipelineStage
    {
        return $run->stages()->where('stage_number', $stageNumber)->first();
    }
}
