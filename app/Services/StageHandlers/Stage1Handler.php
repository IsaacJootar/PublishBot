<?php

namespace App\Services\StageHandlers;

use App\Models\PipelineRun;
use App\Models\PipelineStage;

class Stage1Handler extends BaseStageHandler
{
    public function handle(PipelineRun $run, PipelineStage $stage): void
    {
        $this->setProgress($stage, 'Searching Amazon and Etsy for live market data...');

        $system = 'You are a market research assistant for digital publishing. '
            .'You have access to live web search. Use it to find real, current data before forming your assessment. '
            .'Search Amazon and Etsy directly. Base your report on what you actually find, not on assumptions.';

        $user = "Research the current market demand for the following publishing topic: {$run->topic}\n\n"
            ."Step 1: Search Amazon for '{$run->topic} book' and note the top results, review counts, and price points.\n"
            ."Step 2: Search Etsy for '{$run->topic} printable' and note the top results, bestseller tags, and price points.\n"
            ."Step 3: Return a structured validation report containing:\n"
            ."- Amazon demand level: High / Medium / Low\n"
            ."- Amazon evidence: top 3 results found, their review counts, price range\n"
            ."- Etsy demand level: High / Medium / Low\n"
            ."- Etsy evidence: top 3 results found, bestseller presence, price range\n"
            ."- Competition assessment: is the market crowded or is there room to enter\n"
            ."- Three related topic angles that may perform better or fill a gap\n"
            .'- Overall recommendation: Go or No-Go with a clear one-paragraph reason';

        try {
            $report = $this->claude->completeWithWebSearch($system, $user);
        } catch (\Exception) {
            // Fallback to knowledge-based if web search fails
            $report = "FALLBACK — NO LIVE DATA\n\n"
                .$this->claude->complete($system.' Note: web search unavailable, using training knowledge only.', $user);
        }

        // Determine go/no_go from report
        $lower = strtolower($report);
        $result = str_contains($lower, 'no-go') || str_contains($lower, 'no go') ? 'no_go' : 'go';

        $run->update([
            'validation_report' => $report,
            'validation_result' => $result,
        ]);

        $this->pipeline->writeOutput($run, $stage->output_filename, $report);
    }
}
