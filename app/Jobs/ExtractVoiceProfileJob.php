<?php

namespace App\Jobs;

use App\Models\VoiceProfile;
use App\Services\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ExtractVoiceProfileJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public int $tries = 2;

    public function __construct(public int $voiceProfileId) {}

    public function handle(): void
    {
        $profile = VoiceProfile::find($this->voiceProfileId);
        if (! $profile) {
            return;
        }

        $profile->update(['status' => 'training', 'error_message' => null]);

        $settings = $profile->user->getSettings();
        $claude = new ClaudeService($settings);

        $system = <<<'TXT'
You are a writing style analyst. Your job is to study a person's writing
and produce a detailed, reusable voice profile that can be used to generate
new content that sounds exactly like them.

Be specific and concrete. Not "writes informally" but "uses contractions
constantly, drops subjects from sentences (writes 'Can't believe it' not
'I can't believe it'), and opens paragraphs with a one-line statement
followed by a longer explanation."

Output the profile in clean sections. This will be injected into future
AI prompts, so it must be practical and actionable — not a literary critique.
TXT;

        $user = "Domain: {$profile->name}\n"
            ."Domain description: {$profile->description}\n"
            .'(This person writes about this domain. Use that context when analysing '
            ."their vocabulary, tone, and style choices.)\n\n"
            ."Study this writing carefully. Extract a detailed voice profile.\n\n"
            ."--- WRITING SAMPLE ---\n"
            .$profile->raw_content."\n"
            ."--- END SAMPLE ---\n\n"
            ."Return a voice profile with these exact sections:\n\n"
            ."## TONE AND ENERGY\n"
            ."## SENTENCE STYLE\n"
            ."## VOCABULARY\n"
            ."## HOW THEY OPEN IDEAS\n"
            ."## HOW THEY CLOSE IDEAS\n"
            ."## WHAT THEY NEVER DO\n"
            ."## SIGNATURE PATTERNS\n"
            ."## ONE-PARAGRAPH SUMMARY\n\n"
            .'Plain text. No JSON. No markdown code blocks.';

        try {
            $extracted = $claude->complete($system, $user);

            $profile->update([
                'extracted_style' => $extracted,
                'status' => 'ready',
            ]);
        } catch (Throwable $e) {
            $profile->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
