<?php

namespace App\Http\Controllers;

use App\Jobs\CleanDraftSectionJob;
use App\Jobs\InferChapterOrderJob;
use App\Models\PipelineRun;
use App\Services\ClaudeService;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpWord\IOFactory;

class ConvertDraftController extends Controller
{
    public function index(): View
    {
        $recentConverts = auth()->user()->runs()
            ->where('creation_mode', 'converted')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('convert.index', compact('recentConverts'));
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'files' => ['required', 'array', 'min:1', 'max:20'],
            'files.*' => ['file', 'max:51200', 'mimes:txt,md,docx'],
        ]);

        $user = auth()->user();
        $settings = $user->getSettings();

        if (! $settings->api_key_encrypted) {
            return redirect()->route('settings.index')
                ->with('toast', ['message' => 'Add your Claude API key in Settings first.', 'type' => 'warning']);
        }

        $uploadedFiles = [];
        $allFilenames = [];

        foreach ($request->file('files') as $file) {
            $name = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            $content = $ext === 'docx'
                ? $this->extractDocx($file->getRealPath())
                : (string) file_get_contents($file->getRealPath());

            if (! trim($content)) {
                continue;
            }

            $uploadedFiles[] = [
                'filename' => $name,
                'content' => $content,
                'word_count' => str_word_count(strip_tags($content)),
            ];
            $allFilenames[] = $name;
        }

        if (empty($uploadedFiles)) {
            return back()->with('toast', ['message' => 'All uploaded files appear empty.', 'type' => 'warning']);
        }

        $topic = trim($request->title);
        $slug = Str::slug($topic);
        $existing = $user->runs()->where('slug', 'like', $slug.'%')->count();
        if ($existing > 0) {
            $slug = $slug.'-'.($existing + 1);
        }

        // For single file: keep legacy raw_uploaded_content flow but also populate uploaded_files
        $isSingle = count($uploadedFiles) === 1;

        $run = $user->runs()->create([
            'topic' => $topic,
            'slug' => $slug,
            'status' => 'running',
            'output_path' => $slug,
            'creation_mode' => 'converted',
            'original_filename' => implode(', ', $allFilenames),
            'raw_uploaded_content' => $isSingle ? $uploadedFiles[0]['content'] : null,
            'uploaded_files' => $uploadedFiles,
        ]);

        if ($isSingle) {
            // Single file: go straight to cleaning (existing behaviour)
            $sections = $this->splitIntoSections($uploadedFiles[0]['content']);
            $total = count($sections);
            foreach ($sections as $i => $section) {
                CleanDraftSectionJob::dispatch($run->id, $i, $total, $section)
                    ->delay(now()->addSeconds($i * 2));
            }

            return redirect()->route('convert.review', $run)
                ->with('toast', ['message' => "Uploaded. Cleaning {$total} sections now.", 'type' => 'success']);
        }

        // Multiple files: infer order first, then show order-confirm page
        InferChapterOrderJob::dispatch($run->id);

        return redirect()->route('convert.order', $run)
            ->with('toast', ['message' => count($uploadedFiles).' files uploaded. Claude is inferring the chapter order...', 'type' => 'success']);
    }

    public function orderPage(PipelineRun $pipelineRun): View
    {
        $this->authorise($pipelineRun);

        return view('convert.order', ['run' => $pipelineRun]);
    }

    public function confirmOrder(Request $request, PipelineRun $pipelineRun): RedirectResponse
    {
        $this->authorise($pipelineRun);

        $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['string'],
        ]);

        $confirmedFilenames = $request->order; // ordered list of filenames
        $files = collect($pipelineRun->uploaded_files ?? []);

        // Build merged raw content in confirmed order
        $merged = collect($confirmedFilenames)->map(function ($fname) use ($files) {
            $file = $files->firstWhere('filename', $fname);
            if (! $file) {
                return null;
            }

            return "# {$fname}\n\n".$file['content'];
        })->filter()->implode("\n\n---\n\n");

        $pipelineRun->update([
            'confirmed_order' => $confirmedFilenames,
            'raw_uploaded_content' => $merged,
            'status' => 'running',
            'progress_note' => 'Starting cleaning...',
        ]);

        // Dispatch cleaning jobs on merged content
        $sections = $this->splitIntoSections($merged);
        $total = count($sections);
        foreach ($sections as $i => $section) {
            CleanDraftSectionJob::dispatch($pipelineRun->id, $i, $total, $section)
                ->delay(now()->addSeconds($i * 2));
        }

        return redirect()->route('convert.review', $pipelineRun)
            ->with('toast', ['message' => "Order confirmed. Cleaning {$total} sections now.", 'type' => 'success']);
    }

    public function review(PipelineRun $pipelineRun): View
    {
        $this->authorise($pipelineRun);

        return view('convert.review', ['run' => $pipelineRun]);
    }

    public function status(PipelineRun $pipelineRun): JsonResponse
    {
        $this->authorise($pipelineRun);

        return response()->json([
            'status' => $pipelineRun->status,
            'progress_note' => $pipelineRun->progress_note,
            'cleaning_approved' => $pipelineRun->cleaning_approved,
            'has_cleaned' => (bool) $pipelineRun->cleaned_content,
        ]);
    }

    public function approve(Request $request, PipelineRun $pipelineRun): RedirectResponse
    {
        $this->authorise($pipelineRun);

        $request->validate([
            'cleaned_content' => ['required', 'string'],
        ]);

        $pipelineRun->update([
            'cleaned_content' => $request->cleaned_content,
            'cleaning_approved' => true,
            'status' => 'completed',
            'progress_note' => null,
            'completed_at' => now(),
        ]);

        return redirect()->route('convert.listing', $pipelineRun)
            ->with('toast', ['message' => 'Draft approved. Generating your KDP listing.', 'type' => 'success']);
    }

    public function listing(PipelineRun $pipelineRun): View
    {
        $this->authorise($pipelineRun);

        // Auto-generate listing if not yet done
        if (! $pipelineRun->convert_listing && $pipelineRun->cleaned_content) {
            $this->generateListing($pipelineRun);
            $pipelineRun->refresh();
        }

        return view('convert.listing', ['run' => $pipelineRun]);
    }

    public function launch(PipelineRun $pipelineRun): View
    {
        $this->authorise($pipelineRun);

        if (! $pipelineRun->convert_launch && $pipelineRun->cleaned_content) {
            $this->generateLaunch($pipelineRun);
            $pipelineRun->refresh();
        }

        return view('convert.launch', ['run' => $pipelineRun]);
    }

    public function export(PipelineRun $pipelineRun, string $format, ExportService $export)
    {
        $this->authorise($pipelineRun);
        abort_unless(in_array($format, ['premium', 'kdp', 'master']), 404);
        abort_if(! $pipelineRun->cleaned_content, 404, 'No cleaned content yet.');

        $chapters = $this->buildChapters($pipelineRun->cleaned_content);
        $title = Str::title($pipelineRun->topic);
        $author = $pipelineRun->user->getSettings()->author_name ?: $pipelineRun->user->name;
        $relDir = "users/{$pipelineRun->user_id}/converts/{$pipelineRun->id}";

        $absolute = match ($format) {
            'premium' => $export->exportPremiumPdf("{$relDir}/premium.pdf", $title, $author, null, $chapters, '#6C3CE1', 'Kindle Ebook'),
            'kdp' => $export->exportKdpDocx("{$relDir}/kdp-version.docx", $title, $author, null, $chapters),
            'master' => $export->exportMasterDocx("{$relDir}/master.docx", $title, $author, null, $chapters),
        };

        return response()->download($absolute, basename($absolute));
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function authorise(PipelineRun $run): void
    {
        abort_unless($run->user_id === auth()->id(), 403);
    }

    private function splitIntoSections(string $text): array
    {
        $text = preg_replace("/\r\n|\r/", "\n", $text);

        // Try splitting by chapter/section headings
        $parts = preg_split('/\n(?=(?:chapter|section|part)\s+\d+|#+\s)/i', $text);
        if (is_array($parts) && count($parts) > 1) {
            $parts = array_filter(array_map('trim', $parts), fn ($p) => strlen($p) > 50);
            if (count($parts) >= 2) {
                return array_values($parts);
            }
        }

        // Fallback: split by double blank lines into ~1500-char chunks
        $chunks = preg_split('/\n{3,}/', $text);
        $sections = [];
        $current = '';

        foreach ((array) $chunks as $chunk) {
            $current .= "\n\n".trim($chunk);
            if (mb_strlen($current) >= 1500) {
                $sections[] = trim($current);
                $current = '';
            }
        }
        if (trim($current)) {
            $sections[] = trim($current);
        }

        return $sections ?: [trim($text)];
    }

    private function buildChapters(string $cleaned): array
    {
        $sections = preg_split('/\n{2,}(?=(?:chapter|section|part)\s+\d+|#+\s)/i', $cleaned);
        $chapters = [];

        foreach ((array) $sections as $i => $section) {
            $lines = explode("\n", ltrim($section), 2);
            $heading = trim($lines[0]);
            $body = isset($lines[1]) ? trim($lines[1]) : '';

            if (! $body) {
                $body = $heading;
                $heading = 'Chapter '.($i + 1);
            }

            $chapters[] = ['title' => $heading, 'body' => $body];
        }

        return $chapters ?: [['title' => Str::title(auth()->user()?->name ?? 'Draft'), 'body' => $cleaned]];
    }

    private function generateListing(PipelineRun $run): void
    {
        try {
            $settings = $run->user->getSettings();
            $claude = new ClaudeService($settings);

            $preview = mb_substr((string) $run->cleaned_content, 0, 2000);

            $system = 'You are an Amazon KDP listing specialist. Generate a complete book listing from the manuscript excerpt provided.';
            $user = "Book title: {$run->topic}\n\nManuscript excerpt:\n{$preview}\n\n"
                ."Return JSON only:\n"
                .'{"title":"...","subtitle":"...","description":"150-200 word Amazon description",'
                .'"keywords":["kw1","kw2","kw3","kw4","kw5","kw6","kw7"],'
                .'"categories":["Category 1","Category 2"],'
                .'"kindle_price":9.99,"paperback_price":14.99}';

            $raw = $claude->complete($system, $user);
            $raw = preg_replace('/```json|```/', '', $raw);
            $decoded = json_decode(trim($raw), true);

            if (is_array($decoded)) {
                $run->update(['convert_listing' => $decoded]);
            }
        } catch (\Throwable $e) {
            // Non-fatal — listing is optional
        }
    }

    private function generateLaunch(PipelineRun $run): void
    {
        try {
            $settings = $run->user->getSettings();
            $claude = new ClaudeService($settings);

            $preview = mb_substr((string) $run->cleaned_content, 0, 1500);
            $listing = $run->convert_listing ?? [];

            $system = 'You are a launch copywriter for books. Write launch content based on the book provided.';
            $user = "Title: {$run->topic}\nDescription: ".($listing['description'] ?? '')."\nExcerpt:\n{$preview}\n\n"
                ."Return JSON only:\n"
                .'{"linkedin":"150-250 word launch post","twitter":"under 280 chars",'
                .'"instagram":"100-150 words + hashtags","email_subject":"launch email subject",'
                .'"email_body":"150-200 word launch email"}';

            $raw = $claude->complete($system, $user);
            $raw = preg_replace('/```json|```/', '', $raw);
            $decoded = json_decode(trim($raw), true);

            if (is_array($decoded)) {
                $run->update(['convert_launch' => $decoded]);
            }
        } catch (\Throwable $e) {
            // Non-fatal
        }
    }

    private function extractDocx(string $path): string
    {
        try {
            $doc = IOFactory::load($path);
            $text = '';
            foreach ($doc->getSections() as $section) {
                foreach ($section->getElements() as $el) {
                    if (method_exists($el, 'getText')) {
                        $text .= $el->getText()."\n";
                    } elseif (method_exists($el, 'getElements')) {
                        foreach ($el->getElements() as $sub) {
                            if (method_exists($sub, 'getText')) {
                                $text .= $sub->getText()."\n";
                            }
                        }
                    }
                }
            }

            return $text;
        } catch (\Throwable $e) {
            return '';
        }
    }
}
