<?php

namespace App\Jobs;

use App\Models\PipelineRun;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Cleans one section of an uploaded draft via Claude.
 * Sections are stored keyed by index to prevent race-condition overwrites
 * when multiple jobs run in parallel. Final assembly happens when all
 * sections are present.
 */
class CleanDraftSectionJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public int $tries = 2;

    public function __construct(
        public int $runId,
        public int $sectionIndex,
        public int $totalSections,
        public string $sectionText
    ) {}

    public function handle(): void
    {
        $run = PipelineRun::find($this->runId);
        if (! $run || $run->creation_mode !== 'converted') {
            return;
        }

        $n = $this->sectionIndex + 1;
        $run->update(['progress_note' => "Cleaning section {$n} of {$this->totalSections}..."]);

        $settings = $run->user->getSettings();
        $claude = new ClaudeService($settings);

        $system = 'You are a professional manuscript editor. Your job is to clean and polish '
            ."an uploaded draft without changing the author's voice, ideas, or meaning.\n\n"
            ."Do:\n- Fix grammar, spelling, and punctuation errors\n"
            ."- Improve sentence flow where it is genuinely broken\n"
            ."- Remove duplicate blank lines or excessive whitespace\n"
            ."- Preserve every paragraph break the author wrote\n"
            ."- Keep all headings exactly as the author wrote them\n"
            ."- Return the cleaned text only — no preamble, no commentary\n\n"
            ."Do NOT:\n- Add new content the author did not write\n"
            ."- Remove ideas, arguments, or paragraphs\n"
            ."- Change the author's word choices unnecessarily\n"
            .'- Rewrite in a different style';

        $user = "Clean this section of the manuscript:\n\n".$this->sectionText."\n\nReturn only the cleaned text.";

        try {
            $cleaned = $claude->complete($system, $user);
        } catch (Throwable $e) {
            // Fall back to original text — never block the whole run
            $cleaned = $this->sectionText;
        }

        // Store each section keyed by index to avoid race-condition overwrites.
        // We use a JSON map {sectionIndex: cleanedText} until all sections arrive.
        $run->refresh();
        $existing = $run->cleaned_content ?? '';

        $store = json_decode($existing, true);
        if (! is_array($store)) {
            // First section or legacy plain-text — start fresh map
            $store = [];
        }

        $store[$this->sectionIndex] = $cleaned;

        // Assemble into final plain text when all sections are collected
        if (count($store) >= $this->totalSections) {
            ksort($store);
            $assembled = implode("\n\n", $store);
            $run->update([
                'cleaned_content' => $assembled,
                'status' => 'paused',
                'progress_note' => 'Cleaning complete — review and approve below.',
            ]);
        } else {
            $run->update([
                'cleaned_content' => json_encode($store),
                'progress_note' => "Cleaned {$n} of {$this->totalSections} sections...",
            ]);
        }
    }
}
