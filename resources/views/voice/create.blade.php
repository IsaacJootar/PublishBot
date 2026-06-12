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

        <form method="POST" action="{{ route('voice.store') }}">
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

                {{-- Colour picker --}}
                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.6rem;">Colour tag</label>
                    <div x-data="{ selected: '{{ request('color', '#6C3CE1') }}' }" style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                        <input type="hidden" name="color" :value="selected">
                        @foreach(['#6C3CE1','#10B981','#3B82F6','#F59E0B','#F43F5E','#06B6D4','#84CC16','#F97316'] as $c)
                        <button type="button" @click="selected='{{ $c }}'"
                            :style="selected === '{{ $c }}' ? 'outline:2px solid #0F0A1E;outline-offset:2px;' : ''"
                            style="width:28px;height:28px;border-radius:50%;border:none;cursor:pointer;background:{{ $c }};transition:transform 0.1s;"
                            onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Default toggle --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.875rem;background:#F5F4FF;border-radius:10px;margin-bottom:1.25rem;">
                    <div>
                        <p style="font-size:0.85rem;font-weight:600;color:#0F0A1E;margin:0 0 0.1rem;">Use this as my default voice</p>
                        <p style="font-size:0.75rem;color:#9B93B0;margin:0;">Applied automatically when no other voice is selected</p>
                    </div>
                    <label style="position:relative;display:inline-block;width:40px;height:22px;flex-shrink:0;">
                        <input type="checkbox" name="is_default" value="1" {{ old('is_default') ? 'checked' : '' }}
                            style="opacity:0;width:0;height:0;position:absolute;">
                        <span onclick="this.previousElementSibling.click(); this.style.background = this.previousElementSibling.checked ? '#6C3CE1' : '#E4E0F0';"
                            style="position:absolute;inset:0;border-radius:999px;background:#E4E0F0;cursor:pointer;transition:background 0.2s;">
                            <span style="position:absolute;top:3px;left:3px;width:16px;height:16px;border-radius:50%;background:#fff;transition:transform 0.2s;"></span>
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    Create domain and upload samples →
                </button>
            </div>
        </form>
    </div>

</x-app-layout>
