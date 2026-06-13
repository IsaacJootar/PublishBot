<x-app-layout>
    <x-slot name="title">{{ $filename }}</x-slot>

    <div style="max-width:800px;">

        {{-- Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
            <div>
                <a href="{{ route('runs.show', $run) }}" style="font-size:0.78rem;color:#9B93B0;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:0.5rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    {{ $run->topic }}
                </a>
                <h1 style="font-size:1.1rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">{{ $filename }}</h1>
                <p style="font-size:0.78rem;color:#9B93B0;margin:0;">
                    {{ number_format($wordCount) }} words &nbsp;·&nbsp; {{ number_format($charCount) }} characters
                </p>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button onclick="copyContent()" class="btn-outline" style="font-size:0.82rem;padding:0.5rem 0.875rem;" id="copy-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                    Copy all
                </button>
                <a href="{{ route('runs.file', [$run, $filename]) }}?download=1" class="btn-primary" style="text-decoration:none;font-size:0.82rem;padding:0.5rem 0.875rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                </a>
            </div>
        </div>

        {{-- Content --}}
        <div class="pai-card" style="padding:0;overflow:hidden;">
            <pre id="file-content" style="white-space:pre-wrap;font-family:'Courier New',monospace;font-size:0.82rem;color:#0F0A1E;line-height:1.75;margin:0;padding:1.5rem;overflow-x:auto;">{{ $content }}</pre>
        </div>
    </div>

    <script>
    function copyContent() {
        const text = document.getElementById('file-content').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('copy-btn');
            btn.textContent = '✓ Copied!';
            btn.style.color = '#065F46';
            btn.style.borderColor = '#6EE7B7';
            setTimeout(() => {
                btn.innerHTML = '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg> Copy all';
                btn.style.color = '#6C3CE1';
                btn.style.borderColor = '#6C3CE1';
            }, 2000);
        });
    }
    </script>

</x-app-layout>
