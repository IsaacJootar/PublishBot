<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    {{-- Welcome row --}}
    <div style="margin-bottom:1.75rem; display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1 style="font-size:1.375rem; font-weight:800; color:#0F0A1E; margin:0 0 0.25rem;">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ Auth::user()->author_name ?: explode(' ', Auth::user()->name)[0] }} 👋
            </h1>
            <p style="font-size:0.875rem; color:#5C5470; margin:0;">Your AI publishing engine is ready.</p>
        </div>
        <a href="#" class="btn-primary" style="text-decoration:none;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New project
        </a>
    </div>

    {{-- Metric cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px, 1fr)); gap:1rem; margin-bottom:1.75rem;">
        <div class="metric-card metric-violet">
            <div class="metric-label">Total Projects</div>
            <div class="metric-value">0</div>
            <div class="metric-sub">books + products</div>
        </div>
        <div class="metric-card metric-green">
            <div class="metric-label">Books Complete</div>
            <div class="metric-value">0</div>
            <div class="metric-sub">ready to publish</div>
        </div>
        <div class="metric-card metric-amber">
            <div class="metric-label">Products Created</div>
            <div class="metric-value">0</div>
            <div class="metric-sub">digital products</div>
        </div>
        <div class="metric-card metric-blue">
            <div class="metric-label">Words Generated</div>
            <div class="metric-value">0</div>
            <div class="metric-sub">words written by AI</div>
        </div>
    </div>

    {{-- Two-column grid --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.25rem;">

        {{-- Get started --}}
        <div class="pai-card" style="grid-column: span 2;">
            <div style="margin-bottom:1rem;">
                <h2 style="font-size:0.95rem; font-weight:700; color:#0F0A1E; margin:0 0 0.25rem;">Get started in 3 steps</h2>
                <p style="font-size:0.8rem; color:#9B93B0; margin:0;">Your first sellable product is 60 minutes away.</p>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:0.875rem;">
                @foreach([
                    ['step' => '1', 'title' => 'Add your API key', 'desc' => 'Go to Settings and paste your Claude API key.', 'href' => route('settings.index'), 'cta' => 'Go to Settings →', 'done' => (bool) Auth::user()->anthropic_api_key],
                    ['step' => '2', 'title' => 'Set up My Voice', 'desc' => 'Upload writing samples so Claude sounds like you.', 'href' => '#', 'cta' => 'Set up My Voice →', 'done' => false],
                    ['step' => '3', 'title' => 'Start a project', 'desc' => 'Pick a topic. Choose Book or Digital Product.', 'href' => '#', 'cta' => 'Start now →', 'done' => false],
                ] as $step)
                <div style="background:#F5F4FF; border-radius:10px; padding:1rem; border:1px solid #E4E0F0; position:relative;">
                    @if($step['done'])
                    <div style="position:absolute;top:0.75rem;right:0.75rem;width:20px;height:20px;background:#ECFDF5;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <svg width="11" height="11" fill="none" stroke="#065F46" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    @endif
                    <div style="width:26px;height:26px;background:{{ $step['done'] ? '#6C3CE1' : '#E4E0F0' }};border-radius:8px;display:flex;align-items:center;justify-content:center;margin-bottom:0.625rem;">
                        <span style="font-size:0.75rem;font-weight:800;color:{{ $step['done'] ? '#fff' : '#9B93B0' }};">{{ $step['step'] }}</span>
                    </div>
                    <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0 0 0.25rem;">{{ $step['title'] }}</p>
                    <p style="font-size:0.78rem;color:#5C5470;margin:0 0 0.75rem;line-height:1.5;">{{ $step['desc'] }}</p>
                    <a href="{{ $step['href'] }}" style="font-size:0.78rem;font-weight:600;color:#6C3CE1;text-decoration:none;">{{ $step['cta'] }}</a>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Two pipeline cards --}}
        <div class="pai-card">
            <div style="display:flex;align-items:center;gap:0.625rem;margin-bottom:0.75rem;">
                <div style="width:34px;height:34px;background:linear-gradient(135deg,#6C3CE1,#4C2CA1);border-radius:9px;display:flex;align-items:center;justify-content:center;">
                    <svg width="17" height="17" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <div>
                    <p style="font-size:0.875rem;font-weight:700;color:#0F0A1E;margin:0;">Book Pipeline</p>
                    <p style="font-size:0.75rem;color:#9B93B0;margin:0;">KDP publishing</p>
                </div>
            </div>
            <p style="font-size:0.8rem;color:#5C5470;margin:0 0 0.875rem;line-height:1.5;">Research → Outline → Write → Illustrate → KDP Listing → Launch</p>
            <a href="#" class="btn-primary" style="text-decoration:none;font-size:0.8rem;padding:0.5rem 1rem;">Start a book →</a>
        </div>

        <div class="pai-card">
            <div style="display:flex;align-items:center;gap:0.625rem;margin-bottom:0.75rem;">
                <div style="width:34px;height:34px;background:linear-gradient(135deg,#D97706,#EA580C);border-radius:9px;display:flex;align-items:center;justify-content:center;">
                    <svg width="17" height="17" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p style="font-size:0.875rem;font-weight:700;color:#0F0A1E;margin:0;">Digital Product</p>
                    <p style="font-size:0.75rem;color:#9B93B0;margin:0;">Gumroad · Selar · Payhip</p>
                </div>
            </div>
            <p style="font-size:0.8rem;color:#5C5470;margin:0 0 0.875rem;line-height:1.5;">Research → Structure → Write → Sales Page → Launch</p>
            <a href="#" class="btn-outline" style="text-decoration:none;font-size:0.8rem;padding:0.5rem 1rem;">Start a product →</a>
        </div>

    </div>

    <style>
        @media (max-width: 640px) {
            .pai-page div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
            .pai-page div[style*="grid-column: span 2"] { grid-column: span 1 !important; }
        }
    </style>

</x-app-layout>
