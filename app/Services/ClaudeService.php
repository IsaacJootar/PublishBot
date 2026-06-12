<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    public function __construct(private UserSetting $settings) {}

    /**
     * Standard completion — tries Claude first, falls back to Groq (Llama) silently.
     */
    public function complete(string $system, string $user): string
    {
        if ($this->settings->api_key_encrypted) {
            try {
                return $this->callClaude($system, $user, withWebSearch: false);
            } catch (\Exception $e) {
                Log::warning('Claude failed, trying Groq fallback', ['error' => $e->getMessage()]);
            }
        }

        return $this->callGroq($system, $user);
    }

    /**
     * Web search — Stage 1 only. Falls back to Groq without web search.
     */
    public function completeWithWebSearch(string $system, string $user): string
    {
        if ($this->settings->api_key_encrypted) {
            try {
                $response = $this->callClaudeRaw($system, $user, withWebSearch: true);
                $blocks = $response->json('content', []);
                $text = '';
                foreach ($blocks as $block) {
                    if ($block['type'] === 'text') {
                        $text .= $block['text'];
                    }
                }

                return $text;
            } catch (\Exception $e) {
                Log::warning('Claude web search failed, falling back to Groq', ['error' => $e->getMessage()]);
            }
        }

        // Groq fallback — no live web search, clearly labeled
        $groqResult = $this->callGroq(
            $system.' NOTE: Live web search is unavailable. Use your training knowledge and clearly state this.',
            $user
        );

        return "FALLBACK — NO LIVE DATA (Groq/Llama used)\n\n".$groqResult;
    }

    private function callClaude(string $system, string $user, bool $withWebSearch): string
    {
        $response = $this->callClaudeRaw($system, $user, $withWebSearch);

        return $response->json('content.0.text', '');
    }

    private function callClaudeRaw(string $system, string $user, bool $withWebSearch): Response
    {
        $key = $this->settings->api_key_encrypted;

        if (! $key) {
            throw new \Exception('No Claude API key.');
        }

        $body = [
            'model' => $this->settings->model,
            'max_tokens' => $this->settings->max_tokens,
            'system' => $system,
            'messages' => [['role' => 'user', 'content' => $user]],
        ];

        if ($withWebSearch) {
            $body['tools'] = [[
                'type' => $this->settings->web_search_tool_version,
                'name' => 'web_search',
            ]];
        }

        $response = Http::withHeaders([
            'x-api-key' => $key,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(120)->post('https://api.anthropic.com/v1/messages', $body);

        if (! $response->successful()) {
            throw new \Exception('Claude API error: '.$response->json('error.message', 'Unknown'));
        }

        return $response;
    }

    private function callGroq(string $system, string $user): string
    {
        $key = $this->settings->groq_api_key;

        if (! $key) {
            throw new \Exception(
                'No API key available. Add your Claude key or Groq key in Settings. '.
                'Get a free Groq key at console.groq.com'
            );
        }

        $model = $this->settings->groq_model ?: 'llama-3.3-70b-versatile';
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$key,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => $model,
            'max_tokens' => $this->settings->max_tokens,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $user],
            ],
        ]);

        if (! $response->successful()) {
            throw new \Exception('Groq API error: '.$response->json('error.message', 'Unknown'));
        }

        return $response->json('choices.0.message.content', '');
    }
}
