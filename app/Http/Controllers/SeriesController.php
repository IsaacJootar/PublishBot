<?php

namespace App\Http\Controllers;

use App\Models\PipelineRun;
use App\Models\Series;
use App\Services\PipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SeriesController extends Controller
{
    public function index(): View
    {
        $series = auth()->user()->series()
            ->withCount(['runs', 'runs as completed_runs_count' => fn ($q) => $q->where('status', 'completed')])
            ->orderByDesc('updated_at')
            ->get();

        return view('series.index', compact('series'));
    }

    public function create(): View
    {
        return view('series.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $series = auth()->user()->series()->create($data);

        return redirect()->route('series.show', $series)
            ->with('toast', ['message' => 'Series created. Now plan the first book or attach existing runs.', 'type' => 'success']);
    }

    public function show(Series $series): View
    {
        $this->authorise($series);

        $runs = $series->runs()->orderByDesc('created_at')->get();
        $orphanRuns = auth()->user()->runs()
            ->whereNull('series_id')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'topic', 'status']);

        return view('series.show', compact('series', 'runs', 'orphanRuns'));
    }

    public function edit(Series $series): View
    {
        $this->authorise($series);

        return view('series.edit', compact('series'));
    }

    public function update(Request $request, Series $series): RedirectResponse
    {
        $this->authorise($series);

        $series->update($this->validated($request));

        return redirect()->route('series.show', $series)
            ->with('toast', ['message' => 'Series updated.', 'type' => 'success']);
    }

    public function planNext(Series $series): View
    {
        $this->authorise($series);

        return view('series.plan-next', compact('series'));
    }

    public function storeNext(Request $request, Series $series, PipelineService $pipeline): RedirectResponse
    {
        $this->authorise($series);

        $request->validate(['topic' => ['required', 'string', 'max:200']]);

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

        $run = $user->runs()->create([
            'topic' => $topic,
            'slug' => $slug,
            'status' => 'pending',
            'output_path' => $slug,
            'series_id' => $series->id,
            'voice_profile_id' => optional($user->defaultVoiceProfile())->id,
        ]);

        $pipeline->start($run);

        return redirect()->route('runs.show', $run)
            ->with('toast', ['message' => "New book started in {$series->name}.", 'type' => 'success']);
    }

    public function attachRun(Series $series, PipelineRun $pipelineRun): RedirectResponse
    {
        $this->authorise($series);
        abort_if($pipelineRun->user_id !== auth()->id(), 403);

        $pipelineRun->update(['series_id' => $series->id]);

        return back()->with('toast', ['message' => 'Run attached to series.', 'type' => 'success']);
    }

    public function detachRun(Series $series, PipelineRun $pipelineRun): RedirectResponse
    {
        $this->authorise($series);
        abort_if($pipelineRun->user_id !== auth()->id(), 403);

        if ($pipelineRun->series_id === $series->id) {
            $pipelineRun->update(['series_id' => null]);
        }

        return back()->with('toast', ['message' => 'Run removed from series.', 'type' => 'success']);
    }

    public function destroy(Series $series): RedirectResponse
    {
        $this->authorise($series);

        $series->delete();

        return redirect()->route('series.index')
            ->with('toast', ['message' => 'Series deleted. Books inside it are kept.', 'type' => 'success']);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'emoji' => ['nullable', 'string', 'max:8'],
            'color' => ['nullable', 'string', 'max:16'],
            'format' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'art_style' => ['nullable', 'string', 'max:500'],
            'writing_tone' => ['nullable', 'string', 'max:500'],
            'recurring_themes' => ['nullable', 'string', 'max:500'],
            'never_do' => ['nullable', 'string', 'max:500'],
            'characters' => ['nullable', 'array'],
            'characters.*.name' => ['nullable', 'string', 'max:120'],
            'characters.*.age_appearance' => ['nullable', 'string', 'max:200'],
            'characters.*.personality' => ['nullable', 'string', 'max:200'],
            'characters.*.key_detail' => ['nullable', 'string', 'max:200'],
        ]);

        // Drop empty character rows
        if (! empty($data['characters'])) {
            $data['characters'] = array_values(array_filter(
                $data['characters'],
                fn ($c) => ! empty($c['name'])
            ));
        } else {
            $data['characters'] = [];
        }

        $data['emoji'] = ($data['emoji'] ?? '') ?: '📚';
        $data['color'] = ($data['color'] ?? '') ?: '#6C3CE1';

        return $data;
    }

    private function authorise(Series $series): void
    {
        abort_unless($series->user_id === auth()->id(), 403);
    }
}
