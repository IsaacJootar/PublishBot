<x-app-layout>
    <x-slot name="title">Review cleaned draft</x-slot>

    @php
        $busy = $run->status === 'running';
        // If cleaned_content is a JSON map (partial), try to assemble or use raw
        $cleaned = $run->cleaned_content ?? '';
        $decoded = json_decode($cleaned, true);
        if (is_array($decoded) && !empty($decoded)) {
            ksort($decoded);
            $cleaned = implode("\n\n", $decoded);
        }
        // If still empty, fall back to raw uploaded content so user is never stuck
        $fallbackUsed = (trim($cleaned) === '' && trim($run->raw_uploaded_content ?? '') !== '');
        if ($fallbackUsed) {
            $cleaned = $run->raw_uploaded_content;
        }
        $queueWarning = (trim($cleaned) === '' && $run->status === 'paused');
    @endphp

    <div style="max-width:800px;">
        <div style="margin-bottom:1rem;">
            <a href="{{ route('convert.index') }}" style="font-size:0.78rem;color:#6C3CE1;text-decoration:none;font-weight:600;">← Convert Draft</a>
        </div>

        {{-- Header --}}
        <div class="pai-card" style="margin-bottom:1.25rem;border-top:3px solid #F59E0B;">
            <div style="display:flex;align-items:flex-start;gap:0.75rem;flex-wrap:wrap;">
                <span style="font-size:1.5rem;">📄</span>
                <div style="flex:1;">
                    <h1 style="margin:0;font-size:1.1rem;font-weight:800;color:#0F0A1E;">{{ $run->topic }}</h1>
                    <p style="margin:0.2rem 0 0;font-size:0.78rem;color:#9B93B0;">{{ $run->original_filename }}</p>
                </div>
            </div>

            @if($run->progress_note && !$fallbackUsed)
            <div style="margin-top:0.85rem;background:#EFF6FF;color:#1D4ED8;border-radius:8px;padding:0.55rem 0.8rem;font-size:0.78rem;font-weight:600;">
                ⏳ <span id="progress-text">{{ $run->progress_note }}</span>
            </div>
            @endif
        </div>

        @if($busy)
        {{-- Cleaning in progress --}}
        <div class="pai-card" style="text-align:center;padding:2.5rem 1.5rem;margin-bottom:1.25rem;">
            <div style="width:40px;height:40px;border:3px solid #6C3CE1;border-right-color:transparent;border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 1rem;"></div>
            <p style="font-size:0.95rem;font-weight:700;color:#0F0A1E;margin:0 0 0.3rem;">Cleaning your draft...</p>
            <p id="status-text" style="font-size:0.82rem;color:#9B93B0;margin:0 0 0.85rem;">{{ $run->progress_note ?? 'Processing sections...' }}</p>
            <p style="font-size:0.75rem;color:#9B93B0;margin:0;">
                Make sure <strong>php artisan queue:work</strong> is running in your terminal. The page will refresh automatically when cleaning is done.
            </p>
        </div>
        <script>
        (function() {
            const tick = setInterval(() => {
                fetch('{{ route('convert.status', $run) }}')
                    .then(r => r.json())
                    .then(d => {
                        if (d.progress_note) {
                            const el = document.getElementById('status-text');
                            if (el) el.textContent = d.progress_note;
                        }
                        if (d.status !== 'running') { clearInterval(tick); location.reload(); }
                    }).catch(() => {});
            }, 3000);
        })();
        </script>

        @elseif($queueWarning)
        {{-- Jobs completed but content empty — queue likely wasn't running --}}
        <div style="background:#FEF2F2;border:2px solid #EF4444;border-radius:10px;padding:1rem 1.1rem;margin-bottom:1.25rem;">
            <p style="font-size:0.88rem;font-weight:800;color:#991B1B;margin:0 0 0.4rem;">⚠️ Cleaning did not complete</p>
            <p style="font-size:0.8rem;color:#7F1D1D;margin:0 0 0.85rem;line-height:1.6;">
                The background queue was not running when this file was uploaded, so the cleaning jobs could not process.<br>
                <strong>To fix:</strong> open a terminal in your project folder and run:
            </p>
            <code style="display:block;background:#1A0D33;color:#A5F3FC;border-radius:6px;padding:0.6rem 0.85rem;font-size:0.82rem;margin-bottom:0.85rem;">php artisan queue:work</code>
            <p style="font-size:0.78rem;color:#7F1D1D;margin:0 0 0.75rem;">Then click the button below to restart the cleaning:</p>
            <form method="POST" action="{{ route('convert.reclean', $run) }}">
                @csrf
                <button type="submit" class="btn-primary" style="background:#DC2626;">🔄 Restart cleaning</button>
            </form>
        </div>

        @else
        {{-- Review + edit --}}
        <div class="pai-card" style="margin-bottom:1.25rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:0.85rem;flex-wrap:wrap;">
                <div>
                    @if($fallbackUsed)
                    <span style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">⚠️ Showing original — cleaning incomplete</span>
                    @else
                    <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">✓ Claude cleaned this</span>
                    @endif
                    <h2 style="margin:0.35rem 0 0;font-size:1rem;font-weight:700;color:#0F0A1E;">Review your cleaned manuscript</h2>
                    <p style="margin:0.15rem 0 0;font-size:0.78rem;color:#9B93B0;">
                        @if($fallbackUsed)
                            Cleaning was incomplete — showing your original text. Edit before approving.
                        @else
                            Edit anything here before approving. Your original is preserved.
                        @endif
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('convert.approve', $run) }}">
                @csrf
                <textarea name="cleaned_content" rows="30" class="pai-input"
                    style="font-family:'Courier New',monospace;font-size:0.82rem;line-height:1.7;resize:vertical;margin-bottom:1rem;">{{ $cleaned }}</textarea>

                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
                    <button type="submit" class="btn-primary" style="padding:0.7rem 1.5rem;"
                        onclick="this.textContent='Approving...';this.style.opacity='0.7';">
                        ✓ Approve and continue →
                    </button>
                    <p style="font-size:0.75rem;color:#9B93B0;margin:0;">Next: KDP listing + export files</p>
                </div>
            </form>
        </div>

        {{-- Original --}}
        <details class="pai-card">
            <summary style="cursor:pointer;font-size:0.88rem;font-weight:700;color:#0F0A1E;">View original uploaded content</summary>
            <pre style="white-space:pre-wrap;font-family:'Courier New',monospace;font-size:0.78rem;color:#5C5470;margin:0.75rem 0 0;max-height:300px;overflow-y:auto;background:#F8F7FF;padding:0.75rem;border-radius:8px;">{{ $run->raw_uploaded_content }}</pre>
        </details>
        @endif
    </div>

    <style>
    @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</x-app-layout>
