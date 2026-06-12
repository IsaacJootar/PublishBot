<x-app-layout>
    <x-slot name="title">My Voice</x-slot>

    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.75rem;">
        <div>
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.25rem;">My Voice</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">Each domain profile learns how you write about a specific topic. The more you train it, the more it sounds like you.</p>
        </div>
        <a href="{{ route('voice.create') }}" class="btn-primary" style="text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add a new domain
        </a>
    </div>

    @if($profiles->isEmpty())
        {{-- Onboarding: suggested domains --}}
        <div class="pai-card" style="margin-bottom:1.5rem;">
            <p style="font-size:0.9rem;font-weight:700;color:#0F0A1E;margin:0 0 0.25rem;">Choose a domain to get started</p>
            <p style="font-size:0.8rem;color:#9B93B0;margin:0 0 1.25rem;">Click any domain below to create it. You can also create a custom one.</p>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:0.75rem;">
                @foreach([
                    ['emoji'=>'✍️','name'=>'General Writing','desc'=>'Default voice for any topic','color'=>'#6C3CE1'],
                    ['emoji'=>'📱','name'=>'Social Media & Algorithms','desc'=>'Platform guides, creator content','color'=>'#10B981'],
                    ['emoji'=>'💼','name'=>'Business & Entrepreneurship','desc'=>'Strategy, pricing, solopreneur','color'=>'#3B82F6'],
                    ['emoji'=>'🧒','name'=>'Children\'s Content','desc'=>'Stories and educational books','color'=>'#F59E0B'],
                    ['emoji'=>'🙏','name'=>'Faith & Personal Dev','desc'=>'Devotionals, growth, mindset','color'=>'#F43F5E'],
                    ['emoji'=>'💻','name'=>'Tech & Digital Tools','desc'=>'AI guides, tool breakdowns','color'=>'#06B6D4'],
                    ['emoji'=>'🌿','name'=>'Health & Wellness','desc'=>'Habits, fitness, mental health','color'=>'#84CC16'],
                    ['emoji'=>'💰','name'=>'Finance & Money','desc'=>'Budgeting, investing, income','color'=>'#F97316'],
                ] as $s)
                <a href="{{ route('voice.create') }}?name={{ urlencode($s['name']) }}&emoji={{ urlencode($s['emoji']) }}&color={{ urlencode($s['color']) }}&desc={{ urlencode($s['desc']) }}"
                   style="display:flex;align-items:flex-start;gap:0.625rem;padding:0.875rem;background:#F5F4FF;border:1px solid #E4E0F0;border-left:3px solid {{ $s['color'] }};border-radius:10px;text-decoration:none;transition:background 0.15s;"
                   onmouseover="this.style.background='#EDE9FF'" onmouseout="this.style.background='#F5F4FF'">
                    <span style="font-size:1.25rem;line-height:1;flex-shrink:0;">{{ $s['emoji'] }}</span>
                    <div>
                        <p style="font-size:0.82rem;font-weight:700;color:#0F0A1E;margin:0 0 0.15rem;">{{ $s['name'] }}</p>
                        <p style="font-size:0.75rem;color:#9B93B0;margin:0;">{{ $s['desc'] }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    @else
        {{-- Profile cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;margin-bottom:1.5rem;">
            @foreach($profiles as $profile)
            <div class="pai-card" style="border-left:4px solid {{ $profile->color }};position:relative;">
                {{-- Default badge --}}
                @if($profile->is_default)
                <span style="position:absolute;top:1rem;right:1rem;background:#FFF8E1;color:#D97706;border:1px solid #FCD34D;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">★ Default</span>
                @endif

                <div style="display:flex;align-items:center;gap:0.625rem;margin-bottom:0.75rem;">
                    <span style="font-size:1.5rem;line-height:1;">{{ $profile->emoji }}</span>
                    <div>
                        <p style="font-size:0.95rem;font-weight:700;color:#0F0A1E;margin:0;">{{ $profile->name }}</p>
                        <p style="font-size:0.75rem;color:#9B93B0;margin:0;">{{ $profile->domain_description ?: 'No description' }}</p>
                    </div>
                </div>

                {{-- Word count / status --}}
                <div style="margin-bottom:1rem;">
                    @php
                        $wc = $profile->word_count;
                        if ($wc >= 5000)      { $label = 'Excellent'; $bg = '#ECFDF5'; $color = '#065F46'; }
                        elseif ($wc >= 2000)  { $label = 'Strong';    $bg = '#EFF6FF'; $color = '#1D4ED8'; }
                        elseif ($wc >= 1000)  { $label = 'Good';      $bg = '#F0EBFF'; $color = '#5A2EC9'; }
                        elseif ($wc >= 500)   { $label = 'Getting there'; $bg = '#FFFBEB'; $color = '#92400E'; }
                        else                  { $label = 'Needs more content'; $bg = '#FFF1F2'; $color = '#BE123C'; }
                    @endphp
                    <span style="background:{{ $bg }};color:{{ $color }};border-radius:999px;padding:2px 10px;font-size:0.72rem;font-weight:600;">
                        {{ number_format($wc) }} words · {{ $label }}
                    </span>
                    @if($profile->extracted_style)
                    <span style="background:#ECFDF5;color:#065F46;border-radius:999px;padding:2px 10px;font-size:0.72rem;font-weight:600;margin-left:4px;">
                        ✓ Style extracted
                    </span>
                    @endif
                </div>

                {{-- Actions --}}
                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                    <a href="{{ route('voice.train', $profile) }}" class="btn-primary" style="text-decoration:none;font-size:0.78rem;padding:0.45rem 0.875rem;">
                        {{ $profile->extracted_style ? 'Train more' : 'Upload samples' }}
                    </a>
                    @if(!$profile->is_default)
                    <form method="POST" action="{{ route('voice.default', $profile) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-ghost" style="font-size:0.78rem;padding:0.45rem 0.875rem;">Set as default</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('voice.destroy', $profile) }}" style="display:inline;" onsubmit="return confirm('Delete this voice profile? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#9B93B0;cursor:pointer;font-size:0.78rem;padding:0.45rem 0.5rem;" onmouseover="this.style.color='#EF4444'" onmouseout="this.style.color='#9B93B0'">Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <a href="{{ route('voice.create') }}" style="display:inline-flex;align-items:center;gap:6px;font-size:0.85rem;font-weight:600;color:#6C3CE1;text-decoration:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add another domain
        </a>
    @endif

</x-app-layout>
