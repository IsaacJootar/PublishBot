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
            @foreach($run->stages as $stage)
            @include('runs._stage-card', ['stage' => $stage, 'run' => $run])
            @endforeach
        </div>

        {{-- Validation report panel (shown after Stage 1 completes) --}}
        <div id="validation-panel" style="display:{{ $run->validation_report ? 'block' : 'none' }};margin-bottom:1.5rem;">
            @if($run->validation_report)
            @include('runs._validation-panel', ['run' => $run])
            @endif
        </div>

        {{-- Download All (shown when completed) --}}
        <div id="download-all-panel" style="display:{{ $run->status === 'completed' ? 'block' : 'none' }};">
            <div class="pai-card" style="border:2px solid #10B981;text-align:center;padding:1.5rem;">
                <p style="font-size:1rem;font-weight:800;color:#0F0A1E;margin:0 0 0.25rem;">Pipeline complete 🎉</p>
                <p style="font-size:0.82rem;color:#5C5470;margin:0 0 1.25rem;">All 5 output files are ready.</p>
                <a href="{{ route('runs.download-all', $run) }}" class="btn-primary" style="text-decoration:none;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download all files (.zip)
                </a>
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
