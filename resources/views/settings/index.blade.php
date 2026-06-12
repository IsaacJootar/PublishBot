<x-app-layout>
    <x-slot name="title">Settings</x-slot>

    <div style="max-width:680px;">

        <div style="margin-bottom:1.75rem;">
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.25rem;">Settings</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">Configure your API key and pipeline defaults.</p>
        </div>

        <form method="POST" action="{{ route('settings.update') }}"
              onsubmit="document.getElementById('btn-save').textContent='Saving...';document.getElementById('btn-save').style.opacity='0.7';">
            @csrf

            {{-- API Configuration --}}
            <div class="pai-card" style="margin-bottom:1.25rem;">
                <div style="margin-bottom:1.25rem;">
                    <h2 style="font-size:1rem;font-weight:700;color:#0F0A1E;margin:0 0 0.2rem;">AI Configuration</h2>
                    <p style="font-size:0.8rem;color:#9B93B0;margin:0;">Your Anthropic API key powers the entire pipeline. Get yours at <span style="font-family:monospace;background:#F5F4FF;padding:1px 5px;border-radius:4px;">console.anthropic.com</span></p>
                </div>

                {{-- API Key --}}
                <div style="margin-bottom:1rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
                        <label style="font-size:0.8rem;font-weight:600;color:#0F0A1E;">Anthropic API key <span style="color:#EF4444;">*</span></label>
                        @if($settings->api_key_encrypted)
                        <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">✓ Saved</span>
                        @endif
                    </div>
                    <input type="password" name="api_key"
                        placeholder="{{ $settings->api_key_encrypted ? '•••••••••••••••••••• (leave blank to keep current)' : 'sk-ant-api03-...' }}"
                        class="pai-input" autocomplete="off" style="font-family:monospace;font-size:0.85rem;" />
                </div>

                {{-- Groq key (free fallback) --}}
                <div style="margin-bottom:1rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.4rem;">
                        <label style="font-size:0.8rem;font-weight:600;color:#0F0A1E;">
                            Groq API key
                            <span style="background:#ECFDF5;color:#065F46;border-radius:999px;padding:1px 7px;font-size:0.68rem;font-weight:700;margin-left:5px;">FREE</span>
                        </label>
                        @if($settings->groq_api_key)
                        <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">✓ Saved</span>
                        @endif
                    </div>
                    <input type="password" name="groq_api_key"
                        placeholder="{{ $settings->groq_api_key ? '•••••••••••••••••••• (leave blank to keep)' : 'gsk_...' }}"
                        class="pai-input" autocomplete="off" style="font-family:monospace;font-size:0.85rem;" />
                    <p style="font-size:0.75rem;color:#9B93B0;margin:0.3rem 0 0;">
                        Free at <span style="font-family:monospace;background:#F5F4FF;padding:1px 5px;border-radius:4px;">console.groq.com</span> — runs Llama 3.3 70B. Used automatically if Claude is unavailable.
                    </p>
                </div>

                {{-- Model --}}
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">Model</label>
                    <select name="model" class="pai-input" style="cursor:pointer;">
                        @foreach(['claude-sonnet-4-6','claude-opus-4-5','claude-haiku-4-5'] as $m)
                        <option value="{{ $m }}" {{ $settings->model === $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                    <p style="font-size:0.75rem;color:#9B93B0;margin:0.3rem 0 0;">claude-sonnet-4-6 is recommended — best balance of quality and cost.</p>
                </div>

                {{-- Max tokens --}}
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">Max tokens per call</label>
                    <input type="number" name="max_tokens" value="{{ old('max_tokens', $settings->max_tokens) }}"
                        min="500" max="8000" class="pai-input" />
                    <p style="font-size:0.75rem;color:#9B93B0;margin:0.3rem 0 0;">Default: 2000. Increase for longer chapters or more detailed reports.</p>
                </div>

                {{-- Web search info --}}
                <div style="background:#F5F4FF;border-radius:10px;padding:0.875rem 1rem;border-left:3px solid #6C3CE1;">
                    <p style="font-size:0.8rem;font-weight:600;color:#5A2EC9;margin:0 0 0.2rem;">Live web search — always on for Stage 1</p>
                    <p style="font-size:0.78rem;color:#5C5470;margin:0;">Using: <span style="font-family:monospace;font-size:0.75rem;">{{ $settings->web_search_tool_version }}</span> — Anthropic native, no extra API key needed.</p>
                </div>
            </div>

            {{-- Pipeline Defaults --}}
            <div class="pai-card" style="margin-bottom:1.25rem;">
                <div style="margin-bottom:1.25rem;">
                    <h2 style="font-size:1rem;font-weight:700;color:#0F0A1E;margin:0 0 0.2rem;">Pipeline Defaults</h2>
                    <p style="font-size:0.8rem;color:#9B93B0;margin:0;">These apply to every new pipeline run.</p>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">Chapters <span style="color:#9B93B0;font-weight:400;">(6–12)</span></label>
                        <input type="number" name="chapter_count" value="{{ old('chapter_count', $settings->chapter_count) }}"
                            min="6" max="12" class="pai-input" />
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">Words/chapter <span style="color:#9B93B0;font-weight:400;">(300–800)</span></label>
                        <input type="number" name="words_per_chapter" value="{{ old('words_per_chapter', $settings->words_per_chapter) }}"
                            min="300" max="800" class="pai-input" />
                    </div>
                    <div>
                        <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">Pinterest pins <span style="color:#9B93B0;font-weight:400;">(10–25)</span></label>
                        <input type="number" name="pin_count" value="{{ old('pin_count', $settings->pin_count) }}"
                            min="10" max="25" class="pai-input" />
                    </div>
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">Default writing tone</label>
                    <input type="text" name="default_tone" value="{{ old('default_tone', $settings->default_tone) }}"
                        placeholder="e.g. warm and encouraging, clear and direct, motivational"
                        class="pai-input" />
                    <p style="font-size:0.75rem;color:#9B93B0;margin:0.3rem 0 0;">Injected into all writing prompts. Leave blank for auto tone per topic.</p>
                </div>

                <div>
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">Author name</label>
                    <input type="text" name="author_name" value="{{ old('author_name', $settings->author_name) }}"
                        placeholder="e.g. Isaac Jootar"
                        class="pai-input" />
                    <p style="font-size:0.75rem;color:#9B93B0;margin:0.3rem 0 0;">Used in listing copy where an author name is appropriate.</p>
                </div>
            </div>

            {{-- Quick Topics --}}
            <div class="pai-card" style="margin-bottom:1.25rem;">
                <div style="margin-bottom:1rem;">
                    <h2 style="font-size:1rem;font-weight:700;color:#0F0A1E;margin:0 0 0.2rem;">Quick topic tags</h2>
                    <p style="font-size:0.8rem;color:#9B93B0;margin:0;">Shown on the dashboard as one-click topic starters. Comma-separated.</p>
                </div>
                <textarea name="quick_topics" rows="3" class="pai-input" style="resize:vertical;">{{ old('quick_topics', implode(', ', $settings->quick_topics ?? [])) }}</textarea>
            </div>

            {{-- Actions --}}
            <div style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:center;">
                <button type="submit" class="btn-primary" id="btn-save">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save settings
                </button>
                <button type="button" class="btn-outline" id="btn-test" onclick="testConnection(this)">
                    Test connection
                </button>
            </div>
        </form>
    </div>

    <script>
    function testConnection(btn) {
        btn.disabled = true;
        btn.textContent = 'Connecting...';
        fetch('{{ route('settings.test') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => window.showToast({ message: data.message, type: data.success ? 'success' : 'error' }))
        .catch(() => window.showToast({ message: 'Test failed. Try again.', type: 'error' }))
        .finally(() => { btn.disabled = false; btn.textContent = 'Test connection'; });
    }
    </script>

    <style>
        @media (max-width:640px) {
            [style*="grid-template-columns:1fr 1fr 1fr"] { grid-template-columns: 1fr !important; }
        }
    </style>

</x-app-layout>
