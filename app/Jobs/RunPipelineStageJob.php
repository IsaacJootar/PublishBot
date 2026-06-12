<?php

namespace App\Jobs;

use App\Models\PipelineRun;
use App\Services\ClaudeService;
use App\Services\PipelineService;
use App\Services\StageHandlers\Stage1Handler;
use App\Services\StageHandlers\Stage2Handler;
use App\Services\StageHandlers\Stage3aHandler;
use App\Services\StageHandlers\Stage3bHandler;
use App\Services\StageHandlers\Stage4Handler;
use App\Services\StageHandlers\Stage5Handler;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunPipelineStageJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public int $tries = 1;

    public function __construct(
        public PipelineRun $run,
        public string $stageNumber
    ) {}

    public function handle(PipelineService $pipeline): void
    {
        $stage = $pipeline->getStage($this->run, $this->stageNumber);

        if (! $stage) {
            Log::error("Stage {$this->stageNumber} not found for run {$this->run->id}");

            return;
        }

        // Mark stage as running
        $stage->update(['status' => 'running', 'started_at' => now()]);
        $this->run->update(['current_stage' => (int) $this->stageNumber, 'status' => 'running']);

        try {
            $settings = $this->run->user->getSettings();
            $claude = new ClaudeService($settings);

            $handler = match ($this->stageNumber) {
                '1' => new Stage1Handler($claude, $pipeline),
                '2' => new Stage2Handler($claude, $pipeline),
                '3a' => new Stage3aHandler($claude, $pipeline),
                '3b' => new Stage3bHandler($claude, $pipeline),
                '4' => new Stage4Handler($claude, $pipeline),
                '5' => new Stage5Handler($claude, $pipeline),
                default => throw new \Exception("Unknown stage: {$this->stageNumber}"),
            };

            $handler->handle($this->run, $stage);

            $stage->update(['status' => 'completed', 'completed_at' => now(), 'error_message' => null]);

            $pipeline->dispatchNext($this->run, $this->stageNumber);

        } catch (\Exception $e) {
            Log::error("Stage {$this->stageNumber} failed for run {$this->run->id}", [
                'error' => $e->getMessage(),
            ]);

            $stage->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $this->run->update(['status' => 'failed']);
        }
    }
}
