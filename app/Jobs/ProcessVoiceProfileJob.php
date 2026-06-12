<?php

namespace App\Jobs;

use App\Models\VoiceProfile;
use App\Services\AI\ClaudeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessVoiceProfileJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 2;

    public function __construct(public VoiceProfile $voiceProfile) {}

    public function handle(ClaudeService $claude): void
    {
        $profile = $this->voiceProfile;

        $systemPrompt = 'You are a writing style analyst. Your job is to study a person\'s writing '
            .'and produce a detailed, reusable voice profile that can be used to generate '
            .'new content that sounds exactly like them. '
            .'Be specific and concrete. Not "writes informally" but precise patterns. '
            .'Output the profile in clean sections. This will be injected into future '
            .'AI prompts so it must be practical and actionable — not a literary critique.';

        $userPrompt = "Domain: {$profile->name}\n"
            ."Domain description: {$profile->domain_description}\n"
            ."(This person writes about this domain. Use that context when analysing their vocabulary, tone, and style choices.)\n\n"
            ."Study this writing carefully. Extract a detailed voice profile.\n\n"
            ."--- WRITING SAMPLE ---\n"
            .substr($profile->raw_content ?? '', 0, 12000)
            ."\n--- END SAMPLE ---\n\n"
            ."Return a voice profile with these exact sections:\n\n"
            ."## TONE AND ENERGY\n"
            ."How does this person sound? What's the overall feeling?\n\n"
            ."## SENTENCE STYLE\n"
            ."How long are their sentences? Any distinctive structural patterns?\n\n"
            ."## VOCABULARY\n"
            ."What level of language? Any favourite words or phrases? What do they avoid?\n\n"
            ."## HOW THEY OPEN IDEAS\n"
            ."How do they introduce a new point — questions, statements, stories, examples?\n\n"
            ."## HOW THEY CLOSE IDEAS\n"
            ."How do they end a section — summarise, challenge, inspire, or just move on?\n\n"
            ."## WHAT THEY NEVER DO\n"
            ."Things notably absent from their writing.\n\n"
            ."## SIGNATURE PATTERNS\n"
            ."2-5 specific recurring patterns that make their writing identifiable. Quote examples where possible.\n\n"
            ."## ONE-PARAGRAPH SUMMARY\n"
            .'A single paragraph capturing the essence of this voice. '
            .'Write it so an AI could read it and immediately know how to write like this person.';

        try {
            $extracted = $claude->generate($systemPrompt, $userPrompt);
            $profile->update(['extracted_style' => $extracted]);
        } catch (\Exception $e) {
            Log::error('ProcessVoiceProfileJob failed', [
                'profile_id' => $profile->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
