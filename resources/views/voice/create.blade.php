<x-app-layout>
    <x-slot name="title">Add a new domain</x-slot>

    <div style="max-width:560px;">

        <div style="margin-bottom:1.75rem;">
            <a href="{{ route('voice.index') }}" style="font-size:0.8rem;color:#9B93B0;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                My Voice
            </a>
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.25rem;">Add a new domain</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">Give this domain a name and a look. You'll upload writing samples on the next screen.</p>
        </div>

        <form method="POST" action="{{ route('voice.store') }}" onsubmit="document.getElementById('btn-create').textContent='Creating...';document.getElementById('btn-create').style.opacity='0.7';">
            @csrf

            <div class="pai-card">

                {{-- Emoji + Colour row --}}
                <div style="display:grid;grid-template-columns:auto 1fr;gap:1rem;margin-bottom:1.25rem;">

                    {{-- Emoji picker --}}
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">Emoji</label>
                        <div x-data="{ open: false, selected: '{{ request('emoji', '✍️') }}' }" style="position:relative;">
                            <button type="button" @click="open = !open"
                                style="width:52px;height:42px;border:1.5px solid #E4E0F0;border-radius:10px;background:#FAFAFF;font-size:1.4rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                                <span x-text="selected"></span>
                            </button>
                            <input type="hidden" name="emoji" :value="selected">
                            <div x-show="open" @click.outside="open=false" x-cloak
                                style="position:absolute;top:48px;left:0;background:#fff;border:1px solid #E4E0F0;border-radius:10px;padding:0.625rem;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:50;display:grid;grid-template-columns:repeat(6,1fr);gap:4px;width:200px;">
                                @foreach(['✍️','📱','💼','🧒','🙏','💻','🌿','💰','🎯','📚','🚀','💡','🎨','🧠','⚡','🌍'] as $e)
                                <button type="button" @click="selected='{{ $e }}'; open=false"
                                    style="width:28px;height:28px;border:none;background:none;cursor:pointer;font-size:1rem;border-radius:6px;display:flex;align-items:center;justify-content:center;"
                                    onmouseover="this.style.background='#F0EBFF'" onmouseout="this.style.background='none'">{{ $e }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Domain name --}}
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">Domain name <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="name" value="{{ old('name', request('name')) }}"
                            placeholder="e.g. Children's Content"
                            class="pai-input" required autofocus />
                        @error('name')<p style="color:#EF4444;font-size:0.75rem;margin:0.25rem 0 0;">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Description --}}
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">What topics will this cover? <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                    <input type="text" name="domain_description" value="{{ old('domain_description', request('desc')) }}"
                        placeholder="e.g. Children's stories, educational books, picture books for ages 3-8"
                        class="pai-input" />
                </div>

                {{-- Colour picker — pure JS, no Alpine conflict --}}
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.6rem;">Colour tag</label>
                    <div style="display:flex;flex-wrap:wrap;gap:0.5rem;" id="color-picker">
                        <input type="hidden" name="color" id="color-value" value="{{ old('color', request('color', '#6C3CE1')) }}">
                        @foreach(['#6C3CE1','#10B981','#3B82F6','#F59E0B','#F43F5E','#06B6D4','#84CC16','#F97316'] as $c)
                        <button type="button"
                            data-color="{{ $c }}"
                            onclick="pickColor(this)"
                            style="width:30px;height:30px;border-radius:50%;border:3px solid transparent;cursor:pointer;background:{{ $c }};transition:border-color 0.15s,transform 0.1s;flex-shrink:0;"
                            onmouseover="this.style.transform='scale(1.15)'"
                            onmouseout="this.style.transform='scale(1)'">
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Default toggle — pure JS --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.875rem;background:#F5F4FF;border-radius:10px;margin-bottom:1.25rem;cursor:pointer;" onclick="toggleDefault()">
                    <div>
                        <p style="font-size:0.85rem;font-weight:600;color:#0F0A1E;margin:0 0 0.1rem;">Use this as my default voice</p>
                        <p style="font-size:0.75rem;color:#9B93B0;margin:0;">Applied automatically when no other voice is selected</p>
                    </div>
                    <div id="toggle-track" style="width:42px;height:24px;border-radius:999px;background:#E4E0F0;position:relative;flex-shrink:0;transition:background 0.2s;">
                        <div id="toggle-thumb" style="position:absolute;top:3px;left:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform 0.2s;box-shadow:0 1px 4px rgba(0,0,0,0.2);"></div>
                    </div>
                    <input type="hidden" name="is_default" id="is-default-val" value="{{ old('is_default', 0) }}">
                </div>

                <button type="submit" class="btn-primary" style="width:100%;" id="btn-create">
                    Create domain →
                </button>

                <script>
                // Colour picker
                function pickColor(btn) {
                    document.querySelectorAll('#color-picker button').forEach(b => b.style.border = '3px solid transparent');
                    btn.style.border = '3px solid #0F0A1E';
                    document.getElementById('color-value').value = btn.dataset.color;
                }
                // Init selected color
                (function() {
                    const current = document.getElementById('color-value').value;
                    document.querySelectorAll('#color-picker button').forEach(b => {
                        if (b.dataset.color === current) b.style.border = '3px solid #0F0A1E';
                    });
                })();

                // Toggle
                let defaultOn = {{ old('is_default', 0) ? 'true' : 'false' }};
                function toggleDefault() {
                    defaultOn = !defaultOn;
                    document.getElementById('is-default-val').value = defaultOn ? 1 : 0;
                    document.getElementById('toggle-track').style.background = defaultOn ? '#6C3CE1' : '#E4E0F0';
                    document.getElementById('toggle-thumb').style.transform = defaultOn ? 'translateX(18px)' : 'translateX(0)';
                }
                if (defaultOn) toggleDefault();
                </script>
            </div>
        </form>
    </div>

</x-app-layout>
