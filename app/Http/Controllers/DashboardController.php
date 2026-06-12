<?php

namespace App\Http\Controllers;

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

        $recent = $user->runs()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $quickTopics = $settings->quick_topics ?? [];

        return view('dashboard', compact('stats', 'recent', 'quickTopics', 'settings'));
    }

    public function run(Request $request): RedirectResponse
    {
        $request->validate([
            'topic' => ['required', 'string', 'max:200'],
        ]);

        $topic = trim($request->topic);
        $slug = Str::slug($topic);

        // Append version if slug already used by this user
        $existing = auth()->user()->runs()->where('slug', 'like', $slug.'%')->count();
        if ($existing > 0) {
            $slug = $slug.'-'.($existing + 1);
        }

        $run = auth()->user()->runs()->create([
            'topic' => $topic,
            'slug' => $slug,
            'status' => 'pending',
            'output_path' => $slug,
        ]);

        return redirect()->route('runs.show', $run);
    }
}
