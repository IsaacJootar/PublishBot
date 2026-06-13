<x-app-layout>
    <x-slot name="title">My Series</x-slot>

    <div style="margin-bottom:1.5rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">My Series</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;max-width:560px;">Group your books into series for shared characters, style, and faster production of every next book.</p>
        </div>
        <a href="{{ route('series.create') }}" class="btn-primary" style="text-decoration:none;flex-shrink:0;">+ Create a new series</a>
    </div>

    @if($series->isEmpty())
    <div class="pai-card" style="text-align:center;padding:2.5rem 1.5rem;">
        <div style="font-size:2.5rem;margin-bottom:0.5rem;">📚</div>
        <p style="font-size:0.95rem;font-weight:700;color:#0F0A1E;margin:0 0 0.4rem;">No series yet</p>
        <p style="font-size:0.82rem;color:#9B93B0;margin:0 0 1.25rem;max-width:380px;margin-left:auto;margin-right:auto;">Series let you lock characters, style, and tone across multiple books — so every new book is faster and more consistent than the last.</p>
        <a href="{{ route('series.create') }}" class="btn-primary" style="text-decoration:none;">+ Create your first series</a>
    </div>
    @else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;">
        @foreach($series as $s)
        <a href="{{ route('series.show', $s) }}" class="pai-card" style="text-decoration:none;color:inherit;border-top:4px solid {{ $s->color }};transition:transform 0.15s,box-shadow 0.15s;"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(108,60,225,0.12)'"
           onmouseout="this.style.transform='none';this.style.boxShadow=''">
            <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.5rem;">
                <span style="font-size:1.5rem;">{{ $s->emoji }}</span>
                <h3 style="margin:0;font-size:1rem;font-weight:700;color:#0F0A1E;line-height:1.2;">{{ $s->name }}</h3>
            </div>
            <p style="font-size:0.78rem;color:#5C5470;margin:0 0 0.85rem;min-height:2.4em;line-height:1.4;">{{ $s->description ?: 'No description' }}</p>
            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                <span style="background:#F5F4FF;color:#5A2EC9;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">{{ $s->runs_count }} books</span>
                <span style="background:#ECFDF5;color:#065F46;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">{{ $s->completed_runs_count }} complete</span>
                @if(! empty($s->characters))
                <span style="background:#FEF3C7;color:#92400E;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">{{ count($s->characters) }} characters</span>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    @endif
</x-app-layout>
