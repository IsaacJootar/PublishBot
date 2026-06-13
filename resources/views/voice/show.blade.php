<x-app-layout>
    <x-slot name="title">{{ $profile->name }}</x-slot>

    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('voice.index') }}" style="font-size:0.78rem;color:#6C3CE1;text-decoration:none;font-weight:600;">← All voices</a>
    </div>

    {{-- Header --}}
    <div class="pai-card" style="border-top:4px solid {{ $profile->color }};margin-bottom:1.25rem;">
        <div style="display:flex;align-items:flex-start;gap:0.8rem;">
            <span style="font-size:2rem;">{{ $profile->emoji }}</span>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.2rem;">
                    <h1 style="margin:0;font-size:1.2rem;font-weight:800;color:#0F0A1E;">{{ $profile->name }}</h1>
                    @if($profile->is_default)
                    <span style="background:#FFFBEB;color:#92400E;border:1px solid #FDE68A;border-radius:999px;padding:2px 8px;font-size:0.68rem;font-weight:700;">★ Default</span>
                    @endif
                </div>
                <p style="margin:0;font-size:0.85rem;color:#5C5470;">{{ $profile->description ?: 'No description' }}</p>
            </div>
        </div>

        <div style="margin-top:1rem;display:flex;gap:0.4rem;flex-wrap:wrap;">
            @php
                $tone = $profile->qualityTone();
                $bg = $tone==='green'?'#ECFDF5':($tone==='amber'?'#FFFBEB':'#EFF6FF');
                $color = $tone==='green'?'#065F46':($tone==='amber'?'#92400E':'#1D4ED8');
            @endphp
            <span style="background:{{ $bg }};color:{{ $color }};border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;">{{ number_format($profile->word_count) }} words — {{ $profile->qualityLabel() }}</span>
            <span style="background:#F5F4FF;color:#5A2EC9;border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;">Status: {{ ucfirst($profile->status) }}</span>
        </div>
    </div>

    {{-- Upload samples --}}
    <div class="pai-card" style="margin-bottom:1.25rem;">
        <h2 style="margin:0 0 0.2rem;font-size:1rem;font-weight:700;color:#0F0A1E;">Add writing samples</h2>
        <p style="font-size:0.78rem;color:#9B93B0;margin:0 0 1rem;">Your own posts, transcripts, articles, WhatsApp messages — anything you've written about {{ strtolower($profile->name) }}.</p>

        <div style="background:#F5F4FF;border-radius:10px;padding:0.75rem 0.9rem;margin-bottom:1rem;border-left:3px solid #6C3CE1;">
            <p style="font-size:0.75rem;color:#5C5470;margin:0;font-weight:600;">Works best with:</p>
            <p style="font-size:0.72rem;color:#5C5470;margin:0.25rem 0 0;line-height:1.5;">
                Your social posts • Voice note transcripts • Old articles or blog posts • WhatsApp messages • Course notes or scripts
            </p>
        </div>

        <form method="POST" action="{{ route('voice.train', $profile) }}" enctype="multipart/form-data"
              onsubmit="document.getElementById('btn-upload').textContent='Uploading...';document.getElementById('btn-upload').style.opacity='0.7';">
            @csrf
            <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Upload files (.txt, .md, .docx)</label>
            <input type="file" name="samples[]" multiple accept=".txt,.md,.docx" class="pai-input" style="margin-bottom:0.9rem;padding:0.5rem;"/>

            <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">…or paste text directly</label>
            <textarea name="pasted_text" rows="6" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="Paste any of your writing here..."></textarea>

            <button type="submit" id="btn-upload" class="btn-outline">+ Add to training</button>
        </form>
    </div>

    {{-- Extract / re-extract --}}
    @if($profile->raw_content)
    <div class="pai-card" style="margin-bottom:1.25rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;">
                <h2 style="margin:0 0 0.2rem;font-size:1rem;font-weight:700;color:#0F0A1E;">Extract your style</h2>
                <p style="font-size:0.78rem;color:#9B93B0;margin:0;">Send your samples to Claude. It returns a structured voice profile injected into every future ebook + workbook.</p>
            </div>
            <form method="POST" action="{{ route('voice.extract', $profile) }}"
                  onsubmit="document.getElementById('btn-extract').textContent='Starting...';document.getElementById('btn-extract').style.opacity='0.7';">
                @csrf
                <button type="submit" id="btn-extract" class="btn-primary"
                    {{ $profile->status === 'training' ? 'disabled' : '' }}>
                    {{ $profile->extracted_style ? 'Re-extract style →' : 'Extract my writing style →' }}
                </button>
            </form>
        </div>
        @if($profile->status === 'training')
        <div id="training-banner" style="background:#EFF6FF;color:#1D4ED8;border-radius:8px;padding:0.6rem 0.8rem;margin-top:0.75rem;font-size:0.78rem;font-weight:600;">
            ⏳ Training in progress — page will refresh when done.
        </div>
        <script>
        (function() {
            const tick = setInterval(() => {
                fetch('{{ route('voice.status', $profile) }}')
                    .then(r => r.json())
                    .then(d => {
                        if (d.status === 'ready' || d.status === 'failed') {
                            clearInterval(tick);
                            location.reload();
                        }
                    })
                    .catch(() => {});
            }, 3000);
        })();
        </script>
        @endif
        @if($profile->status === 'failed' && $profile->error_message)
        <div style="background:#FFF1F2;color:#BE123C;border-radius:8px;padding:0.6rem 0.8rem;margin-top:0.75rem;font-size:0.78rem;">
            Failed: {{ $profile->error_message }}
        </div>
        @endif
    </div>
    @endif

    {{-- Extracted style --}}
    @if($profile->extracted_style)
    <div class="pai-card" style="margin-bottom:1.25rem;">
        <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
            <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">✓ Claude trained your voice</span>
        </div>
        <h2 style="margin:0 0 0.5rem;font-size:1rem;font-weight:700;color:#0F0A1E;">Your {{ $profile->name }} voice profile</h2>
        <pre style="font-family:inherit;white-space:pre-wrap;font-size:0.82rem;color:#0F0A1E;line-height:1.6;background:#FAF9FF;padding:1rem;border-radius:10px;margin:0;border:1px solid #E4E0F0;">{{ $profile->extracted_style }}</pre>
    </div>
    @endif

    {{-- Raw content preview --}}
    @if($profile->raw_content)
    <details class="pai-card" style="margin-bottom:1.25rem;">
        <summary style="cursor:pointer;font-size:0.85rem;font-weight:700;color:#0F0A1E;">View raw training content ({{ number_format($profile->word_count) }} words)</summary>
        <pre style="font-family:inherit;white-space:pre-wrap;font-size:0.78rem;color:#5C5470;margin:0.75rem 0 0;max-height:300px;overflow-y:auto;background:#F8F7FF;padding:0.75rem;border-radius:8px;">{{ \Illuminate\Support\Str::limit($profile->raw_content, 5000) }}</pre>
    </details>
    @endif

</x-app-layout>
