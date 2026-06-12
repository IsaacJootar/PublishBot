<x-app-layout>
    <x-slot name="title">Settings</x-slot>

    <div style="max-width: 680px;">

        {{-- Page header --}}
        <div style="margin-bottom: 1.75rem;">
            <h1 style="font-size:1.375rem; font-weight:800; color:#0F0A1E; margin:0 0 0.25rem;">Settings</h1>
            <p style="font-size:0.875rem; color:#5C5470; margin:0;">Configure your API keys and author profile.</p>
        </div>

        {{-- Author profile card --}}
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf

            <div class="pai-card" style="margin-bottom: 1.25rem;">
                <div style="margin-bottom: 1.25rem;">
                    <h2 style="font-size:1rem; font-weight:700; color:#0F0A1E; margin:0 0 0.25rem;">Author Profile</h2>
                    <p style="font-size:0.8rem; color:#9B93B0; margin:0;">Your name appears on book covers and exported documents.</p>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0;">
                    <div>
                        <label style="display:block; font-size:0.8rem; font-weight:600; color:#0F0A1E; margin-bottom:0.4rem;">
                            Your name
                        </label>
                        <input
                            type="text"
                            name="author_name"
                            value="{{ old('author_name', $user->author_name) }}"
                            placeholder="e.g. Isaac Jootar"
                            class="pai-input"
                        />
                    </div>
                    <div>
                        <label style="display:block; font-size:0.8rem; font-weight:600; color:#0F0A1E; margin-bottom:0.4rem;">
                            Pen name <span style="color:#9B93B0; font-weight:400;">(optional)</span>
                        </label>
                        <input
                            type="text"
                            name="pen_name"
                            value="{{ old('pen_name', $user->pen_name) }}"
                            placeholder="e.g. I.J. Books"
                            class="pai-input"
                        />
                    </div>
                </div>

                @if ($errors->any())
                    <div style="margin-top:0.75rem; padding:0.75rem 1rem; background:#FFF1F2; border:1px solid #FECDD3; border-radius:8px; color:#BE123C; font-size:0.825rem;">
                        {{ $errors->first() }}
                    </div>
                @endif
            </div>

            {{-- API Keys card --}}
            <div class="pai-card" style="margin-bottom: 1.25rem;">
                <div style="margin-bottom: 1.25rem;">
                    <h2 style="font-size:1rem; font-weight:700; color:#0F0A1E; margin:0 0 0.25rem;">AI API Keys</h2>
                    <p style="font-size:0.8rem; color:#9B93B0; margin:0;">Keys are encrypted and never shown in plain text.</p>
                </div>

                {{-- Where to get keys --}}
                <div style="background:#F0EBFF; border-radius:10px; padding:0.875rem 1rem; margin-bottom:1.25rem; border-left:3px solid #6C3CE1;">
                    <p style="font-size:0.8rem; font-weight:600; color:#5A2EC9; margin:0 0 0.25rem;">Where to get your keys</p>
                    <p style="font-size:0.78rem; color:#5C5470; margin:0; line-height:1.5;">
                        Claude key → <span style="font-family:monospace; background:#E4E0F0; padding:1px 5px; border-radius:4px;">console.anthropic.com</span> &nbsp;·&nbsp;
                        OpenAI key → <span style="font-family:monospace; background:#E4E0F0; padding:1px 5px; border-radius:4px;">platform.openai.com</span>
                    </p>
                </div>

                {{-- Claude API key --}}
                <div style="margin-bottom:1rem;">
                    <div style="margin-bottom:0.4rem;">
                        <label style="font-size:0.8rem; font-weight:600; color:#0F0A1E;">
                            Claude API key <span style="color:#EF4444;">*</span>
                        </label>
                    </div>
                    <input
                        type="password"
                        name="anthropic_api_key"
                        placeholder="{{ $user->anthropic_api_key ? '••••••••••••  (leave blank to keep current)' : 'sk-ant-api03-...' }}"
                        class="pai-input"
                        autocomplete="off"
                        style="font-family: monospace; font-size: 0.85rem;"
                    />
                </div>

                {{-- OpenAI key --}}
                <div>
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.4rem;">
                        <label style="font-size:0.8rem; font-weight:600; color:#0F0A1E;">
                            OpenAI API key
                            <span style="background:#F0EBFF;color:#5A2EC9;border-radius:999px;padding:1px 7px;font-size:0.68rem;font-weight:600;margin-left:5px;">Backup AI</span>
                        </label>
                        @if($user->openai_api_key)
                        <span style="display:inline-flex;align-items:center;gap:4px;background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Saved
                        </span>
                        @endif
                    </div>
                    <input
                        type="password"
                        name="openai_api_key"
                        placeholder="{{ $user->openai_api_key ? '••••••••••••  (leave blank to keep current)' : 'sk-...' }}"
                        class="pai-input"
                        autocomplete="off"
                        style="font-family: monospace; font-size: 0.85rem;"
                    />
                    <p style="font-size:0.75rem; color:#9B93B0; margin:0.35rem 0 0;">If Claude goes down, PublishAI silently switches to this. Optional.</p>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.75rem; margin-bottom:1.5rem;">

                {{-- Save: plain submit, no Alpine — page redirects naturally --}}
                <button type="submit" class="btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save settings
                </button>

                {{-- Test connection --}}
                <button type="button" class="btn-outline" id="btn-test-connection" onclick="testConnection(this)">
                    Test connection
                </button>

            </div>
        </form>

        {{-- Cost estimates --}}
        <div class="pai-card">
            <div style="margin-bottom:1rem;">
                <h2 style="font-size:1rem; font-weight:700; color:#0F0A1E; margin:0 0 0.25rem;">Estimated AI costs</h2>
                <p style="font-size:0.8rem; color:#9B93B0; margin:0;">Based on Claude Sonnet pricing. A $5 credit covers roughly 7 complete books.</p>
            </div>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #E4E0F0;">
                            <th style="text-align:left; padding:0.5rem 0; font-size:0.75rem; font-weight:600; color:#9B93B0; text-transform:uppercase; letter-spacing:0.05em;">Operation</th>
                            <th style="text-align:right; padding:0.5rem 0; font-size:0.75rem; font-weight:600; color:#9B93B0; text-transform:uppercase; letter-spacing:0.05em;">Est. cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['Research — 10 title angles', '~$0.01'],
                            ['Full chapter outline', '~$0.02'],
                            ['One book chapter', '~$0.03'],
                            ['Full 20-chapter book', '~$0.60'],
                            ['KDP listing pack', '~$0.02'],
                            ['Launch content pack', '~$0.03'],
                            ['Full book end-to-end', '~$0.70'],
                            ['Digital product end-to-end', '~$0.30'],
                        ] as [$op, $cost])
                        <tr style="border-bottom:1px solid #F5F4FF;">
                            <td style="padding:0.65rem 0; font-size:0.85rem; color:#0F0A1E;">{{ $op }}</td>
                            <td style="padding:0.65rem 0; font-size:0.85rem; font-weight:700; color:#6C3CE1; text-align:right; font-family:monospace;">{{ $cost }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

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
        .then(data => {
            window.showToast({ message: data.message, type: data.success ? 'success' : 'error' });
        })
        .catch(() => {
            window.showToast({ message: 'Connection test failed. Try again.', type: 'error' });
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Test connection';
        });
    }
    </script>

</x-app-layout>
