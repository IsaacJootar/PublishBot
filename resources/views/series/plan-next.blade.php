<x-app-layout>
    <x-slot name="title">Plan next book</x-slot>

    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('series.show', $series) }}" style="font-size:0.78rem;color:#6C3CE1;text-decoration:none;font-weight:600;">← Back to {{ $series->name }}</a>
    </div>

    <div style="max-width:680px;">
        <div style="margin-bottom:1.5rem;">
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">Plan the next book</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">Series settings are pre-loaded. Just give it a theme.</p>
        </div>

        {{-- Locked context --}}
        <div class="pai-card" style="background:#F5F4FF;border-left:3px solid {{ $series->color }};margin-bottom:1.25rem;">
            <p style="margin:0 0 0.5rem;font-size:0.75rem;font-weight:700;color:#5A2EC9;text-transform:uppercase;letter-spacing:1.5px;">Locked from series</p>
            <p style="margin:0 0 0.3rem;font-size:0.85rem;color:#0F0A1E;"><strong>{{ $series->emoji }} {{ $series->name }}</strong></p>
            @if($series->format)
            <p style="margin:0 0 0.2rem;font-size:0.78rem;color:#5C5470;"><strong>Format:</strong> {{ $series->format }}</p>
            @endif
            @if(! empty($series->characters))
            <p style="margin:0 0 0.2rem;font-size:0.78rem;color:#5C5470;"><strong>Characters:</strong> {{ implode(', ', array_map(fn($c) => $c['name'] ?? '', $series->characters)) }}</p>
            @endif
            @if($series->writing_tone)
            <p style="margin:0 0 0.2rem;font-size:0.78rem;color:#5C5470;"><strong>Tone:</strong> {{ $series->writing_tone }}</p>
            @endif
            @if($series->art_style)
            <p style="margin:0;font-size:0.78rem;color:#5C5470;"><strong>Art style:</strong> {{ $series->art_style }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('series.plan.store', $series) }}"
              onsubmit="document.getElementById('btn-plan').textContent='Starting...';document.getElementById('btn-plan').style.opacity='0.7';">
            @csrf

            <div class="pai-card" style="margin-bottom:1.25rem;">
                <label style="display:block;font-size:0.85rem;font-weight:700;color:#0F0A1E;margin-bottom:0.3rem;">What's the theme or problem for this book? <span style="color:#EF4444;">*</span></label>
                <p style="font-size:0.75rem;color:#9B93B0;margin:0 0 0.6rem;">e.g. "Learning to share toys" or "First day at school"</p>
                <input type="text" name="topic" required class="pai-input" maxlength="200" autofocus placeholder="Type the theme for the next book..."/>
            </div>

            <button type="submit" id="btn-plan" class="btn-primary" style="width:100%;padding:0.8rem;">
                Start the next book →
            </button>
        </form>
    </div>
</x-app-layout>
