<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    {{-- Header --}}
    <div style="margin-bottom:1.75rem;">
        <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ $settings->author_name ?: explode(' ', Auth::user()->name)[0] }} 👋
        </h1>
        <p style="font-size:0.875rem;color:#5C5470;margin:0;">Any topic. Any niche. Every Friday.</p>
    </div>

    {{-- Metric cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1.75rem;">
        <div class="metric-card metric-violet">
            <div class="metric-label">Pipeline Runs</div>
            <div class="metric-value">{{ $stats['runs'] }}</div>
            <div class="metric-sub">total runs</div>
        </div>
        <div class="metric-card metric-green">
            <div class="metric-label">Completed</div>
            <div class="metric-value">{{ $stats['completed'] }}</div>
            <div class="metric-sub">full pipelines done</div>
        </div>
        <div class="metric-card metric-amber">
            <div class="metric-label">Revenue This Month</div>
            <div class="metric-value">{{ number_format($stats['revenue'], 0) }}</div>
            <div class="metric-sub">{{ $stats['currency'] }} manually logged</div>
        </div>
    </div>

    {{-- Topic input --}}
    <div class="pai-card" style="margin-bottom:1.5rem;">
        <p style="font-size:0.9rem;font-weight:700;color:#0F0A1E;margin:0 0 0.25rem;">Run a new pipeline</p>
        <p style="font-size:0.8rem;color:#9B93B0;margin:0 0 1rem;">Type any topic. One click produces your ebook, workbook, listings, and pins.</p>

        <form method="POST" action="{{ route('runs.create') }}"
              onsubmit="document.getElementById('btn-run').textContent='Starting...';document.getElementById('btn-run').style.opacity='0.7';">
            @csrf
            <div style="display:flex;gap:0.75rem;align-items:flex-start;flex-wrap:wrap;">
                <div style="flex:1;min-width:220px;">
                    <input
                        type="text"
                        name="topic"
                        id="topic-input"
                        placeholder="e.g. Parenting toddlers without losing your mind"
                        maxlength="200"
                        class="pai-input"
                        required
                        autofocus
                        oninput="document.getElementById('char-count').textContent = this.value.length"
                    />
                    <p style="font-size:0.72rem;color:#9B93B0;margin:0.3rem 0 0;text-align:right;">
                        <span id="char-count">0</span>/200
                    </p>
                </div>
                <button type="submit" class="btn-primary" id="btn-run" style="flex-shrink:0;padding:0.72rem 1.5rem;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Run pipeline →
                </button>
            </div>

            {{-- Voice + Series pickers --}}
            <div style="margin-top:0.85rem;display:flex;gap:0.75rem;flex-wrap:wrap;">
                @if($voices->isNotEmpty())
                <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;flex:1;min-width:240px;">
                    <label for="voice-select" style="font-size:0.75rem;font-weight:600;color:#5C5470;white-space:nowrap;">🎙 Voice:</label>
                    <select name="voice_profile_id" id="voice-select" class="pai-input" style="flex:1;min-width:160px;font-size:0.8rem;padding:0.4rem 0.6rem;cursor:pointer;">
                        <option value="">— None —</option>
                        @foreach($voices as $v)
                        <option value="{{ $v->id }}" {{ $defaultVoiceId === $v->id ? 'selected' : '' }}>
                            {{ $v->emoji }} {{ $v->name }} {{ $v->is_default ? '★' : '' }}{{ $v->extracted_style ? '' : ' (untrained)' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if($seriesList->isNotEmpty())
                <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;flex:1;min-width:240px;">
                    <label for="series-select" style="font-size:0.75rem;font-weight:600;color:#5C5470;white-space:nowrap;">📚 Series:</label>
                    <select name="series_id" id="series-select" class="pai-input" style="flex:1;min-width:160px;font-size:0.8rem;padding:0.4rem 0.6rem;cursor:pointer;">
                        <option value="">— None —</option>
                        @foreach($seriesList as $s)
                        <option value="{{ $s->id }}">{{ $s->emoji }} {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            @if($voices->isEmpty())
            <div style="margin-top:0.65rem;background:#FFFBEB;color:#92400E;border-left:3px solid #F59E0B;border-radius:8px;padding:0.55rem 0.8rem;font-size:0.75rem;">
                💡 Train a voice profile to make every ebook sound like you. <a href="{{ route('voice.index') }}" style="color:#92400E;font-weight:700;text-decoration:underline;">Set up My Voice →</a>
            </div>
            @endif
            @error('topic')
            <p style="color:#EF4444;font-size:0.78rem;margin:0.5rem 0 0;">{{ $message }}</p>
            @enderror
        </form>

        {{-- Quick topic tags --}}
        @if(!empty($quickTopics))
        <div style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:0.5rem;">
            @foreach($quickTopics as $tag)
            <button type="button"
                onclick="document.getElementById('topic-input').value='{{ addslashes($tag) }}';document.getElementById('char-count').textContent='{{ strlen($tag) }}';"
                style="background:#F5F4FF;color:#5A2EC9;border:1px solid #E4E0F0;border-radius:999px;padding:4px 12px;font-size:0.78rem;font-weight:600;cursor:pointer;transition:background 0.15s,border-color 0.15s;"
                onmouseover="this.style.background='#EDE9FF';this.style.borderColor='#6C3CE1'"
                onmouseout="this.style.background='#F5F4FF';this.style.borderColor='#E4E0F0'">
                {{ $tag }}
            </button>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Recent runs --}}
    @if($recent->isNotEmpty())
    <div class="pai-card">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <p style="font-size:0.9rem;font-weight:700;color:#0F0A1E;margin:0;">Recent runs</p>
            <a href="{{ route('runs.index') }}" style="font-size:0.78rem;font-weight:600;color:#6C3CE1;text-decoration:none;">View all →</a>
        </div>
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            @foreach($recent as $run)
            @php
                $statusStyles = [
                    'pending'   => ['bg'=>'#F5F4FF','color'=>'#5A2EC9','label'=>'Pending'],
                    'running'   => ['bg'=>'#EFF6FF','color'=>'#1D4ED8','label'=>'Running'],
                    'paused'    => ['bg'=>'#FFFBEB','color'=>'#92400E','label'=>'Review needed'],
                    'completed' => ['bg'=>'#ECFDF5','color'=>'#065F46','label'=>'Completed'],
                    'failed'    => ['bg'=>'#FFF1F2','color'=>'#BE123C','label'=>'Failed'],
                ];
                $st = $statusStyles[$run->status] ?? $statusStyles['pending'];
            @endphp
            <a href="{{ route('runs.show', $run) }}"
               style="display:flex;align-items:center;gap:0.875rem;padding:0.75rem;background:#F5F4FF;border-radius:10px;text-decoration:none;transition:background 0.15s;"
               onmouseover="this.style.background='#EDE9FF'" onmouseout="this.style.background='#F5F4FF'">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,#1C1308,#0E0B04);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" fill="none" stroke="#F59E0B" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $run->topic }}</p>
                    <p style="font-size:0.73rem;color:#9B93B0;margin:0.15rem 0 0;">{{ $run->created_at->diffForHumans() }}</p>
                </div>
                <span style="background:{{ $st['bg'] }};color:{{ $st['color'] }};border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;white-space:nowrap;flex-shrink:0;">{{ $st['label'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @else
    <div class="pai-card" style="text-align:center;padding:2.5rem 1.5rem;">
        <div style="width:52px;height:52px;background:#F5F4FF;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
            <svg width="24" height="24" fill="none" stroke="#6C3CE1" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <p style="font-size:0.95rem;font-weight:700;color:#0F0A1E;margin:0 0 0.4rem;">No runs yet</p>
        <p style="font-size:0.82rem;color:#9B93B0;margin:0 0 1.25rem;">Type a topic above and click Run pipeline to get started.</p>
        @if(!$settings->api_key_encrypted)
        <a href="{{ route('settings.index') }}" style="font-size:0.82rem;font-weight:600;color:#6C3CE1;text-decoration:none;">→ Add your Claude API key in Settings first</a>
        @endif
    </div>
    @endif

</x-app-layout>
