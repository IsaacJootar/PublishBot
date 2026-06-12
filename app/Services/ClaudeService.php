<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ClaudeService
{
    public function __construct(private UserSetting $settings) {}

    /**
     * Standard completion — used for Stages 2–5.
     */
    public function complete(string $system, string $user): string
    {
        $response = $this->call($system, $user, withWebSearch: false);

        return $response->json('content.0.text', '');
    }

    /**
     * Web search completion — used for Stage 1 only.
     * Returns the full text including search results Claude synthesised.
     */
    public function completeWithWebSearch(string $system, string $user): string
    {
        $response = $this->call($system, $user, withWebSearch: true);

        // Collect all text blocks from the response (Claude may return multiple)
        $blocks = $response->json('content', []);
        $text = '';
        foreach ($blocks as $block) {
            if ($block['type'] === 'text') {
                $text .= $block['text'];
            }
        }

        return $text;
    }

    private function call(string $system, string $user, bool $withWebSearch): Response
    {
        $key = $this->settings->api_key_encrypted;

        if (! $key) {
            throw new \Exception('No API key saved. Add your key in Settings.');
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
        ])->timeout(120)->retry(3, 2000, fn ($e) => ! str_contains($e->getMessage(), 'invalid_api_key'))
            ->post('https://api.anthropic.com/v1/messages', $body);

        if (! $response->successful()) {
            $msg = $response->json('error.message', 'Unknown API error');
            throw new \Exception("Claude API error: {$msg}");
        }

        return $response;
    }
}
