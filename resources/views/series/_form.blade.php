@php
    $isEdit = isset($series);
    $action = $isEdit ? route('series.update', $series) : route('series.store');
    $characters = $isEdit ? ($series->characters ?? []) : [];
    if (empty($characters)) {
        $characters = [['name' => '', 'age_appearance' => '', 'personality' => '', 'key_detail' => '']];
    }
@endphp
<form method="POST" action="{{ $action }}"
      onsubmit="document.getElementById('btn-save-series').textContent='Saving...';document.getElementById('btn-save-series').style.opacity='0.7';">
    @csrf
    @if($isEdit) @method('PATCH') @endif

    <div class="pai-card" style="margin-bottom:1.25rem;">
        <h2 style="margin:0 0 0.85rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Basics</h2>

        <div style="display:flex;gap:0.75rem;align-items:flex-start;margin-bottom:1rem;flex-wrap:wrap;">
            <div>
                <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Emoji</label>
                <div style="display:flex;flex-wrap:wrap;gap:0.3rem;max-width:240px;">
                    @foreach(['📚','✍️','🧒','🦸','🐉','🌟','🌙','🚀','🦄','🧚','🦊','🐘'] as $em)
                    <button type="button" onclick="document.getElementById('series-emoji').value='{{ $em }}';document.querySelectorAll('.s-em').forEach(b=>b.style.background='#F5F4FF');this.style.background='#EDE9FF';"
                        class="s-em"
                        style="background:{{ ($isEdit && $series->emoji === $em) ? '#EDE9FF' : '#F5F4FF' }};border:1px solid #E4E0F0;border-radius:8px;padding:0.4rem 0.55rem;font-size:1rem;cursor:pointer;">{{ $em }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="emoji" id="series-emoji" value="{{ $isEdit ? $series->emoji : '📚' }}">
            </div>
            <div>
                <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Colour</label>
                <div style="display:flex;flex-wrap:wrap;gap:0.4rem;">
                    @foreach(['#6C3CE1','#EC4899','#0EA5E9','#F59E0B','#8B5CF6','#10B981','#22C55E','#EAB308'] as $c)
                    <button type="button" onclick="document.getElementById('series-color').value='{{ $c }}';document.querySelectorAll('.s-c').forEach(b=>b.style.borderColor='transparent');this.style.borderColor='#0F0A1E';"
                        class="s-c"
                        style="width:28px;height:28px;border-radius:8px;background:{{ $c }};border:2px solid {{ ($isEdit && $series->color === $c) ? '#0F0A1E' : 'transparent' }};cursor:pointer;"></button>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="series-color" value="{{ $isEdit ? $series->color : '#6C3CE1' }}">
            </div>
        </div>

        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Series name <span style="color:#EF4444;">*</span></label>
        <input type="text" name="name" required class="pai-input" style="margin-bottom:1rem;" value="{{ old('name', $isEdit ? $series->name : '') }}" placeholder="e.g. The Brave Little Coders"/>

        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">One-sentence description</label>
        <textarea name="description" rows="2" class="pai-input" style="resize:vertical;margin-bottom:1rem;" placeholder="What is this series about?">{{ old('description', $isEdit ? $series->description : '') }}</textarea>

        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Format / category</label>
        <input type="text" name="format" class="pai-input" value="{{ old('format', $isEdit ? $series->format : '') }}" placeholder="e.g. Children's picture books, Non-fiction guides, Devotionals"/>
    </div>

    <div class="pai-card" style="margin-bottom:1.25rem;">
        <h2 style="margin:0 0 0.2rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Character Bible</h2>
        <p style="font-size:0.78rem;color:#9B93B0;margin:0 0 1rem;">Lock your characters so every book looks and sounds consistent.</p>

        <div id="char-list">
            @foreach($characters as $i => $c)
            <div class="char-row" style="background:#F5F4FF;border-radius:10px;padding:0.85rem;margin-bottom:0.6rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;">
                    <input type="text" name="characters[{{ $i }}][name]" class="pai-input" placeholder="Name" value="{{ $c['name'] ?? '' }}"/>
                    <input type="text" name="characters[{{ $i }}][age_appearance]" class="pai-input" placeholder="Age / appearance" value="{{ $c['age_appearance'] ?? '' }}"/>
                    <input type="text" name="characters[{{ $i }}][personality]" class="pai-input" placeholder="Personality (2–3 words)" value="{{ $c['personality'] ?? '' }}"/>
                    <input type="text" name="characters[{{ $i }}][key_detail]" class="pai-input" placeholder="Key visual / always wears..." value="{{ $c['key_detail'] ?? '' }}"/>
                </div>
                <button type="button" onclick="this.closest('.char-row').remove()" style="background:none;border:none;color:#BE123C;font-size:0.7rem;font-weight:600;cursor:pointer;margin-top:0.4rem;padding:0;">Remove</button>
            </div>
            @endforeach
        </div>
        <button type="button" onclick="addCharacter()" class="btn-outline" style="padding:0.4rem 0.8rem;font-size:0.78rem;">+ Add another character</button>
    </div>

    <div class="pai-card" style="margin-bottom:1.25rem;">
        <h2 style="margin:0 0 0.85rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Style Guide</h2>

        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Art style (for illustration prompts)</label>
        <input type="text" name="art_style" class="pai-input" style="margin-bottom:1rem;" value="{{ old('art_style', $isEdit ? $series->art_style : '') }}" placeholder="e.g. Soft watercolour, warm pastels, hand-drawn feel"/>

        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Writing tone</label>
        <input type="text" name="writing_tone" class="pai-input" style="margin-bottom:1rem;" value="{{ old('writing_tone', $isEdit ? $series->writing_tone : '') }}" placeholder="e.g. Warm, encouraging, slightly funny"/>

        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Recurring themes</label>
        <input type="text" name="recurring_themes" class="pai-input" style="margin-bottom:1rem;" value="{{ old('recurring_themes', $isEdit ? $series->recurring_themes : '') }}" placeholder="e.g. Curiosity, courage, kindness"/>

        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Things this series never does</label>
        <input type="text" name="never_do" class="pai-input" value="{{ old('never_do', $isEdit ? $series->never_do : '') }}" placeholder="e.g. Never preachy, never scary"/>
    </div>

    <button type="submit" id="btn-save-series" class="btn-primary" style="width:100%;padding:0.8rem;">
        {{ $isEdit ? 'Save changes' : 'Create my series →' }}
    </button>
</form>

<script>
let charIdx = {{ count($characters) }};
function addCharacter() {
    const html = `<div class="char-row" style="background:#F5F4FF;border-radius:10px;padding:0.85rem;margin-bottom:0.6rem;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.6rem;">
            <input type="text" name="characters[${charIdx}][name]" class="pai-input" placeholder="Name"/>
            <input type="text" name="characters[${charIdx}][age_appearance]" class="pai-input" placeholder="Age / appearance"/>
            <input type="text" name="characters[${charIdx}][personality]" class="pai-input" placeholder="Personality (2–3 words)"/>
            <input type="text" name="characters[${charIdx}][key_detail]" class="pai-input" placeholder="Key visual / always wears..."/>
        </div>
        <button type="button" onclick="this.closest('.char-row').remove()" style="background:none;border:none;color:#BE123C;font-size:0.7rem;font-weight:600;cursor:pointer;margin-top:0.4rem;padding:0;">Remove</button>
    </div>`;
    document.getElementById('char-list').insertAdjacentHTML('beforeend', html);
    charIdx++;
}
</script>
