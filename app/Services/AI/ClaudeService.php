<?php

namespace App\Services\AI;

use App\Models\VoiceProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ClaudeService
{
    public function generate(string $systemPrompt, string $userPrompt): string
    {
        try {
            return $this->callClaude($systemPrompt, $userPrompt);
        } catch (\Exception $claudeError) {
            Log::warning('Claude API failed, falling back to OpenAI', [
                'error' => $claudeError->getMessage(),
            ]);

            try {
                return $this->callOpenAI($systemPrompt, $userPrompt);
            } catch (\Exception $openaiError) {
                Log::error('Both AI APIs failed', [
                    'claude_error' => $claudeError->getMessage(),
                    'openai_error' => $openaiError->getMessage(),
                ]);

                throw new \Exception('Generation failed. Check your API keys in Settings.');
            }
        }
    }

    public function buildSystemPrompt(string $base, ?string $voiceProfile = null): string
    {
        if (! $voiceProfile) {
            return $base."\n\nWrite in a clear, direct, practical tone. "
                .'Short sentences. No fluff. No filler phrases.';
        }

        return $base
            ."\n\n--- AUTHOR VOICE PROFILE ---\n"
            .$voiceProfile
            ."\n--- END VOICE PROFILE ---\n\n"
            ."CRITICAL: Write in this author's exact voice. "
            .'Match their sentence length, vocabulary, tone, energy, and style. '
            .'Sound like they personally wrote every word. '
            .'Do not sound like a generic AI.';
    }

    public function buildSystemPromptWithDomain(string $base, ?VoiceProfile $profile = null): string
    {
        if (! $profile || ! $profile->extracted_style) {
            return $this->buildSystemPrompt($base, null);
        }

        $domainContext = $profile->domain_description
            ? "This content is in the domain of: {$profile->domain_description}."
            : '';

        $voiceSection = "\n\n--- AUTHOR VOICE PROFILE: {$profile->name} ---\n"
            .$domainContext."\n"
            .$profile->extracted_style
            ."\n--- END VOICE PROFILE ---\n\n"
            ."Write in this author's exact voice for this domain. "
            .'Sound like they personally wrote this. Not like an AI.';

        return $base.$voiceSection;
    }

    private function callClaude(string $system, string $user): string
    {
        $apiKey = $this->resolveApiKey();

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model', 'claude-sonnet-4-6'),
            'max_tokens' => 4096,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $user],
            ],
        ]);

        if (! $response->successful()) {
            throw new \Exception(
                'Claude API error: '.$response->json('error.message', 'Unknown error')
            );
        }

        return $response->json('content.0.text', '');
    }

    private function callOpenAI(string $system, string $user): string
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'max_tokens' => 4096,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ]);

        return $response->choices[0]->message->content;
    }

    private function resolveApiKey(): string
    {
        // Prefer the logged-in user's key, fall back to env
        $userKey = auth()->check() ? auth()->user()->anthropic_api_key : null;
        $key = $userKey ?: config('services.anthropic.api_key');

        if (! $key) {
            throw new \Exception('No API key found. Add your Claude API key in Settings.');
        }

        return $key;
    }
}
