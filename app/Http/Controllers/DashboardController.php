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

        return view('dashboard', compact('stats', 'recent', 'quickTopics', 'settings'));
    }

    public function run(Request $request, PipelineService $pipeline): RedirectResponse
    {
        $request->validate([
            'topic' => ['required', 'string', 'max:200'],
        ]);

        $user = auth()->user();

        if (! $user->getSettings()->api_key_encrypted) {
            return redirect()->route('settings.index')
                ->with('toast', ['message' => 'Add your API key in Settings before running a pipeline.', 'type' => 'warning']);
        }

        $topic = trim($request->topic);
        $slug = Str::slug($topic);
        $existing = $user->runs()->where('slug', 'like', $slug.'%')->count();

        if ($existing > 0) {
            $slug = $slug.'-'.($existing + 1);
        }

        $run = $user->runs()->create([
            'topic' => $topic,
            'slug' => $slug,
            'status' => 'pending',
            'output_path' => $slug,
        ]);

        $pipeline->start($run);

        return redirect()->route('runs.show', $run);
    }
}
