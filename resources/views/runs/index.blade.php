<x-app-layout>
    <x-slot name="title">History</x-slot>

    <div style="max-width:760px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;">
            <div>
                <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">History</h1>
                <p style="font-size:0.875rem;color:#5C5470;margin:0;">Every pipeline run you've ever performed.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn-primary" style="text-decoration:none;font-size:0.85rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New run
            </a>
        </div>

        @if($runs->isEmpty())
        <div class="pai-card" style="text-align:center;padding:2.5rem;">
            <p style="font-size:0.95rem;font-weight:700;color:#0F0A1E;margin:0 0 0.4rem;">No runs yet</p>
            <p style="font-size:0.82rem;color:#9B93B0;margin:0;">Go to the dashboard to run your first pipeline.</p>
        </div>
        @else
        <div class="pai-card" style="padding:0;overflow:hidden;">
            @foreach($runs as $run)
            @php
                $statusStyles = [
                    'pending'   => ['color'=>'#5A2EC9','bg'=>'#F0EBFF','label'=>'Pending'],
                    'running'   => ['color'=>'#1D4ED8','bg'=>'#DBEAFE','label'=>'Running'],
                    'paused'    => ['color'=>'#92400E','bg'=>'#FEF3C7','label'=>'Review needed'],
                    'completed' => ['color'=>'#065F46','bg'=>'#D1FAE5','label'=>'Completed'],
                    'failed'    => ['color'=>'#BE123C','bg'=>'#FFE4E6','label'=>'Failed'],
                ];
                $st = $statusStyles[$run->status] ?? $statusStyles['pending'];
            @endphp
            <div style="display:flex;align-items:center;gap:0.875rem;padding:0.875rem 1.25rem;border-bottom:1px solid #F5F4FF;transition:background 0.15s;"
                 onmouseover="this.style.background='#F5F4FF'" onmouseout="this.style.background='transparent'">
                <div style="flex:1;min-width:0;">
                    <a href="{{ route('runs.show', $run) }}" style="font-size:0.875rem;font-weight:700;color:#0F0A1E;text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $run->topic }}</a>
                    <p style="font-size:0.73rem;color:#9B93B0;margin:0.15rem 0 0;">{{ $run->created_at->format('M j, Y · g:ia') }}</p>
                </div>
                <span style="background:{{ $st['bg'] }};color:{{ $st['color'] }};border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:700;white-space:nowrap;flex-shrink:0;">{{ $st['label'] }}</span>
                <a href="{{ route('runs.show', $run) }}" style="font-size:0.78rem;font-weight:600;color:#6C3CE1;text-decoration:none;flex-shrink:0;">View →</a>
            </div>
            @endforeach
        </div>

        <div style="margin-top:1rem;">
            {{ $runs->links() }}
        </div>
        @endif
    </div>

</x-app-layout>
