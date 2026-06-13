<?php

namespace App\Http\Controllers;

use App\Jobs\DigitalProducts\ContentJob;
use App\Jobs\DigitalProducts\PublishPackJob;
use App\Jobs\DigitalProducts\ResearchJob;
use App\Jobs\DigitalProducts\StructureJob;
use App\Models\DigitalProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DigitalProductController extends Controller
{
    public function index(): View
    {
        $products = auth()->user()->digitalProducts()
            ->where('is_archived', false)
            ->orderByDesc('updated_at')
            ->get();

        return view('digital-products.index', [
            'products' => $products,
            'types' => DigitalProduct::productTypes(),
        ]);
    }

    public function create(string $type): View
    {
        abort_unless(array_key_exists($type, DigitalProduct::productTypes()), 404);

        $voices = auth()->user()->voiceProfiles()->orderByDesc('is_default')->orderBy('name')->get();

        return view('digital-products.create', [
            'type' => $type,
            'meta' => DigitalProduct::productTypes()[$type],
            'voices' => $voices,
            'defaultVoiceId' => optional($voices->firstWhere('is_default', true))->id,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_type' => ['required', 'string', 'in:prompt_library,sop_pack,email_sequence_vault'],
            'niche' => ['required', 'string', 'max:200'],
            'buyer_description' => ['required', 'string', 'max:500'],
            'buyer_problem' => ['required', 'string', 'max:500'],
            'voice_profile_id' => ['nullable', 'integer', 'exists:voice_profiles,id'],
            // Prompt library
            'prompt_count' => ['nullable', 'integer', 'in:30,50,75'],
            'categories' => ['nullable', 'string', 'max:500'],
            // SOP pack
            'business_type' => ['nullable', 'string', 'max:200'],
            'sop_count' => ['nullable', 'integer', 'in:10,20,30'],
            'key_areas' => ['nullable', 'string', 'max:500'],
            // Email vault
            'sequence_types' => ['nullable', 'string', 'max:500'],
            'sequence_count' => ['nullable', 'integer', 'in:5,10,15'],
            'emails_per_sequence' => ['nullable', 'integer', 'in:3,5,7'],
        ]);

        $voiceId = $data['voice_profile_id'] ?? null;
        if ($voiceId && ! auth()->user()->voiceProfiles()->where('id', $voiceId)->exists()) {
            $voiceId = null;
        }
        if (! $voiceId) {
            $voiceId = optional(auth()->user()->defaultVoiceProfile())->id;
        }

        $briefOptions = match ($data['product_type']) {
            'prompt_library' => [
                'prompt_count' => $data['prompt_count'] ?? 30,
                'categories' => $data['categories'] ?? '',
            ],
            'sop_pack' => [
                'business_type' => $data['business_type'] ?? $data['niche'],
                'sop_count' => $data['sop_count'] ?? 10,
                'key_areas' => $data['key_areas'] ?? '',
            ],
            'email_sequence_vault' => [
                'sequence_types' => $data['sequence_types'] ?? '',
                'sequence_count' => $data['sequence_count'] ?? 5,
                'emails_per_sequence' => $data['emails_per_sequence'] ?? 5,
            ],
        };

        $product = auth()->user()->digitalProducts()->create([
            'voice_profile_id' => $voiceId,
            'product_type' => $data['product_type'],
            'niche' => $data['niche'],
            'buyer_description' => $data['buyer_description'],
            'buyer_problem' => $data['buyer_problem'],
            'brief_options' => $briefOptions,
            'current_stage' => 1,
            'status' => 'researching',
        ]);

        ResearchJob::dispatch($product->id);

        return redirect()->route('digital-products.show', $product)
            ->with('toast', ['message' => 'Brief saved. Claude is researching your niche.', 'type' => 'success']);
    }

    public function show(DigitalProduct $digitalProduct): View
    {
        $this->authorise($digitalProduct);

        return view('digital-products.show', ['product' => $digitalProduct]);
    }

    public function status(DigitalProduct $digitalProduct): JsonResponse
    {
        $this->authorise($digitalProduct);

        return response()->json([
            'status' => $digitalProduct->status,
            'current_stage' => $digitalProduct->current_stage,
            'progress_note' => $digitalProduct->progress_note,
            'error_message' => $digitalProduct->error_message,
        ]);
    }

    public function advance(DigitalProduct $digitalProduct): RedirectResponse
    {
        $this->authorise($digitalProduct);

        $next = match ($digitalProduct->current_stage) {
            2 => StructureJob::class,    // research done → build structure
            3 => ContentJob::class,      // structure done → write content
            4 => PublishPackJob::class,  // content done → build pack
            default => null,
        };

        if (! $next) {
            return back()->with('toast', ['message' => 'No next stage.', 'type' => 'warning']);
        }

        $statusMap = [
            'App\Jobs\DigitalProducts\StructureJob' => 'structuring',
            'App\Jobs\DigitalProducts\ContentJob' => 'writing',
            'App\Jobs\DigitalProducts\PublishPackJob' => 'publishing',
        ];

        $digitalProduct->update(['status' => $statusMap[$next] ?? 'running', 'error_message' => null]);
        $next::dispatch($digitalProduct->id);

        return back()->with('toast', ['message' => 'Next stage started.', 'type' => 'success']);
    }

    public function regenerate(DigitalProduct $digitalProduct, int $stage): RedirectResponse
    {
        $this->authorise($digitalProduct);

        $job = match ($stage) {
            2 => ResearchJob::class,
            3 => StructureJob::class,
            4 => ContentJob::class,
            5 => PublishPackJob::class,
            default => null,
        };

        if (! $job) {
            return back()->with('toast', ['message' => 'Invalid stage.', 'type' => 'warning']);
        }

        $job::dispatch($digitalProduct->id);

        return back()->with('toast', ['message' => 'Regenerating...', 'type' => 'success']);
    }

    public function editField(Request $request, DigitalProduct $digitalProduct): JsonResponse
    {
        $this->authorise($digitalProduct);

        $data = $request->validate([
            'field' => ['required', 'string', 'in:product_title,structure_output,content_output,publish_pack'],
            'value' => ['required'],
        ]);

        $digitalProduct->update([$data['field'] => $data['value']]);

        return response()->json(['ok' => true]);
    }

    public function download(DigitalProduct $digitalProduct): Response
    {
        $this->authorise($digitalProduct);

        $text = "PRODUCT: {$digitalProduct->product_title}\n"
            ."TYPE: {$digitalProduct->product_type}\n"
            ."NICHE: {$digitalProduct->niche}\n"
            .'GENERATED: '.optional($digitalProduct->updated_at)->toDateTimeString()."\n"
            .str_repeat('=', 60)."\n\n"
            .'--- RESEARCH ---'."\n"
            .json_encode($digitalProduct->research_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n\n"
            .'--- STRUCTURE ---'."\n"
            .json_encode($digitalProduct->structure_output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n\n"
            .'--- CONTENT ---'."\n"
            .$digitalProduct->content_output."\n\n"
            .'--- PUBLISH PACK ---'."\n"
            .json_encode($digitalProduct->publish_pack, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n";

        $slug = Str::slug($digitalProduct->product_title ?: $digitalProduct->niche);

        return response($text, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$slug}.txt\"",
        ]);
    }

    public function destroy(DigitalProduct $digitalProduct): RedirectResponse
    {
        $this->authorise($digitalProduct);

        $digitalProduct->delete();

        return redirect()->route('digital-products.index')
            ->with('toast', ['message' => 'Product deleted.', 'type' => 'success']);
    }

    private function authorise(DigitalProduct $product): void
    {
        abort_unless($product->user_id === auth()->id(), 403);
    }
}
