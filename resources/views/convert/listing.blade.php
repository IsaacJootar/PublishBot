<x-app-layout>
    <x-slot name="title">KDP Listing</x-slot>

    <div style="max-width:760px;">
        <div style="margin-bottom:1rem;display:flex;gap:0.5rem;font-size:0.78rem;color:#9B93B0;">
            <a href="{{ route("convert.review",$run) }}" style="color:#6C3CE1;text-decoration:none;font-weight:600;">← Review</a>
            <span>·</span>
            <span style="font-weight:700;color:#0F0A1E;">KDP Listing</span>
            <span>·</span>
            <a href="{{ route("convert.launch",$run) }}" style="color:#6C3CE1;text-decoration:none;font-weight:600;">Launch content →</a>
        </div>

        @if($run->convert_listing)
        @php $l = $run->convert_listing; @endphp
        <div class="pai-card" style="margin-bottom:1.25rem;">
            <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">✓ Claude handled this</span>
            <h2 style="margin:0.5rem 0 1rem;font-size:1rem;font-weight:700;color:#0F0A1E;">KDP Listing</h2>

            @foreach(["title"=>"Title","subtitle"=>"Subtitle","description"=>"Description"] as $field => $label)
            @if(!empty($l[$field]))
            <div style="margin-bottom:1rem;">
                <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#5A2EC9;margin:0 0 0.3rem;">{{ $label }}</p>
                <p style="font-size:0.85rem;color:#0F0A1E;margin:0;background:#F5F4FF;padding:0.65rem 0.85rem;border-radius:8px;line-height:1.6;">{{ $l[$field] }}</p>
            </div>
            @endif
            @endforeach

            @if(!empty($l["keywords"]))
            <div style="margin-bottom:1rem;">
                <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#5A2EC9;margin:0 0 0.3rem;">7 Keywords</p>
                <div style="display:flex;flex-wrap:wrap;gap:0.35rem;">
                    @foreach($l["keywords"] as $kw)
                    <span style="background:#F0EBFF;color:#5A2EC9;border-radius:999px;padding:3px 10px;font-size:0.75rem;">{{ $kw }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($l["categories"]))
            <div style="margin-bottom:1rem;">
                <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#5A2EC9;margin:0 0 0.3rem;">Categories</p>
                @foreach($l["categories"] as $cat)
                <p style="font-size:0.82rem;color:#0F0A1E;margin:0.15rem 0;">{{ $cat }}</p>
                @endforeach
            </div>
            @endif

            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem;">
                @if(!empty($l["kindle_price"]))
                <span style="background:#ECFDF5;color:#065F46;border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;">Kindle: ${{ number_format($l["kindle_price"],2) }}</span>
                @endif
                @if(!empty($l["paperback_price"]))
                <span style="background:#FFFBEB;color:#92400E;border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;">Paperback: ${{ number_format($l["paperback_price"],2) }}</span>
                @endif
            </div>
        </div>
        @else
        <div class="pai-card" style="text-align:center;padding:2rem;margin-bottom:1.25rem;">
            <p style="font-size:0.85rem;color:#9B93B0;margin:0;">Generating listing...</p>
        </div>
        @endif

        {{-- Export buttons --}}
        <div class="pai-card" style="margin-bottom:1.25rem;">
            <h2 style="margin:0 0 0.75rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Download your manuscript</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:0.5rem;">
                <a href="{{ route("convert.export",[$run,"premium"]) }}" class="btn-primary" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">
                    📄 Premium PDF
                    <div style="font-size:0.65rem;font-weight:500;opacity:0.85;margin-top:2px;">Gumroad · Selar · Payhip</div>
                </a>
                <a href="{{ route("convert.export",[$run,"kdp"]) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">
                    📦 KDP Word File
                    <div style="font-size:0.65rem;opacity:0.7;margin-top:2px;">Amazon KDP upload</div>
                </a>
                <a href="{{ route("convert.export",[$run,"master"]) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">
                    ✏️ Master DOCX
                    <div style="font-size:0.65rem;opacity:0.7;margin-top:2px;">For your edits</div>
                </a>
            </div>
        </div>

        <div style="text-align:center;">
            <a href="{{ route("convert.launch",$run) }}" class="btn-outline" style="text-decoration:none;">Launch content →</a>
        </div>
    </div>
</x-app-layout>
