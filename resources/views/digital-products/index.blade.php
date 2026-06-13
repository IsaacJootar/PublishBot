<x-app-layout>
    <x-slot name="title">Digital Products</x-slot>

    <div style="margin-bottom:1.5rem;">
        <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">Digital Products Factory</h1>
        <p style="font-size:0.875rem;color:#5C5470;margin:0;">Pick a product type. Fill a brief. Claude builds the rest.</p>
    </div>

    {{-- Product type cards --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1rem;margin-bottom:2rem;">
        @foreach($types as $key => $t)
        <a href="{{ route('digital-products.create', $key) }}" class="pai-card" style="text-decoration:none;color:inherit;transition:transform 0.15s,box-shadow 0.15s;"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(108,60,225,0.12)'"
           onmouseout="this.style.transform='none';this.style.boxShadow=''">
            <div style="font-size:2rem;margin-bottom:0.5rem;">{{ $t['emoji'] }}</div>
            <h3 style="margin:0 0 0.25rem;font-size:1rem;font-weight:800;color:#0F0A1E;">{{ $t['name'] }}</h3>
            <p style="margin:0 0 0.75rem;font-size:0.8rem;color:#5C5470;line-height:1.4;">{{ $t['tagline'] }}</p>
            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:0.85rem;">
                <span style="background:#ECFDF5;color:#065F46;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">{{ $t['price'] }}</span>
                <span style="background:#F5F4FF;color:#5A2EC9;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:600;">{{ $t['time'] }}</span>
            </div>
            <div style="font-size:0.78rem;font-weight:700;color:#6C3CE1;">Start building →</div>
        </a>
        @endforeach
    </div>

    {{-- Recent products --}}
    @if($products->isNotEmpty())
    <div class="pai-card">
        <h2 style="margin:0 0 0.85rem;font-size:0.95rem;font-weight:700;color:#0F0A1E;">Your products</h2>
        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            @foreach($products as $p)
            @php
                $tMeta = $p->typeMeta();
                $statusBg = match($p->status){'complete'=>'#ECFDF5','failed'=>'#FFF1F2','draft'=>'#F5F4FF',default=>'#EFF6FF'};
                $statusColor = match($p->status){'complete'=>'#065F46','failed'=>'#BE123C','draft'=>'#5A2EC9',default=>'#1D4ED8'};
            @endphp
            <a href="{{ route('digital-products.show', $p) }}"
               style="display:flex;align-items:center;gap:0.85rem;padding:0.75rem;background:#F5F4FF;border-radius:10px;text-decoration:none;color:inherit;transition:background 0.15s;"
               onmouseover="this.style.background='#EDE9FF'" onmouseout="this.style.background='#F5F4FF'">
                <div style="width:38px;height:38px;background:#fff;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;">{{ $tMeta['emoji'] ?? '📦' }}</div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->product_title ?: $p->niche }}</p>
                    <p style="font-size:0.72rem;color:#9B93B0;margin:0.15rem 0 0;">{{ $tMeta['name'] ?? $p->product_type }} · Stage {{ $p->current_stage }}/5 · {{ $p->updated_at->diffForHumans() }}</p>
                </div>
                <span style="background:{{ $statusBg }};color:{{ $statusColor }};border-radius:999px;padding:3px 10px;font-size:0.7rem;font-weight:600;flex-shrink:0;">{{ ucfirst($p->status) }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

</x-app-layout>
