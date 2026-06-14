<x-app-layout>
    <x-slot name="title">Convert Draft</x-slot>

    <div style="max-width:680px;">
        <div style="margin-bottom:1.5rem;">
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">Convert Draft</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">Upload your finished manuscript — one file or many chapters. PublishBot reads the filenames, infers the chapter order, cleans the text, and exports as Premium PDF + KDP DOCX + Master DOCX.</p>
        </div>

        {{-- Upload form --}}
        <div class="pai-card" style="margin-bottom:1.25rem;">
            <h2 style="margin:0 0 0.85rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Upload your document(s)</h2>

            <div style="background:#F5F4FF;border-left:3px solid #6C3CE1;border-radius:8px;padding:0.75rem 0.9rem;margin-bottom:1rem;font-size:0.78rem;color:#5C5470;line-height:1.6;">
                <strong>Accepted formats:</strong> .docx, .txt, .md &nbsp;·&nbsp;
                <strong>Max 20 files, 50 MB each</strong><br>
                <strong>Single file:</strong> goes straight to cleaning. &nbsp;
                <strong>Multiple files:</strong> Claude reads filenames + first lines → proposes chapter order → you confirm or drag to reorder → cleaning starts.
            </div>

            <form method="POST" action="{{ route('convert.upload') }}" enctype="multipart/form-data"
                  id="convert-form" onsubmit="handleSubmit(event)">
                @csrf

                <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Book or document title <span style="color:#EF4444;">*</span></label>
                <input type="text" name="title" required class="pai-input" style="margin-bottom:1rem;"
                       placeholder="e.g. The Freelancer Business Bible" maxlength="200" value="{{ old('title') }}"/>

                <label style="display:block;font-size:0.8rem;font-weight:600;color:#0F0A1E;margin-bottom:0.3rem;">Your file(s) <span style="color:#EF4444;">*</span></label>
                <input type="file" name="files[]" id="file-input" required multiple accept=".txt,.md,.docx"
                       class="pai-input" style="padding:0.5rem;margin-bottom:0.5rem;"
                       onchange="showFileList(this)"/>

                <div id="file-list" style="display:none;background:#F5F4FF;border-radius:8px;padding:0.6rem 0.85rem;margin-bottom:1rem;font-size:0.75rem;color:#5C5470;line-height:1.7;"></div>

                <button type="submit" id="btn-upload" class="btn-primary" style="width:100%;padding:0.8rem;">
                    Convert my draft →
                </button>
            </form>
        </div>

        {{-- Recent converts --}}
        @if($recentConverts->isNotEmpty())
        <div class="pai-card">
            <h2 style="margin:0 0 0.85rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Recent conversions</h2>
            <div style="display:flex;flex-direction:column;gap:0.4rem;">
                @foreach($recentConverts as $r)
                @php
                    $sBg = match($r->status){'completed'=>'#ECFDF5','running'=>'#EFF6FF','paused'=>'#FFFBEB','failed'=>'#FFF1F2',default=>'#F5F4FF'};
                    $sCol = match($r->status){'completed'=>'#065F46','running'=>'#1D4ED8','paused'=>'#92400E','failed'=>'#BE123C',default=>'#5A2EC9'};
                    $href = $r->cleaning_approved
                        ? route('convert.listing',$r)
                        : ($r->inferred_order && ! $r->confirmed_order ? route('convert.order',$r) : route('convert.review',$r));
                @endphp
                <a href="{{ $href }}" style="display:flex;align-items:center;gap:0.75rem;padding:0.65rem 0.85rem;background:#F5F4FF;border-radius:9px;text-decoration:none;transition:background 0.15s;"
                   onmouseover="this.style.background='#EDE9FF'" onmouseout="this.style.background='#F5F4FF'">
                    <span style="font-size:1.1rem;">📄</span>
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:0.82rem;font-weight:700;color:#0F0A1E;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $r->topic }}</p>
                        <p style="font-size:0.7rem;color:#9B93B0;margin:0.1rem 0 0;">{{ $r->original_filename }} · {{ $r->created_at->diffForHumans() }}</p>
                    </div>
                    <span style="background:{{ $sBg }};color:{{ $sCol }};border-radius:999px;padding:2px 9px;font-size:0.7rem;font-weight:600;flex-shrink:0;">{{ ucfirst($r->status) }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <script>
    function showFileList(input) {
        const list = document.getElementById('file-list');
        if (! input.files.length) { list.style.display = 'none'; return; }
        const names = Array.from(input.files).map((f, i) => `${i + 1}. ${f.name} (${Math.round(f.size / 1024)} KB)`).join('<br>');
        list.innerHTML = '<strong>' + input.files.length + ' file(s) selected:</strong><br>' + names;
        list.style.display = 'block';
    }
    function handleSubmit(e) {
        const btn = document.getElementById('btn-upload');
        const count = document.getElementById('file-input').files.length;
        btn.textContent = count > 1 ? 'Uploading ' + count + ' files...' : 'Uploading...';
        btn.style.opacity = '0.7';
    }
    </script>
</x-app-layout>
