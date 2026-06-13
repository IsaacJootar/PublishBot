<?php

namespace App\Http\Controllers;

use App\Jobs\RunPipelineStageJob;
use App\Models\PipelineRun;
use App\Services\PipelineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use ZipArchive;

class RunController extends Controller
{
    public function index(): View
    {
        $runs = auth()->user()->runs()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('runs.index', compact('runs'));
    }

    public function show(PipelineRun $pipelineRun): View
    {
        $this->authorise($pipelineRun);
        $pipelineRun->load('stages');

        return view('runs.show', ['run' => $pipelineRun]);
    }

    /** Polled by the run detail page every 3 seconds. */
    public function status(PipelineRun $pipelineRun): JsonResponse
    {
        $this->authorise($pipelineRun);
        $pipelineRun->load('stages');

        return response()->json([
            'status' => $pipelineRun->status,
            'validation_result' => $pipelineRun->validation_result,
            'validation_report' => $pipelineRun->validation_report,
            'user_confirmed_continue' => $pipelineRun->user_confirmed_continue,
            'stages' => $pipelineRun->stages->map(fn ($s) => [
                'stage_number' => $s->stage_number,
                'stage_name' => $s->stage_name,
                'status' => $s->status,
                'progress_note' => $s->progress_note,
                'output_filename' => $s->output_filename,
                'error_message' => $s->error_message,
            ]),
        ]);
    }

    /** User clicks Continue after reviewing Stage 1. */
    public function continue(PipelineRun $pipelineRun, PipelineService $pipeline): JsonResponse
    {
        $this->authorise($pipelineRun);

        if ($pipelineRun->status !== 'paused') {
            return response()->json(['ok' => false, 'message' => 'Run is not paused.']);
        }

        $pipeline->continue($pipelineRun);

        return response()->json(['ok' => true]);
    }

    /** Retry a failed stage. */
    public function retry(PipelineRun $pipelineRun, string $stageNumber): JsonResponse
    {
        $this->authorise($pipelineRun);

        $stage = $pipelineRun->stages()->where('stage_number', $stageNumber)->first();

        if (! $stage || $stage->status !== 'failed') {
            return response()->json(['ok' => false, 'message' => 'Stage not found or not failed.']);
        }

        $stage->update(['status' => 'pending', 'error_message' => null]);
        $pipelineRun->update(['status' => 'running']);

        RunPipelineStageJob::dispatch($pipelineRun, $stageNumber);

        return response()->json(['ok' => true]);
    }

    /** In-browser file viewer or direct download if ?download=1 */
    public function file(Request $request, PipelineRun $pipelineRun, string $filename): Response|View
    {
        $this->authorise($pipelineRun);

        $allowed = array_column(PipelineService::STAGES, 'file');
        abort_if(! in_array($filename, $allowed), 404);

        $path = "outputs/{$pipelineRun->user_id}/{$pipelineRun->output_path}/{$filename}";
        abort_if(! Storage::disk('local')->exists($path), 404);

        $content = Storage::disk('local')->get($path);

        if ($request->boolean('download')) {
            return response($content, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        return view('runs.file', [
            'run' => $pipelineRun,
            'filename' => $filename,
            'content' => $content,
            'wordCount' => str_word_count($content),
            'charCount' => strlen($content),
        ]);
    }

    /** Download all output files as a zip. */
    public function downloadAll(PipelineRun $pipelineRun)
    {
        $this->authorise($pipelineRun);

        $zipPath = sys_get_temp_dir().'/'."{$pipelineRun->slug}.zip";
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (PipelineService::STAGES as $def) {
            $path = "outputs/{$pipelineRun->user_id}/{$pipelineRun->output_path}/{$def['file']}";
            if (Storage::disk('local')->exists($path)) {
                $zip->addFromString($def['file'], Storage::disk('local')->get($path));
            }
        }

        $zip->close();

        return response()->download($zipPath, "{$pipelineRun->slug}.zip")->deleteFileAfterSend();
    }

    /** Re-run: create a new pipeline run for the same topic. */
    public function rerun(PipelineRun $pipelineRun, PipelineService $pipeline): RedirectResponse
    {
        $this->authorise($pipelineRun);

        $user = auth()->user();
        $existing = $user->runs()->where('slug', 'like', $pipelineRun->slug.'%')->count();
        $newSlug = $pipelineRun->slug.'-'.($existing + 1);

        $newRun = $user->runs()->create([
            'topic' => $pipelineRun->topic,
            'slug' => $newSlug,
            'status' => 'pending',
            'output_path' => $newSlug,
        ]);

        $pipeline->start($newRun);

        return redirect()->route('runs.show', $newRun)
            ->with('toast', ['message' => 'New pipeline started.', 'type' => 'success']);
    }

    public function destroy(PipelineRun $pipelineRun): RedirectResponse
    {
        $this->authorise($pipelineRun);

        // Delete output files
        $dir = "outputs/{$pipelineRun->user_id}/{$pipelineRun->output_path}";
        if (Storage::disk('local')->exists($dir)) {
            Storage::disk('local')->deleteDirectory($dir);
        }

        $pipelineRun->delete();

        return redirect()->route('runs.index')
            ->with('toast', ['message' => 'Run deleted.', 'type' => 'success']);
    }

    private function authorise(PipelineRun $run): void
    {
        abort_if($run->user_id !== auth()->id(), 403);
    }
}
