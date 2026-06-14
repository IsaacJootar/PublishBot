<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Phase EP-01 — Extended Digital Products
 *
 * Registers 8 new product_type values:
 *   content_calendar_system, excel_tracker, notion_business_os,
 *   website_copy_pack, brand_messaging_system, sales_funnel_copy,
 *   niche_research_report, buyer_persona_pack
 *
 * The product_type column is varchar(255) which accepts any string.
 * On PostgreSQL (production) we add a CHECK constraint so the DB
 * enforces the allowed values. SQLite ignores CHECK constraints
 * added via ALTER TABLE but the application validates at form level.
 */
return new class extends Migration
{
    /** All allowed product_type values after this migration. */
    public const ALLOWED_TYPES = [
        'prompt_library',
        'sop_pack',
        'email_sequence_vault',
        'content_calendar_system',
        'excel_tracker',
        'notion_business_os',
        'website_copy_pack',
        'brand_messaging_system',
        'sales_funnel_copy',
        'niche_research_report',
        'buyer_persona_pack',
    ];

    public function up(): void
    {
        // Only add the CHECK constraint on PostgreSQL — SQLite does not
        // enforce CHECK constraints added via ALTER TABLE.
        if (DB::getDriverName() === 'pgsql') {
            $types = implode("','", self::ALLOWED_TYPES);
            DB::statement(
                "ALTER TABLE digital_products
                 DROP CONSTRAINT IF EXISTS chk_product_type"
            );
            DB::statement(
                "ALTER TABLE digital_products
                 ADD CONSTRAINT chk_product_type
                 CHECK (product_type IN ('{$types}'))"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                'ALTER TABLE digital_products
                 DROP CONSTRAINT IF EXISTS chk_product_type'
            );
        }
    }
};
