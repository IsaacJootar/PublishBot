<x-app-layout>
    <x-slot name="title">Train {{ $profile->name }}</x-slot>

    <div style="max-width:680px;">

        <div style="margin-bottom:1.75rem;">
            <a href="{{ route('voice.index') }}" style="font-size:0.8rem;color:#9B93B0;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:0.75rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                My Voice
            </a>
            <div style="display:flex;align-items:center;gap:0.625rem;margin-bottom:0.25rem;">
                <span style="font-size:1.5rem;">{{ $profile->emoji }}</span>
                <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0;">Train {{ $profile->name }}</h1>
            </div>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">Upload anything you've written about this topic. The more you give, the more it sounds like you.</p>
        </div>

        {{-- Upload section --}}
        @if(!$profile->extracted_style)
        <div class="pai-card" style="margin-bottom:1.25rem;">
            <div style="margin-bottom:1.25rem;">
                <p style="font-size:0.9rem;font-weight:700;color:#0F0A1E;margin:0 0 0.875rem;">Upload your writing samples</p>

                <div style="background:#F5F4FF;border-radius:10px;padding:1rem;margin-bottom:1rem;border-left:3px solid #6C3CE1;">
                    <p style="font-size:0.8rem;font-weight:600;color:#5A2EC9;margin:0 0 0.4rem;">Works best with:</p>
                    <ul style="margin:0;padding-left:1.25rem;font-size:0.8rem;color:#5C5470;line-height:1.8;">
                        <li>WhatsApp messages or voice note transcripts</li>
                        <li>Old blog posts, articles, or essays</li>
                        <li>Social media captions you've written</li>
                        <li>Email newsletters or course scripts</li>
                        <li>Any notes or long messages in this topic area</li>
                    </ul>
                </div>

                {{-- Word count indicator --}}
                @if($profile->word_count > 0)
                @php
                    $wc = $profile->word_count;
                    if ($wc >= 5000)      { $label = 'Excellent — deep domain training'; $color = '#065F46'; $bg = '#ECFDF5'; }
                    elseif ($wc >= 2000)  { $label = 'Strong — your voice will come through clearly'; $color = '#1D4ED8'; $bg = '#EFF6FF'; }
                    elseif ($wc >= 1000)  { $label = 'Good — solid foundation'; $color = '#5A2EC9'; $bg = '#F0EBFF'; }
                    elseif ($wc >= 500)   { $label = 'Getting there — more is better'; $color = '#92400E'; $bg = '#FFFBEB'; }
                    else                  { $label = 'Add more — minimum for basic results'; $color = '#BE123C'; $bg = '#FFF1F2'; }
                @endphp
                <div style="background:{{ $bg }};border-radius:8px;padding:0.625rem 0.875rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                    <span style="font-size:0.82rem;font-weight:700;color:{{ $color }};">{{ number_format($wc) }} words — {{ $label }}</span>
                </div>
                @endif
            </div>

            <form method="POST" action="{{ route('voice.upload', $profile) }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.4rem;">
                        Choose files <span style="color:#9B93B0;font-weight:400;">(.txt, .md, .docx — multiple allowed)</span>
                    </label>
                    <input type="file" name="samples[]" multiple accept=".txt,.md,.docx"
                        style="width:100%;padding:0.625rem;border:1.5px dashed #C4B8E0;border-radius:10px;background:#FAFAFF;font-size:0.85rem;color:#5C5470;cursor:pointer;" />
                    @error('samples.*')<p style="color:#EF4444;font-size:0.75rem;margin:0.25rem 0 0;">{{ $message }}</p>@enderror
                    @error('samples')<p style="color:#EF4444;font-size:0.75rem;margin:0.25rem 0 0;">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Extract my writing style →
                </button>
            </form>
        </div>
        @endif

        {{-- Extracted style result --}}
        @if($profile->extracted_style)
        <div class="pai-card" style="margin-bottom:1.25rem;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
                <div>
                    <div class="badge-auto" style="margin-bottom:0.5rem;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Claude handled this
                    </div>
                    <p style="font-size:0.95rem;font-weight:700;color:#0F0A1E;margin:0;">Your {{ $profile->name }} writing voice</p>
                </div>
                <div style="display:flex;gap:0.5rem;">
                    {{-- Add more content --}}
                    <a href="#upload-more" onclick="document.getElementById('upload-more').style.display='block';this.style.display='none';" class="btn-ghost" style="font-size:0.78rem;padding:0.45rem 0.875rem;text-decoration:none;">Add more content</a>
                </div>
            </div>

            {{-- Style preview --}}
            <div style="background:#F5F4FF;border-radius:10px;padding:1rem;margin-bottom:1.25rem;max-height:320px;overflow-y:auto;">
                <pre style="white-space:pre-wrap;font-family:inherit;font-size:0.82rem;color:#0F0A1E;margin:0;line-height:1.7;">{{ $profile->extracted_style }}</pre>
            </div>

            <form method="POST" action="{{ route('voice.approve', $profile) }}">
                @csrf
                <input type="hidden" name="extracted_style" value="{{ $profile->extracted_style }}">
                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                    <button type="submit" class="btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Save this profile
                    </button>
                </div>
            </form>
        </div>

        {{-- Upload more (hidden) --}}
        <div id="upload-more" style="display:none;" class="pai-card" style="margin-bottom:1.25rem;">
            <p style="font-size:0.9rem;font-weight:700;color:#0F0A1E;margin:0 0 1rem;">Add more writing samples</p>
            <form method="POST" action="{{ route('voice.upload', $profile) }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:1rem;">
                    <input type="file" name="samples[]" multiple accept=".txt,.md,.docx"
                        style="width:100%;padding:0.625rem;border:1.5px dashed #C4B8E0;border-radius:10px;background:#FAFAFF;font-size:0.85rem;color:#5C5470;cursor:pointer;" />
                </div>
                <button type="submit" class="btn-primary" style="font-size:0.85rem;">Re-extract with more content →</button>
            </form>
        </div>

        @else
        {{-- Waiting for job --}}
        @if($profile->word_count > 0 && !$profile->extracted_style)
        <div class="pai-card" style="text-align:center;padding:2rem;">
            <div style="width:40px;height:40px;border:3px solid #6C3CE1;border-right-color:transparent;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 1rem;"></div>
            <p style="font-size:0.95rem;font-weight:700;color:#0F0A1E;margin:0 0 0.25rem;">Extracting your writing style...</p>
            <p style="font-size:0.82rem;color:#9B93B0;margin:0;">This takes about 30 seconds. Refresh this page to see the result.</p>
        </div>
        <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        @endif
        @endif

    </div>

</x-app-layout>
