<?php

namespace App\Jobs;

use App\Models\PipelineRun;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Cleans one section of an uploaded draft via Claude.
 * Dispatched once per section; final job assembles cleaned_content.
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

        $system = <<<'TXT'
You are a professional manuscript editor. Your job is to clean and polish
an uploaded draft without changing the author's voice, ideas, or meaning.

Do:
- Fix grammar, spelling, and punctuation errors
- Improve sentence flow where it is genuinely broken
- Remove duplicate blank lines or excessive whitespace
- Preserve every paragraph break the author wrote
- Keep all headings exactly as the author wrote them
- Return the cleaned text only — no preamble, no commentary

Do NOT:
- Add new content the author did not write
- Remove ideas, arguments, or paragraphs
- Change the author's word choices unnecessarily
- Rewrite in a different style
TXT;

        $user = "Clean this section of the manuscript:\n\n".$this->sectionText."\n\nReturn only the cleaned text.";

        try {
            $cleaned = $claude->complete($system, $user);
        } catch (Throwable $e) {
            // Fall back to original text on error — do not fail the whole run
            $cleaned = $this->sectionText;
        }

        // Append this section to cleaned_content atomically
        $run->refresh();
        $existing = $run->cleaned_content ?? '';
        $separator = $existing ? "\n\n" : '';
        $run->update(['cleaned_content' => $existing.$separator.$cleaned]);

        // If this is the last section, mark as ready for review
        if ($this->sectionIndex + 1 >= $this->totalSections) {
            $run->update([
                'status' => 'paused',
                'progress_note' => 'Cleaning complete — review and approve below.',
            ]);
        }
    }
}
