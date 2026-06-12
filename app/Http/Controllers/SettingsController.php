<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $settings = auth()->user()->getSettings();

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'api_key' => ['nullable', 'string', 'max:300'],
            'model' => ['required', 'string'],
            'max_tokens' => ['required', 'integer', 'min:500', 'max:8000'],
            'chapter_count' => ['required', 'integer', 'min:6', 'max:12'],
            'words_per_chapter' => ['required', 'integer', 'min:300', 'max:800'],
            'pin_count' => ['required', 'integer', 'min:10', 'max:25'],
            'default_tone' => ['nullable', 'string', 'max:200'],
            'author_name' => ['nullable', 'string', 'max:100'],
            'quick_topics' => ['nullable', 'string'],
        ]);

        $settings = auth()->user()->getSettings();

        $data = [
            'model' => $request->model,
            'max_tokens' => $request->max_tokens,
            'chapter_count' => $request->chapter_count,
            'words_per_chapter' => $request->words_per_chapter,
            'pin_count' => $request->pin_count,
            'default_tone' => $request->default_tone,
            'author_name' => $request->author_name,
            'quick_topics' => array_filter(
                array_map('trim', explode(',', $request->quick_topics ?? '')),
                fn ($t) => $t !== ''
            ),
        ];

        if ($request->filled('api_key')) {
            $data['api_key_encrypted'] = $request->api_key;
        }

        $settings->update($data);

        return redirect()->route('settings.index')
            ->with('toast', ['message' => 'Settings saved.', 'type' => 'success']);
    }

    public function testConnection(): JsonResponse
    {
        $settings = auth()->user()->getSettings();

        if (! $settings->api_key_encrypted) {
            return response()->json([
                'success' => false,
                'message' => 'No API key saved. Add your key and save first.',
            ]);
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $settings->api_key_encrypted,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
                'model' => $settings->model,
                'max_tokens' => 10,
                'messages' => [['role' => 'user', 'content' => 'Hi']],
            ]);

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Connected. Claude is ready.']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Connection failed: '.$response->json('error.message', 'Unknown error'),
            ]);
        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'message' => 'Connection timed out. Check your internet and try again.',
            ]);
        }
    }
}
