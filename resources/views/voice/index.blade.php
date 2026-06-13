<x-app-layout>
    <x-slot name="title">My Voice</x-slot>

    {{-- Header --}}
    <div style="margin-bottom:1.5rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div>
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">My Voice</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;max-width:560px;">Each domain profile learns how you write about a specific topic. The more you train it, the more it sounds like you.</p>
        </div>
        <button type="button" onclick="document.getElementById('create-modal').style.display='flex'"
                class="btn-primary" style="flex-shrink:0;">
            + Add a new domain
        </button>
    </div>

    {{-- Existing profiles --}}
    @if($profiles->isNotEmpty())
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem;margin-bottom:2rem;">
        @foreach($profiles as $p)
        <div class="pai-card" style="border-top:4px solid {{ $p->color }};position:relative;">
            @if($p->is_default)
            <span style="position:absolute;top:0.75rem;right:0.75rem;background:#FFFBEB;color:#92400E;border:1px solid #FDE68A;border-radius:999px;padding:2px 8px;font-size:0.68rem;font-weight:700;">★ Default</span>
            @endif

            <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.5rem;">
                <span style="font-size:1.5rem;">{{ $p->emoji }}</span>
                <h3 style="margin:0;font-size:0.95rem;font-weight:700;color:#0F0A1E;">{{ $p->name }}</h3>
            </div>
            <p style="font-size:0.78rem;color:#9B93B0;margin:0 0 0.75rem;min-height:2.4em;">{{ $p->description ?: 'No description' }}</p>

            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:0.75rem;">
                @php
                    $statusBg = match($p->status){'ready'=>'#ECFDF5','training'=>'#EFF6FF','failed'=>'#FFF1F2',default=>'#F5F4FF'};
                    $statusColor = match($p->status){'ready'=>'#065F46','training'=>'#1D4ED8','failed'=>'#BE123C',default=>'#5A2EC9'};
                    $statusLabel = match($p->status){'ready'=>'Ready','training'=>'Training...','failed'=>'Failed','draft'=>'Draft',default=>$p->status};
                @endphp
                <span style="background:{{ $statusBg }};color:{{ $statusColor }};border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">{{ $statusLabel }}</span>
                <span style="background:#F5F4FF;color:#5A2EC9;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">{{ number_format($p->word_count) }} words</span>
            </div>

            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                <a href="{{ route('voice.show', $p) }}" class="btn-outline" style="padding:0.4rem 0.75rem;font-size:0.75rem;">Train</a>
                @unless($p->is_default)
                <form method="POST" action="{{ route('voice.default', $p) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-outline" style="padding:0.4rem 0.75rem;font-size:0.75rem;">Set default</button>
                </form>
                @endunless
                <form method="POST" action="{{ route('voice.destroy', $p) }}" style="display:inline;" onsubmit="return confirm('Delete this voice profile?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background:none;border:none;color:#BE123C;font-size:0.75rem;font-weight:600;cursor:pointer;padding:0.4rem 0.5rem;">Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Suggested starters (always shown if user has fewer than 3 profiles) --}}
    @if($profiles->count() < 3)
    <div class="pai-card" style="background:#FAF9FF;">
        <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0 0 0.2rem;">{{ $profiles->isEmpty() ? 'Start with a suggested domain' : 'Add another domain' }}</p>
        <p style="font-size:0.78rem;color:#9B93B0;margin:0 0 1rem;">Click to pre-fill the form. You can edit anything.</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.6rem;">
            @foreach($suggested as $s)
            <button type="button" onclick="openCreateModal(this)"
                data-emoji="{{ $s['emoji'] }}"
                data-name="{{ $s['name'] }}"
                data-description="{{ $s['description'] }}"
                data-color="{{ $s['color'] }}"
                style="background:#fff;border:1px solid #E4E0F0;border-radius:10px;padding:0.7rem;cursor:pointer;text-align:left;transition:border-color 0.15s,box-shadow 0.15s;"
                onmouseover="this.style.borderColor='{{ $s['color'] }}';this.style.boxShadow='0 2px 8px rgba(108,60,225,0.08)'"
                onmouseout="this.style.borderColor='#E4E0F0';this.style.boxShadow='none'">
                <div style="font-size:1.25rem;margin-bottom:0.25rem;">{{ $s['emoji'] }}</div>
                <div style="font-size:0.8rem;font-weight:700;color:#0F0A1E;line-height:1.2;">{{ $s['name'] }}</div>
                <div style="font-size:0.7rem;color:#9B93B0;margin-top:2px;">{{ $s['description'] }}</div>
            </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Create modal --}}
    <div id="create-modal" style="display:none;position:fixed;inset:0;background:rgba(15,10,30,0.55);z-index:50;align-items:center;justify-content:center;padding:1rem;">
        <div style="background:#fff;border-radius:14px;padding:1.5rem;max-width:480px;width:100%;max-height:90vh;overflow-y:auto;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <h2 style="margin:0;font-size:1.05rem;font-weight:800;color:#0F0A1E;">Create a new domain</h2>
                <button type="button" onclick="document.getElementById('create-modal').style.display='none'" style="background:none;border:none;font-size:1.5rem;color:#9B93B0;cursor:pointer;">&times;</button>
            </div>

            <form method="POST" action="{{ route('voice.store') }}">
                @csrf

                <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Emoji</label>
                <div style="display:flex;flex-wrap:wrap;gap:0.3rem;margin-bottom:0.9rem;" id="emoji-picker">
                    @foreach(['✍️','📱','💼','🧒','🙏','💻','🌿','💰','📚','🎨','🎯','🔥'] as $em)
                    <button type="button" onclick="pickEmoji('{{ $em }}')"
                        class="emoji-pick"
                        style="background:#F5F4FF;border:1px solid #E4E0F0;border-radius:8px;padding:0.4rem 0.55rem;font-size:1rem;cursor:pointer;">{{ $em }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="emoji" id="emoji-field" value="✍️">

                <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Colour</label>
                <div style="display:flex;flex-wrap:wrap;gap:0.4rem;margin-bottom:0.9rem;">
                    @foreach(['#6C3CE1','#EC4899','#0EA5E9','#F59E0B','#8B5CF6','#10B981','#22C55E','#EAB308'] as $c)
                    <button type="button" onclick="pickColor('{{ $c }}')"
                        class="color-pick"
                        data-color="{{ $c }}"
                        style="width:28px;height:28px;border-radius:8px;background:{{ $c }};border:2px solid transparent;cursor:pointer;"></button>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="color-field" value="#6C3CE1">

                <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Domain name <span style="color:#EF4444;">*</span></label>
                <input type="text" name="name" id="name-field" required class="pai-input" style="margin-bottom:0.9rem;" placeholder="e.g. Social Media & Algorithms"/>

                <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">What topics will this cover?</label>
                <textarea name="description" id="desc-field" rows="2" class="pai-input" style="resize:vertical;margin-bottom:0.9rem;" placeholder="Instagram, LinkedIn, X, TikTok, content strategy..."></textarea>

                <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;color:#0F0A1E;margin-bottom:1.25rem;cursor:pointer;">
                    <input type="checkbox" name="is_default" value="1" style="width:16px;height:16px;cursor:pointer;">
                    Set as my default profile
                </label>

                <button type="submit" class="btn-primary" style="width:100%;">Create domain →</button>
            </form>
        </div>
    </div>

    <script>
    function pickEmoji(em) {
        document.getElementById('emoji-field').value = em;
        document.querySelectorAll('.emoji-pick').forEach(b => b.style.background = '#F5F4FF');
        event.target.style.background = '#EDE9FF';
    }
    function pickColor(c) {
        document.getElementById('color-field').value = c;
        document.querySelectorAll('.color-pick').forEach(b => b.style.borderColor = 'transparent');
        event.target.style.borderColor = '#0F0A1E';
    }
    function openCreateModal(btn) {
        document.getElementById('emoji-field').value = btn.dataset.emoji;
        document.getElementById('color-field').value = btn.dataset.color;
        document.getElementById('name-field').value = btn.dataset.name;
        document.getElementById('desc-field').value = btn.dataset.description;
        document.getElementById('create-modal').style.display = 'flex';
    }
    </script>
</x-app-layout>
