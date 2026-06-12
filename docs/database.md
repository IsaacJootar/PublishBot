# PublishAI — Database Schema

## Database: SQLite (local) / PostgreSQL (production)
## ORM: Laravel Eloquent
## Migrations: database/migrations/

---

## Migration: create_users_table

Standard Laravel Breeze users table with API key fields added.

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->string('author_name')->nullable();        // name on books
    $table->string('pen_name')->nullable();           // pen name if different
    $table->string('anthropic_api_key')->nullable();  // encrypted
    $table->string('openai_api_key')->nullable();     // encrypted, fallback
    $table->boolean('onboarding_complete')->default(false);
    $table->rememberToken();
    $table->timestamps();
});
```

---

## Migration: create_voice_profiles_table

Each profile = one domain. User can have unlimited domain profiles.
One profile is marked as default. Any project can use any profile.

```php
Schema::create('voice_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');                           // e.g. "Social Media & Tech"
    $table->string('domain')->nullable();             // e.g. "social_algorithms"
    $table->text('domain_description')->nullable();   // what this domain covers
    $table->string('color')->default('#6C3CE1');      // UI colour tag per domain
    $table->string('emoji')->default('✍️');           // UI emoji per domain
    $table->longText('raw_content')->nullable();      // all uploaded text
    $table->longText('extracted_style')->nullable();  // AI-extracted style guide
    $table->integer('word_count')->default(0);
    $table->boolean('is_default')->default(false);    // one default profile
    $table->timestamps();
});
```

**Suggested starter domains (user can rename or add any):**

| Domain name | Emoji | Example topics |
|---|---|---|
| General Writing | ✍️ | Anything — default fallback |
| Social Media & Algorithms | 📱 | Platform guides, creator content |
| Business & Entrepreneurship | 💼 | Strategy, pricing, solopreneur |
| Children's Content | 🧒 | Stories, educational books |
| Faith & Personal Development | 🙏 | Devotionals, growth, mindset |
| Tech & Digital Tools | 💻 | AI guides, tool breakdowns |
| Health & Wellness | 🌿 | Habits, fitness, mental health |
| Finance & Money | 💰 | Budgeting, investing, income |

User can create ANY domain — these are just suggestions shown at onboarding.

---

## Migration: create_series_table

```php
Schema::create('series', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->text('description')->nullable();
    $table->longText('character_bible')->nullable();  // locked character descriptions
    $table->longText('style_guide')->nullable();      // locked art style + tone
    $table->integer('book_count')->default(0);
    $table->timestamps();
});
```

---

## Migration: create_projects_table

One row per book or digital product.

```php
Schema::create('projects', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('voice_profile_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('series_id')->nullable()->constrained()->nullOnDelete();
    $table->string('title');
    $table->enum('product_type', ['book', 'digital_product']);
    $table->string('topic')->nullable();
    $table->enum('status', ['active', 'completed', 'archived'])->default('active');
    $table->integer('current_step')->default(1);
    $table->integer('total_steps')->default(6);       // 6 for books, 5 for products
    $table->enum('book_format', [
        'childrens_educational',
        'childrens_story',
        'parenting_guide',
        'educational_nonfiction'
    ])->nullable();
    $table->string('target_age')->nullable();         // e.g. "3-6", "6-10"
    $table->string('target_reader')->nullable();
    $table->timestamps();
});
```

---

## Migration: create_research_results_table

Ten title angles generated per research session.

```php
Schema::create('research_results', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->string('topic');
    $table->text('title_angle');
    $table->string('book_format')->nullable();
    $table->integer('buyer_intent_score')->default(0);  // 1-100
    $table->enum('competition_level', ['Low', 'Medium', 'High'])->default('Medium');
    $table->text('reason')->nullable();
    $table->boolean('is_selected')->default(false);
    $table->timestamps();
});
```

---

## Migration: create_outlines_table

One row per chapter in the outline.

```php
Schema::create('outlines', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->integer('chapter_number');
    $table->string('chapter_title');
    $table->text('chapter_summary')->nullable();
    $table->integer('page_count_est')->nullable();
    $table->text('learning_obj')->nullable();
    $table->text('illustration_note')->nullable();
    $table->boolean('is_approved')->default(false);
    $table->timestamps();
});
```

---

## Migration: create_manuscripts_table

One row per written chapter.

```php
Schema::create('manuscripts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->integer('chapter_number');
    $table->string('chapter_title');
    $table->longText('content');
    $table->integer('word_count')->default(0);
    $table->boolean('is_approved')->default(false);
    $table->text('revision_notes')->nullable();
    $table->timestamps();
});
```

---

## Migration: create_illustration_prompts_table

One row per page needing an illustration.

```php
Schema::create('illustration_prompts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->integer('page_number');
    $table->text('page_text')->nullable();
    $table->longText('prompt');
    $table->text('character_desc')->nullable();       // locked, same every page
    $table->text('style_desc')->nullable();           // locked, same every page
    $table->boolean('is_completed')->default(false);  // user ticks when done
    $table->timestamps();
});
```

---

## Migration: create_kdp_listings_table

```php
Schema::create('kdp_listings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
    $table->string('book_title')->nullable();
    $table->string('subtitle')->nullable();
    $table->text('description')->nullable();          // HTML formatted
    $table->json('keywords')->nullable();             // array of 7 keywords
    $table->string('primary_category')->nullable();
    $table->string('secondary_category')->nullable();
    $table->text('author_bio')->nullable();
    $table->string('price_recommendation')->nullable();
    $table->boolean('is_approved')->default(false);
    $table->timestamps();
});
```

---

## Migration: create_digital_products_table

```php
Schema::create('digital_products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
    $table->enum('product_type', [
        'prompt_pack', 'notion_template', 'pdf_guide', 'swipe_file', 'toolkit'
    ])->nullable();
    $table->enum('platform', ['gumroad', 'selar', 'payhip'])->nullable();
    $table->decimal('price_usd', 8, 2)->nullable();
    $table->json('sections')->nullable();             // array of section objects
    $table->string('sales_page_title')->nullable();
    $table->longText('sales_page_body')->nullable();
    $table->string('tagline')->nullable();
    $table->boolean('is_approved')->default(false);
    $table->timestamps();
});
```

---

## Migration: create_launch_packs_table

```php
Schema::create('launch_packs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
    $table->text('social_post_linkedin')->nullable();
    $table->text('social_post_twitter')->nullable();
    $table->text('social_post_instagram')->nullable();
    $table->text('social_post_pinterest')->nullable();
    $table->text('social_post_whatsapp')->nullable();
    $table->string('email_1_subject')->nullable();
    $table->longText('email_1_body')->nullable();
    $table->string('email_2_subject')->nullable();
    $table->longText('email_2_body')->nullable();
    $table->string('email_3_subject')->nullable();
    $table->longText('email_3_body')->nullable();
    $table->text('review_request')->nullable();
    $table->boolean('is_approved')->default(false);
    $table->timestamps();
});
```

---

## Eloquent relationships

```php
// User model
public function voiceProfiles(): HasMany          // all domain profiles
public function defaultVoiceProfile(): HasOne     // the is_default=true profile
public function projects(): HasMany
public function series(): HasMany

// Project model
public function user(): BelongsTo
public function voiceProfile(): BelongsTo
public function series(): BelongsTo
public function researchResults(): HasMany
public function outlines(): HasMany
public function manuscripts(): HasMany
public function illustrationPrompts(): HasMany
public function kdpListing(): HasOne
public function digitalProduct(): HasOne
public function launchPack(): HasOne

// Series model
public function user(): BelongsTo
public function projects(): HasMany
```

---

## API key encryption

Store API keys encrypted in the users table:

```php
// User model
protected $casts = [
    'anthropic_api_key' => 'encrypted',
    'openai_api_key'    => 'encrypted',
];
```

Laravel's `encrypted` cast handles encryption/decryption automatically.
Never store API keys in plain text.
