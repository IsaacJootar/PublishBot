<x-app-layout>
    <x-slot name="title">Launch Content</x-slot>

    <div style="max-width:760px;">
        <div style="margin-bottom:1rem;display:flex;gap:0.5rem;font-size:0.78rem;color:#9B93B0;">
            <a href="{{ route("convert.listing",$run) }}" style="color:#6C3CE1;text-decoration:none;font-weight:600;">← KDP Listing</a>
            <span>·</span>
            <span style="font-weight:700;color:#0F0A1E;">Launch Content</span>
        </div>

        @if($run->convert_launch)
        @php $lc = $run->convert_launch; @endphp
        <div class="pai-card" style="margin-bottom:1.25rem;">
            <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">✓ Claude handled this</span>
            <h2 style="margin:0.5rem 0 1rem;font-size:1rem;font-weight:700;color:#0F0A1E;">Launch content</h2>

            @foreach(["linkedin"=>"LinkedIn","twitter"=>"X / Twitter","instagram"=>"Instagram","pinterest"=>"Pinterest","whatsapp"=>"WhatsApp"] as $key => $label)
            @if(!empty($lc[$key]))
            <div style="margin-bottom:0.85rem;">
                <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#5A2EC9;margin:0 0 0.25rem;">{{ $label }}</p>
                <p style="font-size:0.82rem;color:#0F0A1E;margin:0;background:#F5F4FF;padding:0.65rem 0.85rem;border-radius:8px;line-height:1.6;white-space:pre-wrap;">{{ $lc[$key] }}</p>
            </div>
            @endif
            @endforeach

            @if(!empty($lc["email_subject"])&&!empty($lc["email_body"]))
            <div style="margin-bottom:0.85rem;">
                <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#5A2EC9;margin:0 0 0.25rem;">Launch email</p>
                <div style="background:#F5F4FF;padding:0.65rem 0.85rem;border-radius:8px;">
                    <p style="font-size:0.8rem;font-weight:700;color:#0F0A1E;margin:0 0 0.4rem;">Subject: {{ $lc["email_subject"] }}</p>
                    <p style="font-size:0.82rem;color:#5C5470;margin:0;line-height:1.6;white-space:pre-wrap;">{{ $lc["email_body"] }}</p>
                </div>
            </div>
            @endif
        </div>
        @else
        <div class="pai-card" style="text-align:center;padding:2rem;">
            <p style="font-size:0.85rem;color:#9B93B0;margin:0;">Generating launch content...</p>
        </div>
        @endif

        {{-- Export links --}}
        <div class="pai-card">
            <h2 style="margin:0 0 0.75rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Download your manuscript</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:0.5rem;">
                <a href="{{ route("convert.export",[$run,"premium"]) }}" class="btn-primary" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">📄 Premium PDF</a>
                <a href="{{ route("convert.export",[$run,"kdp"]) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">📦 KDP Word File</a>
                <a href="{{ route("convert.export",[$run,"master"]) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">✏️ Master DOCX</a>
            </div>
        </div>
    </div>
</x-app-layout>
