<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ClaudeService
{
    public function __construct(private UserSetting $settings) {}

    public function complete(string $system, string $user): string
    {
        return $this->call($system, $user, withWebSearch: false)
            ->json('content.0.text', '');
    }

    public function completeWithWebSearch(string $system, string $user): string
    {
        $response = $this->call($system, $user, withWebSearch: true);
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
            throw new \Exception(
                'No API key found. Add your Claude API key in Settings to run the pipeline.'
            );
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

        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'x-api-key' => $key,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->timeout(120)
            ->post('https://api.anthropic.com/v1/messages', $body);

        if (! $response->successful()) {
            throw new \Exception(
                'Claude API error: '.$response->json('error.message', 'Unknown error')
            );
        }

        return $response;
    }
}
