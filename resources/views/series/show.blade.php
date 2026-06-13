<x-app-layout>
    <x-slot name="title">{{ $series->name }}</x-slot>

    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('series.index') }}" style="font-size:0.78rem;color:#6C3CE1;text-decoration:none;font-weight:600;">← All series</a>
    </div>

    {{-- Header --}}
    <div class="pai-card" style="border-top:4px solid {{ $series->color }};margin-bottom:1.25rem;">
        <div style="display:flex;align-items:flex-start;gap:0.85rem;flex-wrap:wrap;">
            <span style="font-size:2rem;">{{ $series->emoji }}</span>
            <div style="flex:1;min-width:200px;">
                <h1 style="margin:0;font-size:1.25rem;font-weight:800;color:#0F0A1E;line-height:1.2;">{{ $series->name }}</h1>
                <p style="margin:0.25rem 0 0;font-size:0.85rem;color:#5C5470;">{{ $series->description ?: 'No description' }}</p>
                @if($series->format)
                <p style="margin:0.4rem 0 0;font-size:0.75rem;color:#9B93B0;">Format: {{ $series->format }}</p>
                @endif
            </div>
            <div style="display:flex;gap:0.4rem;flex-shrink:0;">
                <a href="{{ route('series.edit', $series) }}" class="btn-outline" style="padding:0.4rem 0.85rem;font-size:0.75rem;">Edit</a>
                <form method="POST" action="{{ route('series.destroy', $series) }}" onsubmit="return confirm('Delete this series? Books inside it are kept.');">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none;border:none;color:#BE123C;font-size:0.78rem;font-weight:600;cursor:pointer;padding:0.4rem;">Delete</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Plan next CTA --}}
    <div class="pai-card" style="background:linear-gradient(135deg,#FAF9FF,#F5F4FF);margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div style="flex:1;min-width:220px;">
            <p style="font-size:0.9rem;font-weight:700;color:#0F0A1E;margin:0 0 0.2rem;">Plan the next book in {{ $series->name }}</p>
            <p style="font-size:0.78rem;color:#5C5470;margin:0;">Characters and style are loaded automatically — just give it a theme.</p>
        </div>
        <a href="{{ route('series.plan', $series) }}" class="btn-primary" style="text-decoration:none;flex-shrink:0;">Plan next book →</a>
    </div>

    {{-- Character Bible --}}
    @if(! empty($series->characters))
    <details class="pai-card" style="margin-bottom:1.25rem;" open>
        <summary style="cursor:pointer;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Character Bible ({{ count($series->characters) }})</summary>
        <div style="margin-top:0.85rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:0.6rem;">
            @foreach($series->characters as $c)
            <div style="background:#F5F4FF;border-radius:8px;padding:0.7rem 0.85rem;">
                <p style="margin:0;font-size:0.85rem;font-weight:700;color:#0F0A1E;">{{ $c['name'] }}</p>
                @if(! empty($c['age_appearance']))<p style="margin:0.2rem 0 0;font-size:0.72rem;color:#5C5470;"><strong>Look:</strong> {{ $c['age_appearance'] }}</p>@endif
                @if(! empty($c['personality']))<p style="margin:0.15rem 0 0;font-size:0.72rem;color:#5C5470;"><strong>Personality:</strong> {{ $c['personality'] }}</p>@endif
                @if(! empty($c['key_detail']))<p style="margin:0.15rem 0 0;font-size:0.72rem;color:#5C5470;"><strong>Signature:</strong> {{ $c['key_detail'] }}</p>@endif
            </div>
            @endforeach
        </div>
    </details>
    @endif

    {{-- Style Guide --}}
    @if($series->art_style || $series->writing_tone || $series->recurring_themes || $series->never_do)
    <details class="pai-card" style="margin-bottom:1.25rem;">
        <summary style="cursor:pointer;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Style Guide</summary>
        <div style="margin-top:0.85rem;font-size:0.8rem;color:#5C5470;line-height:1.7;">
            @if($series->art_style)<p style="margin:0;"><strong>Art style:</strong> {{ $series->art_style }}</p>@endif
            @if($series->writing_tone)<p style="margin:0.25rem 0 0;"><strong>Writing tone:</strong> {{ $series->writing_tone }}</p>@endif
            @if($series->recurring_themes)<p style="margin:0.25rem 0 0;"><strong>Recurring themes:</strong> {{ $series->recurring_themes }}</p>@endif
            @if($series->never_do)<p style="margin:0.25rem 0 0;"><strong>Never does:</strong> {{ $series->never_do }}</p>@endif
        </div>
    </details>
    @endif

    {{-- Books in series --}}
    <div class="pai-card" style="margin-bottom:1.25rem;">
        <h2 style="margin:0 0 0.85rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Books in this series</h2>
        @if($runs->isEmpty())
        <p style="font-size:0.82rem;color:#9B93B0;margin:0;">No books yet. Click "Plan next book" above to start the first one.</p>
        @else
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            @foreach($runs as $r)
            @php
                $sBg = match($r->status){'completed'=>'#ECFDF5','failed'=>'#FFF1F2','running','pending'=>'#EFF6FF','paused'=>'#FFFBEB',default=>'#F5F4FF'};
                $sCol = match($r->status){'completed'=>'#065F46','failed'=>'#BE123C','running','pending'=>'#1D4ED8','paused'=>'#92400E',default=>'#5A2EC9'};
            @endphp
            <div style="display:flex;align-items:center;gap:0.85rem;padding:0.75rem;background:#F5F4FF;border-radius:10px;">
                <a href="{{ route('runs.show', $r) }}" style="flex:1;min-width:0;text-decoration:none;color:inherit;">
                    <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $r->topic }}</p>
                    <p style="font-size:0.72rem;color:#9B93B0;margin:0.15rem 0 0;">{{ $r->created_at->diffForHumans() }}</p>
                </a>
                <span style="background:{{ $sBg }};color:{{ $sCol }};border-radius:999px;padding:3px 10px;font-size:0.7rem;font-weight:600;flex-shrink:0;">{{ ucfirst($r->status) }}</span>
                <form method="POST" action="{{ route('series.detach-run', [$series, $r]) }}" style="margin:0;">
                    @csrf @method('DELETE')
                    <button type="submit" title="Remove from series" style="background:none;border:none;color:#9B93B0;cursor:pointer;font-size:0.85rem;padding:0.25rem 0.4rem;">×</button>
                </form>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Attach existing runs --}}
    @if($orphanRuns->isNotEmpty())
    <details class="pai-card">
        <summary style="cursor:pointer;font-size:0.88rem;font-weight:700;color:#0F0A1E;">Add an existing book to this series</summary>
        <div style="margin-top:0.85rem;display:flex;flex-direction:column;gap:0.4rem;">
            @foreach($orphanRuns as $r)
            <form method="POST" action="{{ route('series.attach-run', [$series, $r]) }}" style="display:flex;align-items:center;gap:0.75rem;padding:0.6rem 0.75rem;background:#FAF9FF;border-radius:8px;margin:0;">
                @csrf
                <p style="flex:1;margin:0;font-size:0.8rem;color:#0F0A1E;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $r->topic }}</p>
                <button type="submit" class="btn-outline" style="padding:0.3rem 0.7rem;font-size:0.7rem;flex-shrink:0;">+ Add</button>
            </form>
            @endforeach
        </div>
    </details>
    @endif

</x-app-layout>
