<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voice_profile_id',
        'product_type',
        'niche',
        'product_title',
        'buyer_description',
        'buyer_problem',
        'brief_options',
        'current_stage',
        'status',
        'research_output',
        'structure_output',
        'content_output',
        'publish_pack',
        'recommended_platform',
        'recommended_price',
        'error_message',
        'progress_note',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'brief_options' => 'array',
            'research_output' => 'array',
            'structure_output' => 'array',
            'publish_pack' => 'array',
            'current_stage' => 'integer',
            'recommended_price' => 'decimal:2',
            'is_archived' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voiceProfile(): BelongsTo
    {
        return $this->belongsTo(VoiceProfile::class);
    }

    /** All 11 product types with display metadata. */
    public static function productTypes(): array
    {
        return [
            // ── Tier 1 — Original types ───────────────────────────────────
            'prompt_library' => [
                'emoji' => '📦',
                'name' => 'Prompt Library',
                'tagline' => 'Curated AI prompts for a specific niche',
                'price' => '$17 – $47',
                'time' => '~45 mins',
                'tier' => 1,
                'badge' => null,
            ],
            'sop_pack' => [
                'emoji' => '📋',
                'name' => 'SOP Pack',
                'tagline' => 'Standard operating procedures for a business',
                'price' => '$97 – $297',
                'time' => '~60 mins',
                'tier' => 1,
                'badge' => null,
            ],
            'email_sequence_vault' => [
                'emoji' => '📧',
                'name' => 'Email Sequence Vault',
                'tagline' => 'Complete email libraries for a specific niche',
                'price' => '$47 – $197',
                'time' => '~60 mins',
                'tier' => 1,
                'badge' => null,
            ],
            // ── Tier 2 — Systems and Tools ────────────────────────────────
            'content_calendar_system' => [
                'emoji' => '📅',
                'name' => 'Content Calendar System',
                'tagline' => '90-day content plan — ready to post',
                'price' => '$27 – $97',
                'time' => '~30 mins',
                'tier' => 2,
                'badge' => null,
                'exports' => ['pdf', 'docx', 'xlsx'],
            ],
            'excel_tracker' => [
                'emoji' => '📊',
                'name' => 'Excel / Google Sheets Tracker',
                'tagline' => 'Professional tracker — no expensive software',
                'price' => '$47 – $197',
                'time' => '~30 mins',
                'tier' => 2,
                'badge' => null,
                'exports' => ['pdf', 'xlsx'],
                'kdp' => false,
            ],
            'notion_business_os' => [
                'emoji' => '🗂️',
                'name' => 'Notion Business OS',
                'tagline' => 'Your whole business in one Notion workspace',
                'price' => '$97 – $297',
                'time' => '~45 mins',
                'tier' => 2,
                'badge' => null,
                'exports' => ['pdf', 'docx'],
                'kdp' => false,
                'notion_special' => true,
            ],
            // ── Tier 3 — Done-For-You Copy ────────────────────────────────
            'website_copy_pack' => [
                'emoji' => '🌐',
                'name' => 'Website Copy Pack',
                'tagline' => 'Every word for your website — ready to paste',
                'price' => '$197 – $497',
                'time' => '~60 mins',
                'tier' => 3,
                'badge' => 'Premium',
                'badge_color' => 'amber',
                'exports' => ['pdf', 'docx', 'kdp'],
                'fiverr_tip' => true,
            ],
            'brand_messaging_system' => [
                'emoji' => '🎯',
                'name' => 'Brand Messaging System',
                'tagline' => 'Voice, taglines, pitches — your full brand bible',
                'price' => '$297 – $797',
                'time' => '~60 mins',
                'tier' => 3,
                'badge' => 'Premium',
                'badge_color' => 'amber',
                'exports' => ['pdf', 'docx', 'kdp'],
                'fiverr_tip' => true,
            ],
            'sales_funnel_copy' => [
                'emoji' => '📧',
                'name' => 'Sales Funnel Copy Pack',
                'tagline' => 'Landing page, emails, sales page — full funnel',
                'price' => '$197 – $597',
                'time' => '~60 mins',
                'tier' => 3,
                'badge' => 'Premium',
                'badge_color' => 'amber',
                'exports' => ['pdf', 'docx', 'kdp'],
                'fiverr_tip' => true,
            ],
            // ── Tier 4 — Research and Intelligence ───────────────────────
            'niche_research_report' => [
                'emoji' => '🔍',
                'name' => 'Niche Research Report',
                'tagline' => 'Deep market intelligence — ready to act on',
                'price' => '$97 – $497',
                'time' => '~45 mins',
                'tier' => 4,
                'badge' => 'Intelligence',
                'badge_color' => 'blue',
                'exports' => ['pdf', 'docx', 'kdp'],
            ],
            'buyer_persona_pack' => [
                'emoji' => '👤',
                'name' => 'Buyer Persona Pack',
                'tagline' => 'Detailed personas — know your buyer completely',
                'price' => '$47 – $197',
                'time' => '~30 mins',
                'tier' => 4,
                'badge' => 'Intelligence',
                'badge_color' => 'blue',
                'exports' => ['pdf', 'docx', 'kdp'],
            ],
        ];
    }

    /** Types that show a Fiverr tip panel on Stage 5. */
    public static function fiverrTipTypes(): array
    {
        return ['website_copy_pack', 'brand_messaging_system', 'sales_funnel_copy'];
    }

    /** Types that get a KDP DOCX export. */
    public static function kdpTypes(): array
    {
        return [
            'content_calendar_system',
            'website_copy_pack',
            'brand_messaging_system',
            'sales_funnel_copy',
            'niche_research_report',
            'buyer_persona_pack',
        ];
    }

    /** Types that get an XLSX export. */
    public static function xlsxTypes(): array
    {
        return ['content_calendar_system', 'excel_tracker'];
    }

    /** Types with Notion-specific handling. */
    public static function notionTypes(): array
    {
        return ['notion_business_os'];
    }

    /** New extended types added in Phase EP-01. */
    public static function extendedTypes(): array
    {
        return [
            'content_calendar_system',
            'excel_tracker',
            'notion_business_os',
            'website_copy_pack',
            'brand_messaging_system',
            'sales_funnel_copy',
            'niche_research_report',
            'buyer_persona_pack',
        ];
    }

    public function typeMeta(): array
    {
        return self::productTypes()[$this->product_type] ?? [];
    }

    public function stageLabels(): array
    {
        return [
            1 => 'Brief',
            2 => 'Research',
            3 => 'Structure',
            4 => 'Content',
            5 => 'Publish Pack',
        ];
    }
}
