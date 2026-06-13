<x-app-layout>
    <x-slot name="title">{{ $run->topic }}</x-slot>

    <div style="max-width:760px;">

        {{-- Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.75rem;">
            <div>
                <a href="{{ route('runs.index') }}" style="font-size:0.78rem;color:#9B93B0;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:0.5rem;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    History
                </a>
                <h1 style="font-size:1.2rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">{{ $run->topic }}</h1>
                <p style="font-size:0.78rem;color:#9B93B0;margin:0;">Started {{ $run->created_at->diffForHumans() }}</p>
            </div>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <form method="POST" action="{{ route('runs.rerun', $run) }}">
                    @csrf
                    <button type="submit" class="btn-ghost" style="font-size:0.8rem;padding:0.5rem 0.875rem;">↺ Re-run</button>
                </form>
                <form method="POST" action="{{ route('runs.destroy', $run) }}" onsubmit="return confirm('Delete this run and all its files?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none;border:none;font-size:0.8rem;color:#9B93B0;cursor:pointer;padding:0.5rem 0.5rem;" onmouseover="this.style.color='#EF4444'" onmouseout="this.style.color='#9B93B0'">Delete</button>
                </form>
            </div>
        </div>

        {{-- Stage cards --}}
        <div id="stages-container" style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1.5rem;">
            @forelse($run->stages as $stage)
            @include('runs._stage-card', ['stage' => $stage, 'run' => $run])
            @empty
            <div style="padding:1.25rem;background:#F5F4FF;border-radius:12px;text-align:center;">
                <p style="font-size:0.85rem;color:#9B93B0;margin:0;">Starting pipeline... refresh in a moment.</p>
            </div>
            @endforelse
        </div>

        {{-- Validation report panel — populated by JS --}}
        <div id="validation-panel" style="display:{{ $run->validation_report ? 'block' : 'none' }};margin-bottom:1.5rem;"></div>

        {{-- Export buttons (shown when completed) --}}
        <div id="download-all-panel" style="display:{{ $run->status === 'completed' ? 'block' : 'none' }};">
            <div class="pai-card" style="border:2px solid #10B981;padding:1.5rem;">
                <p style="font-size:1rem;font-weight:800;color:#0F0A1E;margin:0 0 0.25rem;text-align:center;">Pipeline complete 🎉</p>
                <p style="font-size:0.82rem;color:#5C5470;margin:0 0 1.25rem;text-align:center;">Your ebook is ready in three formats.</p>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:0.6rem;margin-bottom:1.25rem;">
                    <a href="{{ route('runs.export', [$run, 'premium']) }}" class="btn-primary" style="text-decoration:none;text-align:center;padding:0.7rem 0.85rem;font-size:0.8rem;">
                        📄 Premium PDF
                        <div style="font-size:0.65rem;font-weight:500;opacity:0.85;margin-top:2px;">Gumroad · Selar · Payhip</div>
                    </a>
                    <a href="{{ route('runs.export', [$run, 'kdp']) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.7rem 0.85rem;font-size:0.8rem;">
                        📦 KDP Word File
                        <div style="font-size:0.65rem;font-weight:500;opacity:0.7;margin-top:2px;">Amazon KDP upload</div>
                    </a>
                    <a href="{{ route('runs.export', [$run, 'master']) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.7rem 0.85rem;font-size:0.8rem;">
                        ✏️ Master DOCX
                        <div style="font-size:0.65rem;font-weight:500;opacity:0.7;margin-top:2px;">For your own edits</div>
                    </a>
                </div>

                {{-- Pricing strip --}}
                <div style="display:flex;gap:0.4rem;flex-wrap:wrap;justify-content:center;margin-bottom:1.25rem;">
                    <span style="background:#ECFDF5;color:#065F46;border-radius:999px;padding:3px 10px;font-size:0.7rem;font-weight:600;">Gumroad / Selar / Payhip: $7–$27</span>
                    <span style="background:#FFFBEB;color:#92400E;border-radius:999px;padding:3px 10px;font-size:0.7rem;font-weight:600;">Amazon Kindle: $2.99–$9.99</span>
                    <span style="background:#FFFBEB;color:#92400E;border-radius:999px;padding:3px 10px;font-size:0.7rem;font-weight:600;">Paperback: $9.99–$19.99</span>
                </div>

                {{-- Cover Brief for Canva --}}
                @php
                    $coverTitle = \Illuminate\Support\Str::title($run->topic);
                    $coverBrief = "BOOK COVER BRIEF — {$coverTitle}\n\n"
                        ."Style: Clean, modern, type-driven non-fiction cover with bold title hierarchy. Strong typographic contrast.\n"
                        ."Colours: Deep brand purple #1A0D33 background with violet accent #6C3CE1 for title or geometric shape.\n"
                        ."Mood: Confident, focused, premium — feels like a buy-now book on Kindle.\n\n"
                        ."Required sizes:\n"
                        ."- Amazon KDP: 2560 x 1600 px JPG\n"
                        ."- Gumroad / Selar / Payhip: 1280 x 720 px JPG or PNG\n"
                        ."- Etsy: 2000 x 2000 px JPG or PNG\n\n"
                        ."Steps: canva.com → search 'book cover' or 'ebook cover' templates → pick one → apply this brief → export JPG → upload to each platform.";
                @endphp
                <div style="background:#FFFBEB;border-left:3px solid #F59E0B;border-radius:8px;padding:0.85rem 1rem;margin-bottom:0.75rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.5rem;">
                        <p style="font-size:0.78rem;font-weight:700;color:#92400E;margin:0;">🟧 Your action needed — Cover Brief for Canva</p>
                        <button type="button" id="copy-cover-brief" onclick="copyCoverBrief()" class="btn-outline" style="padding:0.3rem 0.7rem;font-size:0.7rem;">Copy brief</button>
                    </div>
                    <div style="background:#fff;border:1px solid #FDE68A;border-radius:6px;padding:0.7rem 0.85rem;font-size:0.75rem;color:#5C5470;line-height:1.7;">
                        <p style="margin:0 0 0.3rem;"><strong>Title:</strong> {{ $coverTitle }}</p>
                        <p style="margin:0 0 0.3rem;"><strong>Style:</strong> Clean, modern, type-driven non-fiction cover with bold title hierarchy.</p>
                        <p style="margin:0 0 0.3rem;"><strong>Colours:</strong>
                            <span style="display:inline-block;width:11px;height:11px;background:#1A0D33;border-radius:2px;vertical-align:middle;border:1px solid #ddd;"></span>
                            <code style="font-size:0.7rem;">#1A0D33</code>
                            +
                            <span style="display:inline-block;width:11px;height:11px;background:#6C3CE1;border-radius:2px;vertical-align:middle;"></span>
                            <code style="font-size:0.7rem;">#6C3CE1</code>
                        </p>
                        <p style="margin:0 0 0.5rem;"><strong>Mood:</strong> Confident, focused, premium — feels like a buy-now book on Kindle.</p>
                        <p style="margin:0 0 0.2rem;font-weight:700;color:#92400E;">Required sizes:</p>
                        <ul style="margin:0 0 0.5rem;padding-left:1.2rem;">
                            <li>Amazon KDP: <strong>2560 × 1600 px</strong> JPG</li>
                            <li>Gumroad / Selar / Payhip: <strong>1280 × 720 px</strong> JPG or PNG</li>
                            <li>Etsy: <strong>2000 × 2000 px</strong> JPG or PNG</li>
                        </ul>
                        <p style="margin:0;font-style:italic;color:#92400E;">Go to canva.com → search book cover or ebook cover templates → pick one → apply this brief → export JPG → upload to each platform.</p>
                    </div>
                    <textarea id="cover-brief-text" style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">{{ $coverBrief }}</textarea>
                </div>
                <script>
                function copyCoverBrief() {
                    const ta = document.getElementById('cover-brief-text');
                    ta.style.position = 'fixed'; ta.style.left = '0'; ta.style.top = '0';
                    ta.select();
                    try { document.execCommand('copy'); window.showToast && window.showToast({message:'Cover brief copied.', type:'success'}); }
                    catch (e) { window.showToast && window.showToast({message:'Copy failed — select manually.', type:'error'}); }
                    ta.style.position = 'absolute'; ta.style.left = '-9999px';
                    const btn = document.getElementById('copy-cover-brief');
                    const original = btn.textContent;
                    btn.textContent = '✓ Copied';
                    setTimeout(() => btn.textContent = original, 1800);
                }
                </script>

                {{-- Amazon KDP upload steps --}}
                <div style="background:#FFFBEB;border-left:3px solid #F59E0B;border-radius:8px;padding:0.85rem 1rem;">
                    <p style="font-size:0.78rem;font-weight:700;color:#92400E;margin:0 0 0.4rem;">🟧 Your action needed — Upload to Amazon KDP</p>
                    <ol style="margin:0;padding-left:1.2rem;font-size:0.75rem;color:#5C5470;line-height:1.7;">
                        <li>Go to <strong>kdp.amazon.com</strong> and sign in</li>
                        <li>Click <strong>Add new title → Kindle ebook</strong> or <strong>Paperback</strong></li>
                        <li>Copy your title, subtitle, and description from the <strong>04-listings.txt</strong> file</li>
                        <li>Upload <strong>ebook-kdp.docx</strong> as your manuscript</li>
                        <li>Upload your cover image (make one in Canva or Midjourney)</li>
                        <li>Set Kindle price <strong>$2.99–$9.99</strong> · Paperback <strong>$9.99–$19.99</strong></li>
                        <li>Add the 7 backend keywords from your listing</li>
                        <li>Select 2 categories from your listing</li>
                        <li>Click <strong>Publish</strong> — live within 24–72 hours</li>
                        <li>Receive royalties via <strong>Payoneer</strong> (payoneer.com)</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Polling script --}}
    <script>
    const runId   = {{ $run->id }};
    const status  = '{{ $run->status }}';
    const csrfToken = '{{ csrf_token() }}';

    const stageColors = {
        pending:   { border:'#E4E0F0', bg:'#F5F4FF', badge:'#9B93B0', badgeBg:'#F0EBFF', label:'Pending' },
        running:   { border:'#93C5FD', bg:'#EFF6FF', badge:'#1D4ED8', badgeBg:'#DBEAFE', label:'Running...' },
        completed: { border:'#6EE7B7', bg:'#ECFDF5', badge:'#065F46', badgeBg:'#D1FAE5', label:'Done' },
        failed:    { border:'#FCA5A5', bg:'#FFF1F2', badge:'#BE123C', badgeBg:'#FFE4E6', label:'Failed' },
    };

    function renderStages(stages) {
        stages.forEach(s => {
            const card = document.getElementById('stage-' + s.stage_number.replace('a','a').replace('b','b'));
            if (!card) return;
            const c = stageColors[s.status] || stageColors.pending;

            card.style.borderColor = c.border;
            card.style.background  = c.bg;

            const badge = card.querySelector('.stage-badge');
            if (badge) {
                badge.style.color      = c.badge;
                badge.style.background = c.badgeBg;
                badge.textContent      = c.label;
            }

            const progress = card.querySelector('.stage-progress');
            if (progress) {
                progress.textContent = s.progress_note || '';
                progress.style.display = s.progress_note ? 'block' : 'none';
            }

            const fileLink = card.querySelector('.stage-file-link');
            if (fileLink) {
                fileLink.style.display = s.status === 'completed' ? 'inline-flex' : 'none';
            }

            const retryBtn = card.querySelector('.stage-retry');
            if (retryBtn) {
                retryBtn.style.display = s.status === 'failed' ? 'inline-flex' : 'none';
            }

            const errMsg = card.querySelector('.stage-error');
            if (errMsg) {
                errMsg.textContent  = s.error_message || '';
                errMsg.style.display= s.error_message ? 'block' : 'none';
            }

            const spinner = card.querySelector('.stage-spinner');
            if (spinner) spinner.style.display = s.status === 'running' ? 'inline-block' : 'none';
        });
    }

    function poll() {
        fetch('/runs/' + runId + '/status', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                renderStages(data.stages);

                // Show validation panel
                if (data.validation_report) {
                    const vp = document.getElementById('validation-panel');
                    if (vp && vp.style.display === 'none') {
                        vp.style.display = 'block';
                        vp.innerHTML = buildValidationPanel(data.validation_report, data.validation_result, data.user_confirmed_continue);
                    }
                    // Update continue button visibility
                    const continueBtn = document.getElementById('continue-btn');
                    if (continueBtn && data.user_confirmed_continue) continueBtn.style.display = 'none';
                }

                // Show download all
                if (data.status === 'completed') {
                    document.getElementById('download-all-panel').style.display = 'block';
                }

                // Keep polling if still running/paused
                if (['running', 'pending', 'paused'].includes(data.status)) {
                    setTimeout(poll, 3000);
                }
            })
            .catch(() => setTimeout(poll, 5000));
    }

    function buildValidationPanel(report, result, confirmed) {
        const isNoGo    = result === 'no_go';
        const border    = isNoGo ? '#FCA5A5' : '#6EE7B7';
        const bg        = isNoGo ? '#FFF1F2' : '#ECFDF5';
        const resultColor = isNoGo ? '#BE123C' : '#065F46';
        const resultLabel = isNoGo ? '⚠ No-Go' : '✓ Go';

        const continueHtml = confirmed ? '' : `
            <div style="margin-top:1rem;">
                <button id="continue-btn" onclick="continueRun()" class="btn-primary" style="font-size:0.85rem;">
                    ${isNoGo ? 'Continue anyway →' : 'Looks good — continue →'}
                </button>
            </div>`;

        return `
            <div class="pai-card" style="border:2px solid ${border};background:${bg};">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.875rem;flex-wrap:wrap;gap:0.5rem;">
                    <p style="font-size:0.9rem;font-weight:700;color:#0F0A1E;margin:0;">Stage 1 — Market Validation Report</p>
                    <span style="font-size:0.8rem;font-weight:700;color:${resultColor};background:white;border-radius:999px;padding:3px 12px;border:1px solid ${border};">${resultLabel}</span>
                </div>
                <pre style="white-space:pre-wrap;font-family:inherit;font-size:0.8rem;color:#0F0A1E;line-height:1.65;margin:0;max-height:400px;overflow-y:auto;">${report.replace(/</g,'&lt;')}</pre>
                ${continueHtml}
            </div>`;
    }

    function continueRun() {
        const btn = document.getElementById('continue-btn');
        if (btn) { btn.disabled = true; btn.textContent = 'Starting...'; }
        fetch('/runs/' + runId + '/continue', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                if (btn) btn.style.display = 'none';
                setTimeout(poll, 1000);
            }
        })
        .catch(() => { if (btn) { btn.disabled = false; btn.textContent = 'Try again'; } });
    }

    function retryStage(stageNumber) {
        const btn = document.querySelector(`#stage-${stageNumber} .stage-retry`);
        if (btn) { btn.disabled = true; btn.textContent = 'Retrying...'; }
        fetch(`/runs/${runId}/retry/${stageNumber}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => { if (data.ok) setTimeout(poll, 1000); });
    }

    // Start polling if active
    if (['running', 'pending', 'paused'].includes(status)) {
        setTimeout(poll, 3000);
    }

    // Render validation panel if already present on load
    @if($run->validation_report)
    document.getElementById('validation-panel').innerHTML = buildValidationPanel(
        @json($run->validation_report),
        '{{ $run->validation_result }}',
        {{ $run->user_confirmed_continue ? 'true' : 'false' }}
    );
    @endif
    </script>

</x-app-layout>
