<?php

namespace App\Jobs;

use App\Models\PipelineRun;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class InferChapterOrderJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 2;

    public function __construct(public int $runId) {}

    public function handle(): void
    {
        $run = PipelineRun::find($this->runId);
        if (! $run) {
            return;
        }

        $files = $run->uploaded_files ?? [];
        if (empty($files)) {
            return;
        }

        $run->update(['progress_note' => 'Analysing file order...']);

        $fileSummary = collect($files)->map(function ($f, $i) {
            $preview = mb_substr((string) ($f['content'] ?? ''), 0, 300);
            $words = $f['word_count'] ?? 0;

            return 'File '.($i + 1).": {$f['filename']} ({$words} words)\nFirst 300 chars: {$preview}";
        })->implode("\n\n---\n\n");

        $system = <<<'TXT'
You are analysing a set of uploaded manuscript files to determine the correct reading order.
Look at:
- Filenames (numbers, "chapter", "intro", "conclusion", "part", "appendix" etc.)
- Opening sentences of each file
- Logical narrative or structural flow

Return JSON only. Be confident — make a clear decision.
TXT;

        $user = "Book title: {$run->topic}\n\n"
            ."These files were uploaded together as one manuscript:\n\n"
            .$fileSummary."\n\n"
            ."Determine the correct reading order. Return JSON:\n"
            .'{"order": ['
            .'{"filename": "exact-filename.txt", "title": "Chapter or section title inferred from content", "position": 1, "reason": "one sentence why this goes here"}'
            .',...],'
            .'"summary": "One sentence describing how you determined the order"}';

        try {
            $settings = $run->user->getSettings();
            $claude = new ClaudeService($settings);
            $raw = $claude->complete($system, $user);
            $raw = trim(preg_replace('/```json|```/', '', $raw));
            $decoded = json_decode($raw, true);

            if (is_array($decoded) && ! empty($decoded['order'])) {
                $run->update([
                    'inferred_order' => $decoded,
                    'status' => 'paused',
                    'progress_note' => 'Order inferred — please confirm before cleaning.',
                ]);
            } else {
                // Fallback: use filename sort order
                $fallback = collect($files)->sortBy('filename')->values()->map(fn ($f, $i) => [
                    'filename' => $f['filename'],
                    'title' => $f['filename'],
                    'position' => $i + 1,
                    'reason' => 'Sorted alphabetically by filename.',
                ])->all();

                $run->update([
                    'inferred_order' => ['order' => $fallback, 'summary' => 'Sorted alphabetically by filename.'],
                    'status' => 'paused',
                    'progress_note' => 'Order inferred — please confirm before cleaning.',
                ]);
            }
        } catch (Throwable $e) {
            // Fallback gracefully
            $fallback = collect($files)->map(fn ($f, $i) => [
                'filename' => $f['filename'],
                'title' => $f['filename'],
                'position' => $i + 1,
                'reason' => 'Could not infer order — using upload order.',
            ])->all();

            $run->update([
                'inferred_order' => ['order' => $fallback, 'summary' => 'Using upload order (order inference failed).'],
                'status' => 'paused',
                'progress_note' => 'Review file order below.',
            ]);
        }
    }
}
