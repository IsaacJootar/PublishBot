<?php

namespace App\Http\Controllers;

use App\Services\PipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $settings = $user->getSettings();

        $stats = [
            'runs' => $user->runs()->count(),
            'completed' => $user->runs()->where('status', 'completed')->count(),
            'revenue' => $user->sales()
                ->whereMonth('sale_date', now()->month)
                ->whereYear('sale_date', now()->year)
                ->sum('amount'),
            'currency' => $user->sales()->latest()->value('currency') ?? 'USD',
        ];

        $recent = $user->runs()->orderByDesc('created_at')->limit(5)->get();
        $quickTopics = $settings->quick_topics ?? [];
        $voices = $user->voiceProfiles()->orderByDesc('is_default')->orderBy('name')->get();
        $defaultVoiceId = optional($voices->firstWhere('is_default', true))->id;
        $seriesList = $user->series()->orderBy('name')->get();

        return view('dashboard', compact('stats', 'recent', 'quickTopics', 'settings', 'voices', 'defaultVoiceId', 'seriesList'));
    }

    public function run(Request $request, PipelineService $pipeline): RedirectResponse
    {
        $request->validate([
            'topic' => ['required', 'string', 'max:200'],
            'voice_profile_id' => ['nullable', 'integer', 'exists:voice_profiles,id'],
            'series_id' => ['nullable', 'integer', 'exists:series,id'],
        ]);

        $user = auth()->user();

        $settings = $user->getSettings();
        if (! $settings->api_key_encrypted) {
            return redirect()->route('settings.index')
                ->with('toast', ['message' => 'Add your Claude API key in Settings first.', 'type' => 'warning']);
        }

        $topic = trim($request->topic);
        $slug = Str::slug($topic);
        $existing = $user->runs()->where('slug', 'like', $slug.'%')->count();

        if ($existing > 0) {
            $slug = $slug.'-'.($existing + 1);
        }

        $voiceProfileId = $request->voice_profile_id;
        if ($voiceProfileId) {
            // Ensure it's the user's own
            $owns = $user->voiceProfiles()->where('id', $voiceProfileId)->exists();
            if (! $owns) {
                $voiceProfileId = null;
            }
        }
        if (! $voiceProfileId) {
            $voiceProfileId = optional($user->defaultVoiceProfile())->id;
        }

        $seriesId = $request->series_id;
        if ($seriesId && ! $user->series()->where('id', $seriesId)->exists()) {
            $seriesId = null;
        }

        $run = $user->runs()->create([
            'topic' => $topic,
            'slug' => $slug,
            'status' => 'pending',
            'output_path' => $slug,
            'voice_profile_id' => $voiceProfileId,
            'series_id' => $seriesId,
        ]);

        $pipeline->start($run);

        return redirect()->route('runs.show', $run);
    }
}
