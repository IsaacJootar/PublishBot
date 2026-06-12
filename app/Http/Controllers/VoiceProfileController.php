<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessVoiceProfileJob;
use App\Models\VoiceProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class VoiceProfileController extends Controller
{
    public function index(): View
    {
        $profiles = auth()->user()->voiceProfiles()->orderByDesc('is_default')->orderBy('name')->get();

        return view('voice.index', compact('profiles'));
    }

    public function create(): View
    {
        return view('voice.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'domain_description' => ['nullable', 'string', 'max:500'],
            'color' => ['required', 'string'],
            'emoji' => ['required', 'string', 'max:10'],
            'is_default' => ['boolean'],
        ]);

        if ($request->boolean('is_default')) {
            auth()->user()->voiceProfiles()->update(['is_default' => false]);
        }

        $profile = auth()->user()->voiceProfiles()->create([
            'name' => $request->name,
            'domain' => str($request->name)->slug()->value(),
            'domain_description' => $request->domain_description,
            'color' => $request->color,
            'emoji' => $request->emoji,
            'is_default' => $request->boolean('is_default'),
        ]);

        return redirect()->route('voice.train', $profile)
            ->with('toast', ['message' => 'Domain created. Now upload your writing samples.', 'type' => 'success']);
    }

    public function train(VoiceProfile $voiceProfile): View
    {
        $this->authorise($voiceProfile);

        return view('voice.train', ['profile' => $voiceProfile]);
    }

    public function upload(Request $request, VoiceProfile $voiceProfile): RedirectResponse
    {
        $this->authorise($voiceProfile);

        $request->validate([
            'samples' => ['required', 'array', 'min:1'],
            'samples.*' => ['file', 'mimes:txt,md,docx', 'max:5120'],
        ]);

        $rawContent = $voiceProfile->raw_content ?? '';

        foreach ($request->file('samples') as $file) {
            $rawContent .= "\n\n".$this->extractText($file);
        }

        $wordCount = str_word_count($rawContent);

        $voiceProfile->update([
            'raw_content' => $rawContent,
            'word_count' => $wordCount,
        ]);

        ProcessVoiceProfileJob::dispatch($voiceProfile);

        return redirect()->route('voice.train', $voiceProfile)
            ->with('toast', ['message' => 'Extracting your writing style... this takes about 30 seconds.', 'type' => 'info']);
    }

    public function approve(Request $request, VoiceProfile $voiceProfile): RedirectResponse
    {
        $this->authorise($voiceProfile);

        $request->validate([
            'extracted_style' => ['required', 'string'],
        ]);

        $voiceProfile->update(['extracted_style' => $request->extracted_style]);

        return redirect()->route('voice.index')
            ->with('toast', ['message' => 'Voice profile saved.', 'type' => 'success']);
    }

    public function setDefault(VoiceProfile $voiceProfile): RedirectResponse
    {
        $this->authorise($voiceProfile);

        auth()->user()->voiceProfiles()->update(['is_default' => false]);
        $voiceProfile->update(['is_default' => true]);

        return back()->with('toast', ['message' => "{$voiceProfile->name} set as your default voice.", 'type' => 'success']);
    }

    public function destroy(VoiceProfile $voiceProfile): RedirectResponse
    {
        $this->authorise($voiceProfile);
        $voiceProfile->delete();

        return redirect()->route('voice.index')
            ->with('toast', ['message' => 'Domain profile deleted.', 'type' => 'success']);
    }

    private function authorise(VoiceProfile $profile): void
    {
        abort_if($profile->user_id !== auth()->id(), 403);
    }

    private function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'docx') {
            // Basic docx text extraction — read XML content
            try {
                $zip = new \ZipArchive;
                if ($zip->open($file->getRealPath()) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();
                    $text = strip_tags(str_replace(['</w:p>', '</w:r>'], ["\n", ' '], $xml));

                    return preg_replace('/\s+/', ' ', $text);
                }
            } catch (\Exception) {
                // Fall through to plain text read
            }
        }

        return file_get_contents($file->getRealPath());
    }
}
