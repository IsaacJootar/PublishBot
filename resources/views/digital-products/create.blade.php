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

        <form method="POST" action="{{ route('digital-products.store') }}"
              onsubmit="document.getElementById('btn-start').textContent='Starting...';document.getElementById('btn-start').style.opacity='0.7';">
            @csrf
            <input type="hidden" name="product_type" value="{{ $type }}">

            <div class="pai-card" style="margin-bottom:1.25rem;">
                <h2 style="margin:0 0 1rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Brief</h2>

                {{-- Website Copy Pack --}}
                @if($type === 'website_copy_pack')
                    <label class="pai-label">Business name <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="business_name" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Lagos Creative Studio" value="{{ old('business_name') }}"/>

                    <label class="pai-label">What does this business do? <span style="color:#EF4444;">*</span></label>
                    <textarea name="niche" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Brand design studio for African startups">{{ old('niche') }}</textarea>

                    <label class="pai-label">Who is the ideal customer? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Nigerian tech founders launching their first product">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">Top 3 problems you solve <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Weak brand perception, no consistency, poor online presence">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.25rem;">
                        <label class="pai-label">What makes this business different? <span style="color:#EF4444;">*</span></label>
                        <textarea name="differentiator" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Only agency in Lagos that specialises in Afrocentric branding for tech startups">{{ old('differentiator') }}</textarea>

                        <label class="pai-label">Tone of voice</label>
                        <select name="tone" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach(['Friendly and warm','Professional','Bold and direct','Playful','Calm and reassuring'] as $t)
                                <option value="{{ $t }}" {{ old('tone','Friendly and warm') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">How many service pages?</label>
                        <select name="service_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach([1,2,3,4,5] as $n)
                                <option value="{{ $n }}" {{ (int)old('service_count',2) === $n ? 'selected' : '' }}>{{ $n }} page{{ $n > 1 ? 's' : '' }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Service names <span style="color:#9B93B0;font-weight:400;">(one per line)</span></label>
                        <textarea name="service_names" rows="3" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="Brand Strategy&#10;Logo Design&#10;Website Design">{{ old('service_names') }}</textarea>

                        <label class="pai-label">Business location <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <input type="text" name="business_location" class="pai-input" placeholder="e.g. Lagos Island, Lagos" value="{{ old('business_location') }}"/>
                    </div>

                {{-- Brand Messaging System --}}
                @elseif($type === 'brand_messaging_system')
                    <label class="pai-label">Business or brand name <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="business_name" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Kemi Builds" value="{{ old('business_name') }}"/>

                    <label class="pai-label">Your name <span style="color:#9B93B0;font-weight:400;">(if personal brand)</span></label>
                    <input type="text" name="founder_name" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Kemi Adeleke" value="{{ old('founder_name') }}"/>

                    <label class="pai-label">What do you do in one sentence? <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. I help African founders build brands that attract premium clients" value="{{ old('niche') }}"/>

                    <label class="pai-label">Who do you help? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Early-stage founders in Nigeria and Ghana who want to stand out">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">What problem do you solve? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Founders sound generic and lose deals to competitors who look more established">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.25rem;">
                        <label class="pai-label">What makes you different? <span style="color:#EF4444;">*</span></label>
                        <textarea name="differentiator" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. I combine brand psychology with African storytelling — no western templates">{{ old('differentiator') }}</textarea>

                        <label class="pai-label">Your core values <span style="color:#9B93B0;font-weight:400;">(up to 5, one per line)</span></label>
                        <textarea name="core_values" rows="3" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="Authenticity&#10;Excellence&#10;Community">{{ old('core_values') }}</textarea>

                        <label class="pai-label">Your personality in 3 words</label>
                        <input type="text" name="personality_words" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Bold, warm, direct" value="{{ old('personality_words') }}"/>

                        <label class="pai-label">What do you never want to sound like?</label>
                        <input type="text" name="avoid_sounding_like" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Corporate, robotic, or preachy" value="{{ old('avoid_sounding_like') }}"/>

                        <label class="pai-label">Price point of main offer</label>
                        <input type="text" name="price_point" class="pai-input" placeholder="e.g. ₦500,000 / $1,500" value="{{ old('price_point') }}"/>
                    </div>

                {{-- Sales Funnel Copy Pack --}}
                @elseif($type === 'sales_funnel_copy')
                    <label class="pai-label">What are you selling? <span style="color:#EF4444;">*</span></label>
                    <textarea name="niche" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. A 6-week online course on freelance pricing for Nigerian designers">{{ old('niche') }}</textarea>

                    <label class="pai-label">Who is the ideal buyer? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Freelance designers in Africa, 1-5 years experience, undercharging">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">Their biggest problem? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. They attract low-budget clients and don't know how to charge premium rates">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.25rem;">
                        <label class="pai-label">Price point <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="price_point" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. $497 or ₦150,000" value="{{ old('price_point') }}"/>

                        <label class="pai-label">What have they tried before?</label>
                        <textarea name="tried_before" rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. YouTube tutorials, free courses, copying others' pricing">{{ old('tried_before') }}</textarea>

                        <label class="pai-label">What makes your offer different?</label>
                        <textarea name="differentiator" rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Live coaching + proven pricing framework specific to the African market">{{ old('differentiator') }}</textarea>

                        <label class="pai-label">Tone</label>
                        <select name="tone" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach(['Friendly','Professional','Bold','Empathetic','Direct'] as $t)
                                <option value="{{ $t }}" {{ old('tone','Friendly') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>

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

                {{-- Niche Research Report --}}
                @elseif($type === 'niche_research_report')
                    <label class="pai-label">What niche to research? <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Online fitness coaching for African women" value="{{ old('niche') }}"/>

                    <label class="pai-label">Who will use this report? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. A founder deciding whether to launch a product in this niche">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">What decision does this report inform? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Whether to invest 6 months building a course or a software product">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.25rem;">
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

                        <label class="pai-label">Specific questions to answer <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <textarea name="specific_questions" rows="2" class="pai-input" style="resize:vertical;" placeholder="e.g. What is the total addressable market? Who are the top 3 players?">{{ old('specific_questions') }}</textarea>
                    </div>

                {{-- Buyer Persona Pack --}}
                @elseif($type === 'buyer_persona_pack')
                    <label class="pai-label">What industry or niche? <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Social media management for SMEs" value="{{ old('niche') }}"/>

                    <label class="pai-label">Describe your ideal customer broadly <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Small business owners in Nigeria, 30-50, struggling to stay consistent online">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">What are you selling to them? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Done-for-you social media management at ₦150,000/month">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.25rem;">
                        <label class="pai-label">Price point</label>
                        <input type="text" name="price_point" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. $297 / ₦120,000 per month" value="{{ old('price_point') }}"/>

                        <label class="pai-label">How many personas?</label>
                        <select name="persona_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach([3,4,5] as $n)
                                <option value="{{ $n }}" {{ (int)old('persona_count',3) === $n ? 'selected' : '' }}>{{ $n }} personas</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Geographic focus</label>
                        <input type="text" name="geography" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Nigeria, South Africa, Kenya" value="{{ old('geography','Nigeria') }}"/>

                        <label class="pai-label">Any specific persona types to include? <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <textarea name="persona_types" rows="2" class="pai-input" style="resize:vertical;" placeholder="e.g. The overwhelmed founder, the budget-conscious manager">{{ old('persona_types') }}</textarea>
                    </div>

                {{-- Excel Tracker --}}
                @elseif($type === 'excel_tracker')
                    <label class="pai-label">What type of business is this tracker for? <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Freelance copywriting business" value="{{ old('niche') }}"/>

                    <label class="pai-label">Who will use this tracker? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Solo freelancers managing multiple clients without a CRM">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">Their biggest tracking problem? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Losing track of invoices, late payments, which clients owe money">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.25rem;">
                        <label class="pai-label">What type of tracker? <span style="color:#EF4444;">*</span></label>
                        <select name="tracker_type" required class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            <option value="">— Choose tracker type —</option>
                            @foreach(['Client tracker','Income tracker','Project tracker','Sales pipeline','Social media analytics','Inventory tracker','Custom'] as $t)
                                <option value="{{ $t }}" {{ old('tracker_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">How many team members will use it?</label>
                        <select name="team_size" class="pai-input" style="cursor:pointer;">
                            @foreach(['Just me','2-5','5-10','10+'] as $s)
                                <option value="{{ $s }}" {{ old('team_size','Just me') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                {{-- Notion Business OS --}}
                @elseif($type === 'notion_business_os')
                    <label class="pai-label">What type of business? <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Marketing consultancy" value="{{ old('niche') }}"/>

                    <label class="pai-label">Who is this workspace for? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Solo consultant managing 5-10 clients, using WhatsApp and notebooks">{{ old('buyer_description') }}</textarea>

                    <label class="pai-label">Biggest operational problems to solve? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. No central place for client info, missing deadlines, disorganised finances">{{ old('buyer_problem') }}</textarea>

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.25rem;">
                        <label class="pai-label">Solo or team?</label>
                        <select name="team_size" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach(['Solo','Small team 2-5','Larger team'] as $s)
                                <option value="{{ $s }}" {{ old('team_size','Solo') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">How many clients at once?</label>
                        <select name="client_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                            @foreach(['1-5','5-10','10-20','20+'] as $c)
                                <option value="{{ $c }}" {{ old('client_count','1-5') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>

                        <label class="pai-label">Which areas to organise?</label>
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
                            @foreach(['Clients','Projects','Content','Finance','SOPs','Goals','Meetings','Personal productivity'] as $a)
                                <label style="display:flex;align-items:center;gap:0.35rem;font-size:0.82rem;cursor:pointer;background:#F5F4FF;border-radius:8px;padding:0.35rem 0.7rem;">
                                    <input type="checkbox" name="organise_areas[]" value="{{ $a }}" {{ in_array($a, old('organise_areas', ['Clients','Projects','Finance'])) ? 'checked' : '' }}> {{ $a }}
                                </label>
                            @endforeach
                        </div>

                        <label class="pai-label">Current tools to replace <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <input type="text" name="current_tools" class="pai-input" placeholder="e.g. WhatsApp, spreadsheets, paper notebooks" value="{{ old('current_tools') }}"/>
                    </div>

                {{-- Content Calendar System --}}
                @elseif($type === 'content_calendar_system')
                    <label class="pai-label">What niche or industry is this calendar for? <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Personal finance for Nigerians" value="{{ old('niche') }}"/>

                    <label class="pai-label">Who is the target audience? <span style="color:#EF4444;">*</span></label>
                    <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Young professionals in Lagos learning to save and invest">{{ old('buyer_description') }}</textarea>

                    <input type="hidden" name="buyer_problem" value="Consistent content creation and planning">

                    <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.25rem;">
                        <label class="pai-label">Which platforms? <span style="color:#EF4444;">*</span></label>
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
                        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1rem;">
                            @foreach(['Brand awareness','Leads','Sales','Community','Authority'] as $g)
                                <label style="display:flex;align-items:center;gap:0.35rem;font-size:0.82rem;cursor:pointer;background:#F5F4FF;border-radius:8px;padding:0.35rem 0.7rem;">
                                    <input type="checkbox" name="content_goals[]" value="{{ $g }}" {{ in_array($g, old('content_goals', ['Brand awareness','Authority'])) ? 'checked' : '' }}> {{ $g }}
                                </label>
                            @endforeach
                        </div>

                        <label class="pai-label">Tone of voice</label>
                        <select name="tone" class="pai-input" style="cursor:pointer;">
                            @foreach(['Professional','Casual','Inspirational','Educational','Entertaining'] as $t)
                                <option value="{{ $t }}" {{ old('tone','Educational') === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                {{-- Original types: Prompt Library, SOP Pack, Email Vault --}}
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
                        <option value="">— No voice profile —</option>
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
