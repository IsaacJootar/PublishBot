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
        return view('settings.index', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'author_name' => ['nullable', 'string', 'max:100'],
            'pen_name' => ['nullable', 'string', 'max:100'],
            'anthropic_api_key' => ['nullable', 'string', 'max:200'],
            'openai_api_key' => ['nullable', 'string', 'max:200'],
        ]);

        $user = auth()->user();

        $data = [
            'author_name' => $request->author_name,
            'pen_name' => $request->pen_name,
        ];

        if ($request->filled('anthropic_api_key')) {
            $data['anthropic_api_key'] = $request->anthropic_api_key;
        }

        if ($request->filled('openai_api_key')) {
            $data['openai_api_key'] = $request->openai_api_key;
        }

        $user->update($data);

        return redirect()->route('settings.index')
            ->with('toast', ['message' => 'Settings saved successfully.', 'type' => 'success']);
    }

    public function testConnection(): JsonResponse
    {
        $user = auth()->user();

        if (! $user->anthropic_api_key) {
            return response()->json([
                'success' => false,
                'message' => 'No API key found. Add your Claude API key and save first.',
            ]);
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $user->anthropic_api_key,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                'model' => config('services.anthropic.model', 'claude-sonnet-4-6'),
                'max_tokens' => 10,
                'messages' => [
                    ['role' => 'user', 'content' => 'Say hello. Reply with one word only.'],
                ],
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Claude API connected successfully.',
                ]);
            }

            $error = $response->json('error.message', 'Connection failed.');

            return response()->json([
                'success' => false,
                'message' => 'Could not connect to Claude. '.$error,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection timed out. Check your internet connection and try again.',
            ]);
        }
    }
}
