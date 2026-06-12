@php
    $styles = [
        'pending'   => ['border'=>'#E4E0F0','bg'=>'#F5F4FF','badge'=>'#9B93B0','badgeBg'=>'#F0EBFF','label'=>'Pending'],
        'running'   => ['border'=>'#93C5FD','bg'=>'#EFF6FF','badge'=>'#1D4ED8','badgeBg'=>'#DBEAFE','label'=>'Running...'],
        'completed' => ['border'=>'#6EE7B7','bg'=>'#ECFDF5','badge'=>'#065F46','badgeBg'=>'#D1FAE5','label'=>'Done'],
        'failed'    => ['border'=>'#FCA5A5','bg'=>'#FFF1F2','badge'=>'#BE123C','badgeBg'=>'#FFE4E6','label'=>'Failed'],
    ];
    $c = $styles[$stage->status] ?? $styles['pending'];
    $cardId = 'stage-'.$stage->stage_number;
@endphp

<div id="{{ $cardId }}"
     style="border:1.5px solid {{ $c['border'] }};background:{{ $c['bg'] }};border-radius:12px;padding:1rem 1.125rem;transition:border-color 0.3s,background 0.3s;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;">
        <div style="display:flex;align-items:center;gap:0.625rem;">
            {{-- Spinner (running only) --}}
            <span class="stage-spinner" style="display:{{ $stage->status === 'running' ? 'inline-block' : 'none' }};width:14px;height:14px;border:2px solid {{ $c['badge'] }};border-right-color:transparent;border-radius:50%;animation:spin 0.7s linear infinite;flex-shrink:0;"></span>
            {{-- Done check --}}
            @if($stage->status === 'completed')
            <svg width="14" height="14" fill="none" stroke="#065F46" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            @elseif($stage->status === 'failed')
            <svg width="14" height="14" fill="none" stroke="#BE123C" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            @else
            <span style="width:14px;height:14px;border-radius:50%;background:{{ $c['badge'] }};opacity:0.4;flex-shrink:0;"></span>
            @endif
            <div>
                <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0;">Stage {{ $stage->stage_number }} — {{ $stage->stage_name }}</p>
                <span class="stage-progress" style="display:{{ $stage->progress_note ? 'block' : 'none' }};font-size:0.75rem;color:#5C5470;margin-top:2px;">{{ $stage->progress_note }}</span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <span class="stage-badge" style="background:{{ $c['badgeBg'] }};color:{{ $c['badge'] }};border-radius:999px;padding:2px 10px;font-size:0.72rem;font-weight:700;">{{ $c['label'] }}</span>
            {{-- File links --}}
            <span class="stage-file-link" style="display:{{ $stage->status === 'completed' ? 'inline-flex' : 'none' }};align-items:center;gap:4px;">
                <a href="{{ route('runs.file', [$run, $stage->output_filename]) }}"
                   style="font-size:0.75rem;font-weight:600;color:#1D4ED8;text-decoration:none;background:#DBEAFE;border-radius:999px;padding:2px 10px;">
                    View
                </a>
                <a href="{{ route('runs.file', [$run, $stage->output_filename]) }}?download=1"
                   style="font-size:0.75rem;font-weight:600;color:#065F46;text-decoration:none;background:#D1FAE5;border-radius:999px;padding:2px 10px;">
                    ↓
                </a>
            </span>
            {{-- Retry button --}}
            <button class="stage-retry"
                onclick="retryStage('{{ $stage->stage_number }}')"
                style="display:{{ $stage->status === 'failed' ? 'inline-flex' : 'none' }};align-items:center;gap:4px;font-size:0.75rem;font-weight:600;color:#BE123C;background:#FFE4E6;border:none;border-radius:999px;padding:2px 10px;cursor:pointer;">
                ↺ Retry
            </button>
        </div>
    </div>
    {{-- Error message --}}
    <p class="stage-error" style="display:{{ $stage->error_message ? 'block' : 'none' }};font-size:0.78rem;color:#BE123C;margin:0.5rem 0 0;background:#FFF1F2;padding:0.5rem 0.75rem;border-radius:8px;">{{ $stage->error_message }}</p>
</div>
