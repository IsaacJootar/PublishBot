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
            </div>
        </div>

        <form method="POST" action="{{ route('digital-products.store') }}"
              onsubmit="document.getElementById('btn-start').textContent='Starting...';document.getElementById('btn-start').style.opacity='0.7';">
            @csrf
            <input type="hidden" name="product_type" value="{{ $type }}">

            <div class="pai-card" style="margin-bottom:1.25rem;">
                <h2 style="margin:0 0 1rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Brief</h2>

                <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">What is the niche or topic? <span style="color:#EF4444;">*</span></label>
                <input type="text" name="niche" required class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Freelance graphic designers" value="{{ old('niche') }}"/>

                <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Who is the buyer? <span style="color:#EF4444;">*</span></label>
                <textarea name="buyer_description" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. Self-employed designers who want to use AI in their work">{{ old('buyer_description') }}</textarea>

                <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">What is their biggest problem? <span style="color:#EF4444;">*</span></label>
                <textarea name="buyer_problem" required rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="e.g. They spend too much time on client emails and proposals">{{ old('buyer_problem') }}</textarea>

                @if($type === 'prompt_library')
                <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.5rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">How many prompts?</label>
                    <select name="prompt_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                        <option value="30">30 prompts</option>
                        <option value="50" selected>50 prompts</option>
                        <option value="75">75 prompts</option>
                    </select>

                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Prompt categories <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                    <input type="text" name="categories" class="pai-input" placeholder="e.g. Client communication, proposals, social media" value="{{ old('categories') }}"/>
                </div>
                @elseif($type === 'sop_pack')
                <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.5rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">What type of business?</label>
                    <input type="text" name="business_type" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Freelance design studio" value="{{ old('business_type') }}"/>

                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">How many SOPs?</label>
                    <select name="sop_count" class="pai-input" style="margin-bottom:1rem;cursor:pointer;">
                        <option value="10" selected>10 SOPs</option>
                        <option value="20">20 SOPs</option>
                        <option value="30">30 SOPs</option>
                    </select>

                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Key business areas <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                    <input type="text" name="key_areas" class="pai-input" placeholder="e.g. Client onboarding, project delivery, invoicing" value="{{ old('key_areas') }}"/>
                </div>
                @elseif($type === 'email_sequence_vault')
                <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:0.5rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Sequence types <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                    <input type="text" name="sequence_types" class="pai-input" style="margin-bottom:1rem;" placeholder="e.g. Welcome, proposal follow-up, project completion" value="{{ old('sequence_types') }}"/>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">How many sequences?</label>
                            <select name="sequence_count" class="pai-input" style="cursor:pointer;">
                                <option value="5" selected>5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Emails per sequence?</label>
                            <select name="emails_per_sequence" class="pai-input" style="cursor:pointer;">
                                <option value="3">3</option>
                                <option value="5" selected>5</option>
                                <option value="7">7</option>
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                @if($voices->isNotEmpty())
                <div style="border-top:1px solid #E4E0F0;padding-top:1rem;margin-top:1rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Write in voice</label>
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
