<?php

namespace App\Http\Controllers;

use App\Jobs\DigitalProducts\ContentJob;
use App\Jobs\DigitalProducts\PublishPackJob;
use App\Jobs\DigitalProducts\ResearchJob;
use App\Jobs\DigitalProducts\StructureJob;
use App\Models\DigitalProduct;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $allTypes = implode(',', array_keys(DigitalProduct::productTypes()));

        $data = $request->validate([
            'product_type' => ['required', 'string', "in:{$allTypes}"],
            'niche' => ['required', 'string', 'max:500'],
            'buyer_description' => ['required', 'string', 'max:1000'],
            'buyer_problem' => ['required', 'string', 'max:1000'],
            'voice_profile_id' => ['nullable', 'integer', 'exists:voice_profiles,id'],
            // Original types
            'prompt_count' => ['nullable', 'integer', 'in:30,50,75'],
            'categories' => ['nullable', 'string', 'max:500'],
            'business_type' => ['nullable', 'string', 'max:200'],
            'sop_count' => ['nullable', 'integer', 'in:10,20,30'],
            'key_areas' => ['nullable', 'string', 'max:500'],
            'sequence_types' => ['nullable', 'string', 'max:500'],
            'sequence_count' => ['nullable', 'integer', 'in:5,10,15'],
            'emails_per_sequence' => ['nullable', 'integer', 'in:3,5,7'],
            // Content Calendar System
            'platforms' => ['nullable', 'array'],
            'platforms.*' => ['nullable', 'string', 'max:50'],
            'posting_frequency' => ['nullable', 'string', 'in:3,5,7,Daily'],
            'content_goals' => ['nullable', 'array'],
            'content_goals.*' => ['nullable', 'string', 'max:50'],
            'tone' => ['nullable', 'string', 'max:100'],
            // Excel Tracker
            'tracker_type' => ['nullable', 'string', 'max:100'],
            'team_size' => ['nullable', 'string', 'max:50'],
            // Notion OS
            'client_count' => ['nullable', 'string', 'max:20'],
            'organise_areas' => ['nullable', 'array'],
            'organise_areas.*' => ['nullable', 'string', 'max:50'],
            'current_tools' => ['nullable', 'string', 'max:500'],
            // Website Copy Pack + Brand Messaging + Sales Funnel
            'business_name' => ['nullable', 'string', 'max:200'],
            'founder_name' => ['nullable', 'string', 'max:200'],
            'differentiator' => ['nullable', 'string', 'max:1000'],
            'service_count' => ['nullable', 'integer', 'in:1,2,3,4,5'],
            'service_names' => ['nullable', 'string', 'max:500'],
            'business_location' => ['nullable', 'string', 'max:200'],
            'core_values' => ['nullable', 'string', 'max:500'],
            'personality_words' => ['nullable', 'string', 'max:200'],
            'avoid_sounding_like' => ['nullable', 'string', 'max:200'],
            'price_point' => ['nullable', 'string', 'max:100'],
            'tried_before' => ['nullable', 'string', 'max:500'],
            'needs_upsell' => ['nullable', 'string', 'in:Yes,No'],
            'needs_webinar' => ['nullable', 'string', 'in:Yes,No'],
            // Niche Research Report
            'geography' => ['nullable', 'string', 'max:100'],
            'report_depth' => ['nullable', 'string', 'max:50'],
            'competitors' => ['nullable', 'string', 'max:500'],
            'specific_questions' => ['nullable', 'string', 'max:1000'],
            // Buyer Persona Pack
            'persona_count' => ['nullable', 'integer', 'in:3,4,5'],
            'persona_types' => ['nullable', 'string', 'max:500'],
        ]);

        $voiceId = $data['voice_profile_id'] ?? null;
        if ($voiceId && ! auth()->user()->voiceProfiles()->where('id', $voiceId)->exists()) {
            $voiceId = null;
        }
        if (! $voiceId) {
            $voiceId = optional(auth()->user()->defaultVoiceProfile())->id;
        }

        $briefOptions = match ($data['product_type']) {
            // ── Original types ────────────────────────────────────────────
            'prompt_library' => [
                'prompt_count' => $data['prompt_count'] ?? 50,
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
            // ── Extended types ────────────────────────────────────────────
            'content_calendar_system' => [
                'platforms' => $data['platforms'] ?? ['Instagram', 'LinkedIn'],
                'posting_frequency' => $data['posting_frequency'] ?? '5',
                'content_goals' => $data['content_goals'] ?? ['Brand awareness', 'Authority'],
                'tone' => $data['tone'] ?? 'Educational',
            ],
            'excel_tracker' => [
                'tracker_type' => $data['tracker_type'] ?? 'Client tracker',
                'team_size' => $data['team_size'] ?? 'Just me',
            ],
            'notion_business_os' => [
                'team_size' => $data['team_size'] ?? 'Solo',
                'client_count' => $data['client_count'] ?? '1-5',
                'organise_areas' => $data['organise_areas'] ?? ['Clients', 'Projects', 'Finance'],
                'current_tools' => $data['current_tools'] ?? '',
            ],
            'website_copy_pack' => [
                'business_name' => $data['business_name'] ?? $data['niche'],
                'differentiator' => $data['differentiator'] ?? '',
                'tone' => $data['tone'] ?? 'Friendly and warm',
                'service_count' => $data['service_count'] ?? 2,
                'service_names' => $data['service_names'] ?? '',
                'business_location' => $data['business_location'] ?? '',
            ],
            'brand_messaging_system' => [
                'business_name' => $data['business_name'] ?? $data['niche'],
                'founder_name' => $data['founder_name'] ?? '',
                'differentiator' => $data['differentiator'] ?? '',
                'core_values' => $data['core_values'] ?? '',
                'personality_words' => $data['personality_words'] ?? '',
                'avoid_sounding_like' => $data['avoid_sounding_like'] ?? '',
                'price_point' => $data['price_point'] ?? '',
            ],
            'sales_funnel_copy' => [
                'price_point' => $data['price_point'] ?? '',
                'tried_before' => $data['tried_before'] ?? '',
                'differentiator' => $data['differentiator'] ?? '',
                'tone' => $data['tone'] ?? 'Friendly',
                'needs_upsell' => $data['needs_upsell'] ?? 'No',
                'needs_webinar' => $data['needs_webinar'] ?? 'No',
            ],
            'niche_research_report' => [
                'geography' => $data['geography'] ?? 'Nigeria',
                'report_depth' => $data['report_depth'] ?? 'Standard (20 pages)',
                'competitors' => $data['competitors'] ?? '',
                'specific_questions' => $data['specific_questions'] ?? '',
            ],
            'buyer_persona_pack' => [
                'price_point' => $data['price_point'] ?? '',
                'persona_count' => $data['persona_count'] ?? 3,
                'geography' => $data['geography'] ?? 'Nigeria',
                'persona_types' => $data['persona_types'] ?? '',
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

    public function export(DigitalProduct $digitalProduct, string $format, ExportService $export)
    {
        $this->authorise($digitalProduct);
        abort_unless(in_array($format, ['premium', 'kdp', 'master', 'xlsx']), 404);
        abort_if(! $digitalProduct->content_output, 404, 'Content not generated yet.');

        // XLSX only for types that support it
        if ($format === 'xlsx') {
            abort_unless(in_array($digitalProduct->product_type, DigitalProduct::xlsxTypes()), 404);
        }

        // KDP blocked for the 2 non-book types (excel_tracker, notion_business_os)
        if ($format === 'kdp') {
            abort_if(in_array($digitalProduct->product_type, ['excel_tracker', 'notion_business_os']), 404, 'KDP not available for this product type.');
        }

        $title = $digitalProduct->product_title ?: Str::title($digitalProduct->niche);
        $author = $digitalProduct->user->getSettings()->author_name ?: $digitalProduct->user->name;
        $subtitle = null;
        $tagline = $digitalProduct->structure_output['tagline'] ?? null;
        $sections = $this->parseSections($digitalProduct);

        $productTypeLabel = DigitalProduct::productTypes()[$digitalProduct->product_type]['name'] ?? null;

        $relDir = "users/{$digitalProduct->user_id}/digital-products/{$digitalProduct->id}";

        $absolute = match ($format) {
            'premium' => $export->exportDigitalProductPdf("{$relDir}/premium.pdf", $digitalProduct->product_type, $title, $author, $tagline, $sections, '#6C3CE1', $productTypeLabel),
            'kdp' => $export->exportKdpDocx("{$relDir}/kdp-version.docx", $title, $author, $subtitle, $sections),
            'master' => $export->exportMasterDocx("{$relDir}/master.docx", $title, $author, $subtitle, $sections),
            'xlsx' => match ($digitalProduct->product_type) {
                'content_calendar_system' => $export->exportContentCalendarXlsx("{$relDir}/content-calendar.xlsx", $title, $sections),
                'excel_tracker' => $export->exportExcelTrackerXlsx("{$relDir}/tracker.xlsx", $title, $sections, $digitalProduct->structure_output ?? []),
                default => abort(404),
            },
        };

        return response()->download($absolute, basename($absolute));
    }

    private function parseSections(DigitalProduct $product): array
    {
        $raw = (string) $product->content_output;

        // New extended types store content as JSON with a 'sections' key
        if (in_array($product->product_type, DigitalProduct::extendedTypes())) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && isset($decoded['sections'])) {
                return $decoded['sections'];
            }
        }

        // Original 3 types — plain text with CATEGORY/SOP/SEQUENCE headers
        $sections = [];
        if (preg_match_all('/^(CATEGORY|SOP|SEQUENCE)\s+\d+:\s*(.+)$/m', $raw, $matches, PREG_OFFSET_CAPTURE)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $title = trim($matches[2][$i][0]);
                $start = $matches[0][$i][1] + strlen($matches[0][$i][0]);
                $end = $i + 1 < $count ? $matches[0][$i + 1][1] : strlen($raw);
                $body = trim(substr($raw, $start, $end - $start));
                $body = preg_replace("/^\s*-{3,}\s*\n/", '', $body);
                $sections[] = ['title' => $title, 'body' => $body];
            }
        }

        if (! $sections) {
            $sections[] = ['title' => $product->structure_output['product_title'] ?? 'Content', 'body' => $raw];
        }

        return $sections;
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
