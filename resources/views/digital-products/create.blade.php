<x-app-layout>
    <x-slot name="title">New {{ $meta['name'] }}</x-slot>

    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('digital-products.index') }}" style="font-size:0.78rem;color:#6C3CE1;text-decoration:none;font-weight:600;">← All product types</a>
    </div>

    <div style="max-width:680px;">
        <div class="pai-card" style="margin-bottom:1.25rem;">
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.4rem;">
                <span style="font-size:1.75rem;">{{ $meta['emoji'] }}</span>
                <div>
                    <h1 style="margin:0;font-size:1.15rem;font-weight:800;color:#0F0A1E;">{{ $meta['name'] }}</h1>
                    <p style="margin:0;font-size:0.8rem;color:#5C5470;">{{ $meta['tagline'] }}</p>
                </div>
            </div>
            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-top:0.85rem;">
                <span style="background:#ECFDF5;color:#065F46;border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;">Price: {{ $meta['price'] }}</span>
                <span style="background:#F5F4FF;color:#5A2EC9;border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;">Time: {{ $meta['time'] }}</span>
                @if(!empty($meta['badge']))
                    @php $badgeStyle = ($meta['badge_color'] ?? '') === 'amber' ? 'background:#FEF3C7;color:#92400E' : 'background:#DBEAFE;color:#1E40AF'; @endphp
                    <span style="{{ $badgeStyle }};border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;">{{ $meta['badge'] }}</span>
                @endif
            </div>
        </div>

        {{-- Niche product note --}}
        @if(in_array($type, \App\Models\DigitalProduct::extendedTypes()))
        <div style="background:#F5F4FF;border-left:3px solid #6C3CE1;border-radius:8px;padding:0.7rem 0.9rem;margin-bottom:1.25rem;font-size:0.78rem;color:#5C5470;line-height:1.6;">
            <strong style="color:#0F0A1E;">You are creating a niche product</strong> — not a custom product for one client. PublishBot generates content for the whole niche with <strong>[BRACKETS]</strong> for buyers to fill in their specific details. You upload once and sell to thousands.
        </div>
        @endif

        <form method="POST" action="{{ route('digital-products.store') }}"
              onsubmit="document.getElementById('btn-start').textContent='Starting...';document.getElementById('btn-start').style.opacity='0.7';">
            @csrf
            <input type="hidden" name="product_type" value="{{ $type }}">

            <div class="pai-card" style="margin-bottom:1.25rem;">
                <h2 style="margin:0 0 1rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Brief</h2>

                {{-- ── 4 CORE FIELDS — same for all 8 new types ─────────────── --}}
                @if(in_array($type, \App\Models\DigitalProduct::extendedTypes()))

                    <label class="pai-label">
                        @if($type === 'niche_research_report') What niche to research?
                        @elseif($type === 'content_calendar_system') What niche or industry is this calendar for?
                        @elseif($type === 'excel_tracker') What type of business is this tracker for?
                        @elseif($type === 'notion_business_os') What type of business is this OS for?
                        @else What niche or industry is this product for?
                        @endif
                        <span style="color:#EF4444;">*</span>
                    </label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;"
                        placeholder="{{ match($type) {
                            'niche_research_report'     => 'e.g. Online fitness coaching for African women',
                            'content_calendar_system'   => 'e.g. Personal finance for young Nigerians',
                            'excel_tracker'             => 'e.g. Freelance copywriting businesses',
                            'notion_business_os'        => 'e.g. Marketing consultants',
                            'website_copy_pack'         => 'e.g. Freelance graphic designers',
                            'brand_messaging_system'    => 'e.g. Business coaches and consultants',
                            'sales_funnel_copy'         => 'e.g. Online course creators in Africa',
                            'buyer_persona_pack'        => 'e.g. Social media management agencies',
                            default                     => 'e.g. Freelance graphic designers',
                        } }}"
                        value="{{ old('niche') }}"/>

                    <label class="pai-label">Who is the typical buyer in this niche? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;"
                        placeholder="{{ match($type) {
                            'niche_research_report'   => 'e.g. A founder deciding whether to launch a product in this niche',
                            'content_calendar_system' => 'e.g. Young professionals in Lagos learning to save and invest',
                            'excel_tracker'           => 'e.g. Solo freelancers managing multiple clients without a CRM',
                            'notion_business_os'      => 'e.g. Solo consultants managing 5–10 clients using WhatsApp and notebooks',
                            'website_copy_pack'       => 'e.g. Self-employed designers launching or rebranding their website',
                            'brand_messaging_system'  => 'e.g. Coaches who have been in business 1–3 years and struggle to explain what they do',
                            'sales_funnel_copy'       => 'e.g. Course creators who have knowledge to sell but no funnel to sell it with',
                            'buyer_persona_pack'      => 'e.g. Agency owners who need to understand their clients\' customers better',
                            default                   => 'e.g. Self-employed professionals who want to grow their business',
                        } }}">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">What is their biggest problem? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;"
                        placeholder="{{ match($type) {
                            'niche_research_report'   => 'e.g. They don\'t know if this niche is worth entering before investing months of work',
                            'content_calendar_system' => 'e.g. They run out of ideas after week 2 and stop posting consistently',
                            'excel_tracker'           => 'e.g. Losing track of invoices, late payments, and which clients owe money',
                            'notion_business_os'      => 'e.g. No central place for client info — missing deadlines and disorganised finances',
                            'website_copy_pack'       => 'e.g. They don\'t know what to write on their website and can\'t afford a copywriter',
                            'brand_messaging_system'  => 'e.g. They sound generic online and lose deals to competitors who look more established',
                            'sales_funnel_copy'       => 'e.g. They have great offers but no system to attract and convert cold leads',
                            'buyer_persona_pack'      => 'e.g. Writing copy for "everyone" and converting nobody',
                            default                   => 'e.g. They spend too much time on low-value tasks instead of growing their business',
                        } }}">{{ old('buyer_problem') }}</textarea>

                    <label class="pai-label">Tone of voice</label>
                    <select name="tone" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                        @foreach(['Professional','Warm and friendly','Bold and direct','Casual','Empathetic'] as $t)
                            <option value="{{ $t }}" {{ old('tone','Professional') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>

                    {{-- ── Type-specific extra fields ──────────────────────── --}}

                    @if($type === 'content_calendar_system')
                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;">
                        <label class="pai-label">Which platforms?</label>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
                            @foreach(['Instagram','LinkedIn','X (Twitter)','TikTok','Pinterest','Facebook','YouTube'] as $p)
                                <label style="display:flex;align-items:center;gap:0.35rem;font-size:0.82rem;cursor:pointer;background:#F5F4FF;border-radius:8px;padding:0.35rem 0.7rem;">
                                    <input type="checkbox" name="platforms[]" value="{{ $p }}" {{ in_array($p, old('platforms', ['Instagram','LinkedIn'])) ? 'checked' : '' }}> {{ $p }}
                                </label>
                            @endforeach
                        </div>

                        <label class="pai-label">How many posts per week?</label>
                        <select name="posting_frequency" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach(['3','5','7','Daily'] as $f)
                                <option value="{{ $f }}" {{ old('posting_frequency','5') === $f ? 'selected' : '' }}>{{ $f }} posts/week</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Content goals</label>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                            @foreach(['Brand awareness','Leads','Sales','Community','Authority'] as $g)
                                <label style="display:flex;align-items:center;gap:0.35rem;font-size:0.82rem;cursor:pointer;background:#F5F4FF;border-radius:8px;padding:0.35rem 0.7rem;">
                                    <input type="checkbox" name="content_goals[]" value="{{ $g }}" {{ in_array($g, old('content_goals', ['Brand awareness','Authority'])) ? 'checked' : '' }}> {{ $g }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @elseif($type === 'excel_tracker')
                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;">
                        <label class="pai-label">What type of tracker? <span style="color:#EF4444;">*</span></label>
                        <select name="tracker_type" required class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            <option value="">— Choose tracker type —</option>
                            @foreach(['Client tracker','Income tracker','Project tracker','Sales pipeline','Social media analytics','Inventory tracker','Custom'] as $t)
                                <option value="{{ $t }}" {{ old('tracker_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">How many team members will typically use it?</label>
                        <select name="team_size" class="pai-input" style="cursor:pointer;">
                            @foreach(['Just me','2-5','5-10','10+'] as $s)
                                <option value="{{ $s }}" {{ old('team_size','Just me') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    @elseif($type === 'notion_business_os')
                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;">
                        <label class="pai-label">Solo or team?</label>
                        <select name="team_size" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach(['Solo','Small team 2-5','Larger team'] as $s)
                                <option value="{{ $s }}" {{ old('team_size','Solo') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Typical number of clients at once?</label>
                        <select name="client_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach(['1-5','5-10','10-20','20+'] as $c)
                                <option value="{{ $c }}" {{ old('client_count','1-5') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Which areas to organise?</label>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                            @foreach(['Clients','Projects','Content','Finance','SOPs','Goals','Meetings','Personal productivity'] as $a)
                                <label style="display:flex;align-items:center;gap:0.35rem;font-size:0.82rem;cursor:pointer;background:#F5F4FF;border-radius:8px;padding:0.35rem 0.7rem;">
                                    <input type="checkbox" name="organise_areas[]" value="{{ $a }}" {{ in_array($a, old('organise_areas', ['Clients','Projects','Finance'])) ? 'checked' : '' }}> {{ $a }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @elseif($type === 'website_copy_pack')
                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;">
                        <label class="pai-label">How many service pages should the pack include?</label>
                        <select name="service_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach([1,2,3,4,5] as $n)
                                <option value="{{ $n }}" {{ (int)old('service_count',2) === $n ? 'selected' : '' }}>{{ $n }} service page{{ $n > 1 ? 's' : '' }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Typical service names in this niche <span style="color:#9B93B0;font-weight:400;">(one per line — buyers replace with their own)</span></label>
                        <textarea name="service_names" rows="3" class="pai-input" style="resize:vertical;" placeholder="e.g. Brand Identity Package&#10;Logo Design&#10;Website Design">{{ old('service_names') }}</textarea>
                    </div>

                    @elseif($type === 'brand_messaging_system')
                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;">
                        <label class="pai-label">What makes the top practitioners in this niche different from the average?</label>
                        <textarea name="differentiator" rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. The best ones specialise in one industry and charge premium rates — they don't try to work with everyone">{{ old('differentiator') }}</textarea>

                        <label class="pai-label">Typical price point for services in this niche</label>
                        <input type="text" name="price_point" class="pai-input" placeholder="e.g. ₦500,000–₦2,000,000 / $500–$5,000 per project" value="{{ old('price_point') }}"/>
                    </div>

                    @elseif($type === 'sales_funnel_copy')
                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;">
                        <label class="pai-label">Typical price point for offers in this niche <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="price_point" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. $297–$997 / ₦50,000–₦200,000" value="{{ old('price_point') }}"/>

                        <label class="pai-label">What do buyers in this niche typically try before buying?</label>
                        <textarea name="tried_before" rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Free YouTube tutorials, cheap Udemy courses, DIY approaches that don't work">{{ old('tried_before') }}</textarea>

                        <label class="pai-label">What makes the best offers in this niche stand out?</label>
                        <textarea name="differentiator" rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Done-with-you coaching instead of just recorded videos, plus a community">{{ old('differentiator') }}</textarea>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div>
                                <label class="pai-label">Include upsell page?</label>
                                <select name="needs_upsell" class="pai-input" style="cursor:pointer;">
                                    <option value="No" {{ old('needs_upsell','No') === 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ old('needs_upsell') === 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div>
                                <label class="pai-label">Include webinar invite?</label>
                                <select name="needs_webinar" class="pai-input" style="cursor:pointer;">
                                    <option value="No" {{ old('needs_webinar','No') === 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Yes" {{ old('needs_webinar') === 'Yes' ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    @elseif($type === 'niche_research_report')
                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;">
                        <label class="pai-label">Geographic focus</label>
                        <select name="geography" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach(['Global','USA','UK','Nigeria','Africa','Europe','Asia','Other'] as $g)
                                <option value="{{ $g }}" {{ old('geography','Nigeria') === $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Report depth</label>
                        <select name="report_depth" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach(['Overview (10 pages)','Standard (20 pages)','Deep dive (30 pages)'] as $d)
                                <option value="{{ $d }}" {{ old('report_depth','Standard (20 pages)') === $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Specific competitors to include <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <input type="text" name="competitors" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Piggyvest, Cowrywise, Risevest" value="{{ old('competitors') }}"/>

                        <label class="pai-label">Specific questions this report must answer <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <textarea name="specific_questions" rows="2" class="pai-input" style="resize:vertical;" placeholder="e.g. What is the TAM? Who are the top 3 players? Where is the white space?">{{ old('specific_questions') }}</textarea>
                    </div>

                    @elseif($type === 'buyer_persona_pack')
                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;">
                        <label class="pai-label">Typical price point for offers in this niche</label>
                        <input type="text" name="price_point" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. $297/month / ₦120,000 per project" value="{{ old('price_point') }}"/>

                        <label class="pai-label">How many personas?</label>
                        <select name="persona_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach([3,4,5] as $n)
                                <option value="{{ $n }}" {{ (int)old('persona_count',3) === $n ? 'selected' : '' }}>{{ $n }} personas</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Geographic focus</label>
                        <input type="text" name="geography" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Nigeria, South Africa, Kenya" value="{{ old('geography','Nigeria') }}"/>

                        <label class="pai-label">Any specific persona archetypes to include? <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <textarea name="persona_types" rows="2" class="pai-input" style="resize:vertical;" placeholder="e.g. The overwhelmed beginner, the experienced pro ready to scale, the sceptic who has tried and failed">{{ old('persona_types') }}</textarea>
                    </div>
                    @endif

                {{-- ── ORIGINAL 3 TYPES — unchanged ────────────────────────── --}}
                @elseif($type === 'prompt_library')
                    <label class="pai-label">What is the niche or topic? <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Freelance graphic designers" value="{{ old('niche') }}"/>

                    <label class="pai-label">Who is the buyer? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Self-employed designers who want to use AI in their work">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">What is their biggest problem? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. They spend too much time on client emails and proposals">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.5rem;">
                        <label class="pai-label">How many prompts?</label>
                        <select name="prompt_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            <option value="30">30 prompts</option>
                            <option value="50" selected>50 prompts</option>
                            <option value="75">75 prompts</option>
                        </select>

                        <label class="pai-label">Prompt categories <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <input type="text" name="categories" class="pai-input" placeholder="e.g. Client communication, proposals, social media" value="{{ old('categories') }}"/>
                    </div>

                @elseif($type === 'sop_pack')
                    <label class="pai-label">What is the niche or topic? <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Freelance design studio" value="{{ old('niche') }}"/>

                    <label class="pai-label">Who is the buyer? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Studio owners who want to systemise their client work">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">What is their biggest problem? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Every project starts from scratch — no repeatable system">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.5rem;">
                        <label class="pai-label">What type of business?</label>
                        <input type="text" name="business_type" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Freelance design studio" value="{{ old('business_type') }}"/>

                        <label class="pai-label">How many SOPs?</label>
                        <select name="sop_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            <option value="10" selected>10 SOPs</option>
                            <option value="20">20 SOPs</option>
                            <option value="30">30 SOPs</option>
                        </select>

                        <label class="pai-label">Key business areas <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <input type="text" name="key_areas" class="pai-input" placeholder="e.g. Client onboarding, project delivery, invoicing" value="{{ old('key_areas') }}"/>
                    </div>

                @elseif($type === 'email_sequence_vault')
                    <label class="pai-label">What is the niche or topic? <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Freelance graphic designers" value="{{ old('niche') }}"/>

                    <label class="pai-label">Who is the buyer? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Designers wanting to automate their client follow-ups">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">What is their biggest problem? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Leads go cold because they forget to follow up">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.5rem;">
                        <label class="pai-label">Sequence types <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <input type="text" name="sequence_types" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Welcome, proposal follow-up, project completion" value="{{ old('sequence_types') }}"/>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div>
                                <label class="pai-label">How many sequences?</label>
                                <select name="sequence_count" class="pai-input" style="cursor:pointer;">
                                    <option value="5" selected>5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                </select>
                            </div>
                            <div>
                                <label class="pai-label">Emails per sequence?</label>
                                <select name="emails_per_sequence" class="pai-input" style="cursor:pointer;">
                                    <option value="3">3</option>
                                    <option value="5" selected>5</option>
                                    <option value="7">7</option>
                                </select>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Voice profile selector (all types) --}}
                @if($voices->isNotEmpty())
                <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:1rem;">
                    <label class="pai-label">Write in voice</label>
                    <select name="voice_profile_id" class="pai-input" style="cursor:pointer;">
                        <option value="" {{ $defaultVoiceId ? '' : 'selected' }}>— No voice profile —</option>
                        @foreach($voices as $v)
                            <option value="{{ $v->id }}" {{ $defaultVoiceId === $v->id ? 'selected' : '' }}>
                                {{ $v->emoji }} {{ $v->name }} {{ $v->is_default ? '(default)' : '' }}{{ $v->extracted_style ? '' : ' — untrained' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <button type="submit" id="btn-start" class="btn-primary" style="width:100%;padding:0.8rem;">
                Start building →
            </button>
        </form>
    </div>
</x-app-layout>
