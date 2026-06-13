<?php

namespace App\Http\Controllers;

use App\Jobs\ExtractVoiceProfileJob;
use App\Models\VoiceProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpWord\IOFactory;

class VoiceProfileController extends Controller
{
    public function index(): View
    {
        $profiles = auth()->user()->voiceProfiles()->orderByDesc('is_default')->orderBy('name')->get();

        return view('voice.index', [
            'profiles' => $profiles,
            'suggested' => VoiceProfile::suggestedDomains(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'emoji' => ['nullable', 'string', 'max:8'],
            'color' => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:300'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = (bool) ($data['is_default'] ?? false);

        if ($isDefault) {
            auth()->user()->voiceProfiles()->update(['is_default' => false]);
        }

        if (! auth()->user()->voiceProfiles()->exists()) {
            $isDefault = true;
        }

        $profile = auth()->user()->voiceProfiles()->create([
            'name' => $data['name'],
            'emoji' => $data['emoji'] ?: '✍️',
            'color' => $data['color'] ?: '#6C3CE1',
            'description' => $data['description'] ?? null,
            'is_default' => $isDefault,
            'status' => 'draft',
        ]);

        return redirect()->route('voice.show', $profile)
            ->with('toast', ['message' => 'Domain created. Now add some writing samples.', 'type' => 'success']);
    }

    public function show(VoiceProfile $voiceProfile): View
    {
        $this->authorise($voiceProfile);

        return view('voice.show', ['profile' => $voiceProfile]);
    }

    public function update(Request $request, VoiceProfile $voiceProfile): RedirectResponse
    {
        $this->authorise($voiceProfile);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'emoji' => ['nullable', 'string', 'max:8'],
            'color' => ['nullable', 'string', 'max:16'],
            'description' => ['nullable', 'string', 'max:300'],
        ]);

        $voiceProfile->update($data);

        return back()->with('toast', ['message' => 'Profile updated.', 'type' => 'success']);
    }

    public function train(Request $request, VoiceProfile $voiceProfile): RedirectResponse
    {
        $this->authorise($voiceProfile);

        $request->validate([
            'samples.*' => ['file', 'max:5120', 'mimes:txt,md,docx'],
            'pasted_text' => ['nullable', 'string'],
        ]);

        $newContent = '';

        if ($request->hasFile('samples')) {
            foreach ($request->file('samples') as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                if ($ext === 'docx') {
                    $newContent .= $this->extractDocxText($file->getRealPath())."\n\n";
                } else {
                    $newContent .= file_get_contents($file->getRealPath())."\n\n";
                }
            }
        }

        if ($pasted = trim((string) $request->pasted_text)) {
            $newContent .= $pasted."\n\n";
        }

        if (! $newContent) {
            return back()->with('toast', ['message' => 'No content uploaded. Add files or paste some text.', 'type' => 'warning']);
        }

        $combined = trim(($voiceProfile->raw_content ?? '')."\n\n".$newContent);
        $words = str_word_count(strip_tags($combined));

        $voiceProfile->update([
            'raw_content' => $combined,
            'word_count' => $words,
        ]);

        return back()->with('toast', ['message' => "Added {$words} total words. Click Extract to train.", 'type' => 'success']);
    }

    public function extract(VoiceProfile $voiceProfile): RedirectResponse
    {
        $this->authorise($voiceProfile);

        if (! $voiceProfile->raw_content) {
            return back()->with('toast', ['message' => 'No content to extract from. Upload samples first.', 'type' => 'warning']);
        }

        ExtractVoiceProfileJob::dispatch($voiceProfile->id);

        return back()->with('toast', ['message' => 'Training started. This usually takes 30–60 seconds.', 'type' => 'success']);
    }

    public function status(VoiceProfile $voiceProfile): JsonResponse
    {
        $this->authorise($voiceProfile);

        return response()->json([
            'status' => $voiceProfile->status,
            'word_count' => $voiceProfile->word_count,
            'has_style' => (bool) $voiceProfile->extracted_style,
            'error_message' => $voiceProfile->error_message,
        ]);
    }

    public function setDefault(VoiceProfile $voiceProfile): RedirectResponse
    {
        $this->authorise($voiceProfile);

        DB::transaction(function () use ($voiceProfile) {
            auth()->user()->voiceProfiles()->update(['is_default' => false]);
            $voiceProfile->update(['is_default' => true]);
        });

        return back()->with('toast', ['message' => "{$voiceProfile->name} is now your default voice.", 'type' => 'success']);
    }

    public function destroy(VoiceProfile $voiceProfile): RedirectResponse
    {
        $this->authorise($voiceProfile);

        $voiceProfile->delete();

        return redirect()->route('voice.index')
            ->with('toast', ['message' => 'Profile deleted.', 'type' => 'success']);
    }

    private function authorise(VoiceProfile $profile): void
    {
        abort_unless($profile->user_id === auth()->id(), 403);
    }

    private function extractDocxText(string $path): string
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
