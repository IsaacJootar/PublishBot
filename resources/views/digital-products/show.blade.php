<x-app-layout>
    <x-slot name="title">{{ $product->product_title ?: $product->niche }}</x-slot>

    @php
        $meta = $product->typeMeta();
        $labels = $product->stageLabels();
        $busy = in_array($product->status, ['researching','structuring','writing','publishing']);
    @endphp

    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('digital-products.index') }}" style="font-size:0.78rem;color:#6C3CE1;text-decoration:none;font-weight:600;">← All products</a>
    </div>

    {{-- Header --}}
    <div class="pai-card" style="margin-bottom:1.25rem;">
        <div style="display:flex;align-items:flex-start;gap:0.85rem;flex-wrap:wrap;">
            <span style="font-size:2rem;">{{ $meta['emoji'] ?? '📦' }}</span>
            <div style="flex:1;min-width:200px;">
                <h1 style="margin:0;font-size:1.15rem;font-weight:800;color:#0F0A1E;">{{ $product->product_title ?: $product->niche }}</h1>
                <p style="margin:0.2rem 0 0;font-size:0.82rem;color:#5C5470;">{{ $meta['name'] ?? $product->product_type }} · {{ $product->niche }}</p>
            </div>
            <form method="POST" action="{{ route('digital-products.destroy', $product) }}" onsubmit="return confirm('Delete this product?');">
                @csrf @method('DELETE')
                <button type="submit" style="background:none;border:none;color:#BE123C;font-size:0.78rem;font-weight:600;cursor:pointer;">Delete</button>
            </form>
        </div>

        {{-- Stage progress --}}
        <div style="margin-top:1rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
            @foreach($labels as $num => $label)
            @php
                $done = $product->current_stage > $num || ($product->current_stage == $num && $product->status === 'complete');
                $active = $product->current_stage == $num && $busy;
                $bg = $done ? '#ECFDF5' : ($active ? '#EFF6FF' : '#F5F4FF');
                $color = $done ? '#065F46' : ($active ? '#1D4ED8' : '#9B93B0');
            @endphp
            <span style="background:{{ $bg }};color:{{ $color }};border-radius:999px;padding:4px 10px;font-size:0.72rem;font-weight:600;">
                {{ $done ? '✓' : $num }} {{ $label }}{{ $active ? '...' : '' }}
            </span>
            @endforeach
        </div>

        @if($product->progress_note)
        <div style="margin-top:0.85rem;background:#EFF6FF;color:#1D4ED8;border-radius:8px;padding:0.55rem 0.8rem;font-size:0.78rem;font-weight:600;">
            ⏳ {{ $product->progress_note }}
        </div>
        @endif

        @if($product->status === 'failed' && $product->error_message)
        <div style="margin-top:0.85rem;background:#FFF1F2;color:#BE123C;border-radius:8px;padding:0.6rem 0.85rem;font-size:0.78rem;">
            Failed: {{ $product->error_message }}
            <form method="POST" action="{{ route('digital-products.regenerate', [$product, max(2, $product->current_stage)]) }}" style="margin-top:0.5rem;">
                @csrf
                <button type="submit" class="btn-outline" style="padding:0.35rem 0.75rem;font-size:0.75rem;">Retry</button>
            </form>
        </div>
        @endif
    </div>

    @if($busy)
    <script>
    (function() {
        const tick = setInterval(() => {
            fetch('{{ route('digital-products.status', $product) }}')
                .then(r => r.json())
                .then(d => {
                    if (d.status !== '{{ $product->status }}' || d.current_stage !== {{ $product->current_stage }}) {
                        clearInterval(tick);
                        location.reload();
                    }
                    if (d.progress_note) {
                        document.querySelectorAll('.dp-progress-note').forEach(el => el.textContent = d.progress_note);
                    }
                })
                .catch(() => {});
        }, 3000);
    })();
    </script>
    @endif

    {{-- Stage 2: Research --}}
    @if($product->research_output)
    <div class="pai-card" style="margin-bottom:1.25rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:0.85rem;flex-wrap:wrap;">
            <div>
                <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">✓ Claude handled this</span>
                <h2 style="margin:0.3rem 0 0;font-size:1rem;font-weight:700;color:#0F0A1E;">Stage 2 — Research</h2>
            </div>
            <div style="display:flex;gap:0.4rem;">
                <form method="POST" action="{{ route('digital-products.regenerate', [$product, 2]) }}">
                    @csrf
                    <button type="submit" class="btn-outline" style="padding:0.4rem 0.85rem;font-size:0.75rem;" {{ $busy ? 'disabled' : '' }}>Regenerate</button>
                </form>
                @if($product->current_stage == 2 && ! $busy)
                <form method="POST" action="{{ route('digital-products.advance', $product) }}">
                    @csrf
                    <button type="submit" class="btn-primary" style="padding:0.4rem 0.85rem;font-size:0.75rem;">Build structure →</button>
                </form>
                @endif
            </div>
        </div>

        @php $r = $product->research_output; @endphp
        @if(isset($r['buyer_profile']))
        <p style="font-size:0.82rem;color:#5C5470;margin:0 0 0.85rem;line-height:1.6;"><strong>Buyer:</strong> {{ $r['buyer_profile'] }}</p>
        @endif

        @if(isset($r['pain_points']))
        <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0.85rem 0 0.4rem;">Top pain points</p>
        <ul style="margin:0;padding-left:1.2rem;font-size:0.8rem;color:#5C5470;line-height:1.6;">
            @foreach($r['pain_points'] as $pp)
            <li><strong>{{ $pp['pain'] ?? '' }}</strong> — {{ $pp['why_it_hurts'] ?? '' }}</li>
            @endforeach
        </ul>
        @endif

        @if(isset($r['market_gap']))
        <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0.85rem 0 0.4rem;">Market gap</p>
        <p style="font-size:0.8rem;color:#5C5470;margin:0;line-height:1.6;">{{ $r['market_gap'] }}</p>
        @endif

        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.85rem;">
            @if(isset($r['recommended_platform']))
            <span style="background:#F5F4FF;color:#5A2EC9;border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;">Platform: {{ $r['recommended_platform'] }}</span>
            @endif
            @if(isset($r['recommended_price']))
            <span style="background:#ECFDF5;color:#065F46;border-radius:999px;padding:3px 10px;font-size:0.72rem;font-weight:600;">Price: ${{ $r['recommended_price'] }}</span>
            @endif
        </div>
    </div>
    @endif

    {{-- Stage 3: Structure --}}
    @if($product->structure_output)
    <div class="pai-card" style="margin-bottom:1.25rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:0.85rem;flex-wrap:wrap;">
            <div>
                <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">✓ Claude handled this</span>
                <h2 style="margin:0.3rem 0 0;font-size:1rem;font-weight:700;color:#0F0A1E;">Stage 3 — Structure</h2>
                @if(!empty($product->structure_output['product_title']))
                <p style="margin:0.2rem 0 0;font-size:0.85rem;color:#5C5470;"><strong>{{ $product->structure_output['product_title'] }}</strong></p>
                @endif
            </div>
            <div style="display:flex;gap:0.4rem;">
                <form method="POST" action="{{ route('digital-products.regenerate', [$product, 3]) }}">
                    @csrf
                    <button type="submit" class="btn-outline" style="padding:0.4rem 0.85rem;font-size:0.75rem;" {{ $busy ? 'disabled' : '' }}>Regenerate</button>
                </form>
                @if($product->current_stage == 3 && ! $busy)
                <form method="POST" action="{{ route('digital-products.advance', $product) }}">
                    @csrf
                    <button type="submit" class="btn-primary" style="padding:0.4rem 0.85rem;font-size:0.75rem;">Write full content →</button>
                </form>
                @endif
            </div>
        </div>

        @php $s = $product->structure_output; @endphp

        {{-- Original 3 types --}}
        @if(isset($s['categories']))
            @foreach($s['categories'] as $c)
            <div style="background:#F5F4FF;border-radius:8px;padding:0.6rem 0.85rem;margin-bottom:0.5rem;">
                <p style="margin:0;font-size:0.85rem;font-weight:700;color:#0F0A1E;">{{ $c['name'] ?? '' }} <span style="font-size:0.7rem;font-weight:600;color:#5A2EC9;">({{ $c['prompt_count'] ?? 0 }} prompts)</span></p>
                <p style="margin:0.2rem 0 0;font-size:0.75rem;color:#5C5470;">{{ $c['what_it_covers'] ?? '' }}</p>
            </div>
            @endforeach
        @elseif(isset($s['sops']))
            @foreach($s['sops'] as $sop)
            <div style="background:#F5F4FF;border-radius:8px;padding:0.6rem 0.85rem;margin-bottom:0.5rem;">
                <p style="margin:0;font-size:0.85rem;font-weight:700;color:#0F0A1E;">{{ $sop['title'] ?? '' }}</p>
                <p style="margin:0.2rem 0 0;font-size:0.75rem;color:#5C5470;">{{ $sop['covers'] ?? '' }}</p>
            </div>
            @endforeach
        @elseif(isset($s['sequences']))
            @foreach($s['sequences'] as $seq)
            <div style="background:#F5F4FF;border-radius:8px;padding:0.6rem 0.85rem;margin-bottom:0.5rem;">
                <p style="margin:0;font-size:0.85rem;font-weight:700;color:#0F0A1E;">{{ $seq['name'] ?? '' }} <span style="font-size:0.7rem;font-weight:600;color:#5A2EC9;">({{ $seq['email_count'] ?? 0 }} emails)</span></p>
                <p style="margin:0.2rem 0 0;font-size:0.75rem;color:#5C5470;"><strong>Trigger:</strong> {{ $seq['trigger'] ?? '' }} · <strong>Goal:</strong> {{ $seq['goal'] ?? '' }}</p>
            </div>
            @endforeach

        {{-- Content Calendar System --}}
        @elseif(isset($s['monthly_themes']))
            @foreach(($s['monthly_themes'] ?? []) as $month)
            <div style="background:#F5F4FF;border-radius:8px;padding:0.6rem 0.85rem;margin-bottom:0.5rem;">
                <p style="margin:0;font-size:0.85rem;font-weight:700;color:#0F0A1E;">Month {{ $month['month'] ?? '' }}: {{ $month['theme'] ?? '' }}</p>
                <p style="margin:0.2rem 0 0;font-size:0.75rem;color:#5C5470;">{{ $month['focus'] ?? '' }}</p>
            </div>
            @endforeach
            @if(!empty($s['content_pillars']))
            <p style="font-size:0.8rem;font-weight:700;color:#0F0A1E;margin:0.75rem 0 0.35rem;">Content Pillars</p>
            <div style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                @foreach($s['content_pillars'] as $pillar)
                <span style="background:#EDE9FF;color:#5A2EC9;border-radius:8px;padding:3px 10px;font-size:0.75rem;font-weight:600;">{{ $pillar['pillar'] ?? '' }} ({{ $pillar['percentage'] ?? 0 }}%)</span>
                @endforeach
            </div>
            @endif

        {{-- Excel Tracker --}}
        @elseif(isset($s['sheets']))
            @foreach($s['sheets'] as $sheet)
            <div style="background:#F0FDF4;border-radius:8px;padding:0.6rem 0.85rem;margin-bottom:0.5rem;border-left:3px solid #059669;">
                <p style="margin:0;font-size:0.85rem;font-weight:700;color:#064E3B;">{{ $sheet['sheet_name'] ?? '' }}</p>
                <p style="margin:0.2rem 0 0;font-size:0.75rem;color:#5C5470;">{{ $sheet['purpose'] ?? '' }} · {{ count($sheet['columns'] ?? []) }} columns</p>
            </div>
            @endforeach

        {{-- Notion Business OS --}}
        @elseif(isset($s['workspace_sections']))
            <div style="background:#FEF3C7;border-left:3px solid #F59E0B;border-radius:8px;padding:0.7rem 0.9rem;margin-bottom:0.85rem;">
                <p style="margin:0;font-size:0.78rem;font-weight:700;color:#92400E;">🗂️ Notion Business OS — {{ count($s['workspace_sections']) }} workspace sections designed</p>
            </div>
            @foreach($s['workspace_sections'] as $ws)
            <div style="background:#FFFBEB;border-radius:8px;padding:0.6rem 0.85rem;margin-bottom:0.5rem;border-left:3px solid #F59E0B;">
                <p style="margin:0;font-size:0.85rem;font-weight:700;color:#78350F;">{{ $ws['section_name'] ?? '' }}</p>
                <p style="margin:0.2rem 0 0;font-size:0.75rem;color:#5C5470;">{{ $ws['purpose'] ?? '' }}
                    @if(!empty($ws['databases'])) · {{ count($ws['databases']) }} database(s)@endif
                </p>
            </div>
            @endforeach

        {{-- Website Copy Pack --}}
        @elseif(isset($s['pages']) && is_array($s['pages']))
            <p style="font-size:0.8rem;color:#5C5470;margin:0 0 0.6rem;">Pages to write:</p>
            @foreach($s['pages'] as $page)
            <div style="background:#EFF6FF;border-radius:8px;padding:0.55rem 0.85rem;margin-bottom:0.4rem;border-left:3px solid #2563EB;">
                <p style="margin:0;font-size:0.85rem;font-weight:700;color:#1E3A8A;">{{ $page }}</p>
            </div>
            @endforeach
            @if(!empty($s['tone_direction']))
            <p style="font-size:0.75rem;color:#5C5470;margin:0.6rem 0 0;"><strong>Tone:</strong> {{ $s['tone_direction'] }}</p>
            @endif

        {{-- Brand Messaging System --}}
        @elseif(isset($s['deliverables']))
            @foreach($s['deliverables'] as $i => $d)
            <div style="background:#FFFBEB;border-radius:8px;padding:0.55rem 0.85rem;margin-bottom:0.4rem;border-left:3px solid #D97706;">
                <p style="margin:0;font-size:0.82rem;font-weight:700;color:#78350F;">{{ $i + 1 }}. {{ $d }}</p>
            </div>
            @endforeach

        {{-- Sales Funnel Copy --}}
        @elseif(isset($s['funnel_components']))
            @foreach($s['funnel_components'] as $i => $comp)
            <div style="background:#FFF5F5;border-radius:8px;padding:0.55rem 0.85rem;margin-bottom:0.4rem;border-left:3px solid #DC2626;">
                <p style="margin:0;font-size:0.82rem;font-weight:700;color:#7F1D1D;">{{ $i + 1 }}. {{ $comp }}</p>
            </div>
            @endforeach
            @if(!empty($s['email_count']))
            <p style="font-size:0.75rem;color:#5C5470;margin:0.5rem 0 0;"><strong>Email sequence:</strong> {{ $s['email_count'] }} emails · <strong>Lead magnet:</strong> {{ $s['lead_magnet_type'] ?? 'TBD' }}</p>
            @endif

        {{-- Niche Research Report --}}
        @elseif(isset($s['sections']) && is_array($s['sections']))
            @if(!empty($s['page_count']))<p style="font-size:0.78rem;font-weight:600;color:#6C3CE1;margin:0 0 0.5rem;">{{ $s['page_count'] }} pages · {{ $s['geography'] ?? '' }}</p>@endif
            @foreach($s['sections'] as $i => $sec)
            <div style="background:#F5F4FF;border-radius:8px;padding:0.55rem 0.85rem;margin-bottom:0.4rem;border-left:3px solid #6C3CE1;">
                <p style="margin:0;font-size:0.82rem;font-weight:700;color:#1A0D33;">{{ $i + 1 }}. {{ $sec }}</p>
            </div>
            @endforeach

        {{-- Buyer Persona Pack --}}
        @elseif(isset($s['personas']) && is_array($s['personas']))
            @foreach($s['personas'] as $persona)
            <div style="background:#E0F7FA;border-radius:8px;padding:0.6rem 0.85rem;margin-bottom:0.5rem;border-left:3px solid #0891B2;">
                <p style="margin:0;font-size:0.85rem;font-weight:700;color:#0C4A6E;">Persona {{ $persona['number'] ?? '' }}: {{ $persona['title'] ?? $persona['archetype'] ?? 'Persona' }}</p>
                @if(!empty($persona['focus']))<p style="margin:0.2rem 0 0;font-size:0.75rem;color:#5C5470;">{{ $persona['focus'] }}</p>@endif
            </div>
            @endforeach
        @endif
    </div>
    @endif

    {{-- Stage 4: Content --}}
    @if($product->content_output)
    <div class="pai-card" style="margin-bottom:1.25rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:0.85rem;flex-wrap:wrap;">
            <div>
                <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">✓ Claude handled this</span>
                <h2 style="margin:0.3rem 0 0;font-size:1rem;font-weight:700;color:#0F0A1E;">Stage 4 — Full Content</h2>
            </div>
            <div style="display:flex;gap:0.4rem;">
                <form method="POST" action="{{ route('digital-products.regenerate', [$product, 4]) }}">
                    @csrf
                    <button type="submit" class="btn-outline" style="padding:0.4rem 0.85rem;font-size:0.75rem;" {{ $busy ? 'disabled' : '' }}>Regenerate</button>
                </form>
                @if($product->current_stage == 4 && ! $busy)
                <form method="POST" action="{{ route('digital-products.advance', $product) }}">
                    @csrf
                    <button type="submit" class="btn-primary" style="padding:0.4rem 0.85rem;font-size:0.75rem;">Build launch pack →</button>
                </form>
                @endif
            </div>
        </div>

        @php
            $isExtended = in_array($product->product_type, \App\Models\DigitalProduct::extendedTypes());
            $contentSections = null;
            if ($isExtended) {
                $decoded = json_decode($product->content_output, true);
                $contentSections = is_array($decoded) && isset($decoded['sections']) ? $decoded['sections'] : null;
            }
        @endphp

        @if($contentSections)
            {{-- Extended types: show section list with preview --}}
            <div style="background:#ECFDF5;border-left:3px solid #10B981;border-radius:8px;padding:0.65rem 0.9rem;margin-bottom:0.85rem;">
                <p style="margin:0;font-size:0.78rem;font-weight:700;color:#065F46;">✓ {{ count($contentSections) }} sections generated — usage guide is first (included in PDF & DOCX)</p>
            </div>
            @foreach($contentSections as $i => $sec)
            <details style="margin-bottom:0.4rem;" {{ $i === 0 ? 'open' : '' }}>
                <summary style="cursor:pointer;background:#F5F4FF;border-radius:8px;padding:0.55rem 0.85rem;font-size:0.82rem;font-weight:700;color:#0F0A1E;list-style:none;display:flex;align-items:center;gap:0.5rem;">
                    <span style="color:#6C3CE1;font-size:0.7rem;">{{ $i + 1 }}</span> {{ $sec['title'] ?? 'Section '.($i+1) }}
                </summary>
                <div style="padding:0.6rem 0.85rem;font-size:0.76rem;color:#5C5470;line-height:1.6;max-height:200px;overflow-y:auto;border:1px solid #E4E0F0;border-top:none;border-radius:0 0 8px 8px;white-space:pre-wrap;">{{ mb_substr($sec['body'] ?? '', 0, 600) }}{{ strlen($sec['body'] ?? '') > 600 ? '…' : '' }}</div>
            </details>
            @endforeach
        @else
            {{-- Original types: plain text --}}
            <pre style="font-family:inherit;white-space:pre-wrap;font-size:0.78rem;color:#0F0A1E;line-height:1.6;background:#FAF9FF;padding:1rem;border-radius:10px;margin:0;max-height:480px;overflow-y:auto;border:1px solid #E4E0F0;">{{ $product->content_output }}</pre>
        @endif
    </div>
    @endif

    {{-- Stage 5: Publish Pack --}}
    @if($product->publish_pack)
    @php $pk = $product->publish_pack; @endphp
    <div class="pai-card" style="margin-bottom:1.25rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:0.85rem;flex-wrap:wrap;">
            <div>
                <span style="background:#ECFDF5;color:#065F46;border:1px solid #6EE7B7;border-radius:999px;padding:2px 8px;font-size:0.7rem;font-weight:700;">✓ Claude handled this</span>
                <h2 style="margin:0.3rem 0 0;font-size:1rem;font-weight:700;color:#0F0A1E;">Stage 5 — Launch Pack</h2>
            </div>
            <div style="display:flex;gap:0.4rem;">
                <form method="POST" action="{{ route('digital-products.regenerate', [$product, 5]) }}">
                    @csrf
                    <button type="submit" class="btn-outline" style="padding:0.4rem 0.85rem;font-size:0.75rem;" {{ $busy ? 'disabled' : '' }}>Regenerate</button>
                </form>
            </div>
        </div>

        @if(isset($pk['sales_page']))
        <details style="margin-bottom:0.6rem;" open>
            <summary style="cursor:pointer;font-size:0.88rem;font-weight:700;color:#0F0A1E;padding:0.4rem 0;">📄 Sales page copy</summary>
            <div style="padding:0.6rem;background:#FAF9FF;border-radius:8px;margin-top:0.4rem;">
                <p style="font-size:0.95rem;font-weight:800;color:#0F0A1E;margin:0 0 0.3rem;">{{ $pk['sales_page']['headline'] ?? '' }}</p>
                <p style="font-size:0.82rem;color:#5C5470;margin:0 0 0.85rem;">{{ $pk['sales_page']['subheadline'] ?? '' }}</p>
                <p style="font-size:0.8rem;color:#0F0A1E;margin:0 0 0.85rem;line-height:1.6;">{{ $pk['sales_page']['hook'] ?? '' }}</p>

                @if(isset($pk['sales_page']['whats_inside']))
                <p style="font-size:0.8rem;font-weight:700;color:#0F0A1E;margin:0 0 0.3rem;">What's inside</p>
                <ul style="margin:0 0 0.85rem;padding-left:1.2rem;font-size:0.78rem;color:#5C5470;line-height:1.6;">
                    @foreach($pk['sales_page']['whats_inside'] as $item)<li>{{ $item }}</li>@endforeach
                </ul>
                @endif

                @if(isset($pk['sales_page']['cta']))
                <p style="font-size:0.85rem;font-weight:700;color:#6C3CE1;margin:0.85rem 0 0;">CTA: {{ $pk['sales_page']['cta'] }}</p>
                @endif
            </div>
        </details>
        @endif

        @if(isset($pk['social_posts']))
        <details style="margin-bottom:0.6rem;">
            <summary style="cursor:pointer;font-size:0.88rem;font-weight:700;color:#0F0A1E;padding:0.4rem 0;">📱 Social posts</summary>
            <div style="padding:0.6rem;background:#FAF9FF;border-radius:8px;margin-top:0.4rem;">
                @foreach(['linkedin'=>'LinkedIn','twitter'=>'X/Twitter','instagram'=>'Instagram','pinterest'=>'Pinterest','whatsapp'=>'WhatsApp'] as $k => $label)
                @if(isset($pk['social_posts'][$k]))
                <p style="font-size:0.78rem;font-weight:700;color:#5A2EC9;margin:0.6rem 0 0.2rem;">{{ $label }}</p>
                <p style="font-size:0.78rem;color:#0F0A1E;margin:0;line-height:1.6;white-space:pre-wrap;">{{ $pk['social_posts'][$k] }}</p>
                @endif
                @endforeach
            </div>
        </details>
        @endif

        @if(isset($pk['launch_emails']))
        <details style="margin-bottom:0.6rem;">
            <summary style="cursor:pointer;font-size:0.88rem;font-weight:700;color:#0F0A1E;padding:0.4rem 0;">📧 Launch emails (3-email sequence)</summary>
            <div style="padding:0.6rem;background:#FAF9FF;border-radius:8px;margin-top:0.4rem;">
                @foreach(['email_1'=>'Email 1','email_2'=>'Email 2','email_3'=>'Email 3'] as $k => $label)
                @if(isset($pk['launch_emails'][$k]))
                @php $em = $pk['launch_emails'][$k]; @endphp
                <div style="border-bottom:1px solid #E4E0F0;padding:0.6rem 0;">
                    <p style="font-size:0.72rem;color:#9B93B0;margin:0;">{{ $em['send_timing'] ?? '' }}</p>
                    <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0.2rem 0;">{{ $label }}: {{ $em['subject'] ?? '' }}</p>
                    <p style="font-size:0.78rem;color:#5C5470;margin:0;line-height:1.6;white-space:pre-wrap;">{{ $em['body'] ?? '' }}</p>
                </div>
                @endif
                @endforeach
            </div>
        </details>
        @endif

        {{-- Download buttons --}}
        @php
            $isXlsxType = in_array($product->product_type, \App\Models\DigitalProduct::xlsxTypes());
            $isKdpType  = in_array($product->product_type, \App\Models\DigitalProduct::kdpTypes());
            $isNoKdp    = in_array($product->product_type, ['excel_tracker','notion_business_os']);
        @endphp
        <div style="border-top:1px solid #E4E0F0;margin-top:1rem;padding-top:1rem;">
            <p style="font-size:0.85rem;font-weight:700;color:#0F0A1E;margin:0 0 0.5rem;">📥 Download your product</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:0.5rem;">
                <a href="{{ route('digital-products.export', [$product, 'premium']) }}" class="btn-primary" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">
                    📄 Premium PDF
                    <div style="font-size:0.65rem;font-weight:500;opacity:0.85;margin-top:2px;">Gumroad · Selar · Payhip</div>
                </a>
                <a href="{{ route('digital-products.export', [$product, 'master']) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">
                    ✏️ Master DOCX
                    <div style="font-size:0.65rem;font-weight:500;opacity:0.7;margin-top:2px;">Editable Word file</div>
                </a>
                @if($isXlsxType)
                <a href="{{ route('digital-products.export', [$product, 'xlsx']) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;border-color:#059669;color:#059669;">
                    📊 Download Excel File
                    <div style="font-size:0.65rem;font-weight:500;opacity:0.7;margin-top:2px;">Excel · Google Sheets</div>
                </a>
                @endif
                @if($isKdpType)
                <a href="{{ route('digital-products.export', [$product, 'kdp']) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">
                    📦 KDP Word File
                    <div style="font-size:0.65rem;font-weight:500;opacity:0.7;margin-top:2px;">Amazon KDP upload</div>
                </a>
                @elseif($isNoKdp)
                <div style="text-align:center;padding:0.65rem 0.85rem;font-size:0.75rem;color:#9B93B0;background:#F5F4FF;border-radius:10px;border:1px dashed #D0CAEE;">
                    ℹ️ No KDP version<br>
                    <span style="font-size:0.65rem;">Not a book format</span>
                </div>
                @else
                <a href="{{ route('digital-products.export', [$product, 'kdp']) }}" class="btn-outline" style="text-decoration:none;text-align:center;padding:0.65rem 0.85rem;font-size:0.78rem;">
                    📦 KDP Word File
                    <div style="font-size:0.65rem;font-weight:500;opacity:0.7;margin-top:2px;">Amazon KDP upload</div>
                </a>
                @endif
            </div>
        </div>

        {{-- Notion OS orange badge — shown for notion_business_os only --}}
        @if($product->product_type === 'notion_business_os')
        <div style="background:#FEF3C7;border:2px solid #F59E0B;border-radius:10px;padding:1rem 1.1rem;margin-top:1rem;">
            <p style="font-size:0.88rem;font-weight:800;color:#92400E;margin:0 0 0.6rem;">🟧 Your action needed — Build and sell your Notion OS</p>
            <p style="font-size:0.78rem;color:#78350F;margin:0 0 0.85rem;line-height:1.6;">Download your PDF spec above. Then follow these 4 steps to start selling unlimited copies.</p>

            <div style="display:flex;flex-direction:column;gap:0.6rem;">
                <div style="background:#FFFBEB;border-radius:8px;padding:0.65rem 0.85rem;">
                    <p style="margin:0 0 0.25rem;font-size:0.8rem;font-weight:700;color:#78350F;">Step 1 — Build the workspace in Notion <span style="font-weight:400;color:#92400E;">(3–5 hours)</span></p>
                    <p style="margin:0;font-size:0.75rem;color:#5C5470;line-height:1.6;">Open your PDF specification. Follow Section 1 (Getting Started) to create each database and page in your own Notion account using the spec as your guide. <strong>Alternative:</strong> Hire a Notion builder on Fiverr ($50–$150) to build it from your spec.</p>
                </div>
                <div style="background:#FFFBEB;border-radius:8px;padding:0.65rem 0.85rem;">
                    <p style="margin:0 0 0.25rem;font-size:0.8rem;font-weight:700;color:#78350F;">Step 2 — Get your duplicate link</p>
                    <p style="margin:0;font-size:0.75rem;color:#5C5470;line-height:1.6;">In Notion, click <strong>Share</strong> on the workspace root page → <strong>Publish to web</strong> → enable <strong>Allow duplicate as template</strong> → copy the link.</p>
                </div>
                <div style="background:#FFFBEB;border-radius:8px;padding:0.65rem 0.85rem;">
                    <p style="margin:0 0 0.25rem;font-size:0.8rem;font-weight:700;color:#78350F;">Step 3 — Sell the link</p>
                    <p style="margin:0;font-size:0.75rem;color:#5C5470;line-height:1.6;">List on <strong>Gumroad</strong>, <strong>Etsy</strong>, or <strong>Selar</strong>. When a buyer purchases, they receive your duplicate link. They click it → click Duplicate → done. You never send anything manually — the platform delivers the link automatically.</p>
                </div>
                <div style="background:#FFFBEB;border-radius:8px;padding:0.65rem 0.85rem;">
                    <p style="margin:0 0 0.25rem;font-size:0.8rem;font-weight:700;color:#78350F;">Step 4 — Sell forever</p>
                    <p style="margin:0;font-size:0.75rem;color:#5C5470;line-height:1.6;">One build. Unlimited sales. Update the workspace any time — the duplicate link always shares the latest version.</p>
                </div>
            </div>

            <div style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid #FDE68A;">
                <p style="font-size:0.75rem;font-weight:700;color:#92400E;margin:0 0 0.25rem;">💰 Recommended pricing</p>
                <p style="font-size:0.75rem;color:#78350F;margin:0;">Gumroad / Selar: <strong>$97–$297</strong> · Etsy: <strong>$47–$97</strong></p>
            </div>
        </div>
        @endif

        {{-- Pricing strip (not for Notion OS) --}}
        @if($product->product_type !== 'notion_business_os')
        <div style="display:flex;gap:0.4rem;flex-wrap:wrap;justify-content:center;margin-top:1rem;">
            <span style="background:#ECFDF5;color:#065F46;border-radius:999px;padding:3px 10px;font-size:0.7rem;font-weight:600;">Selar / Gumroad / Payhip: $27–$97</span>
            <span style="background:#FEF3C7;color:#92400E;border-radius:999px;padding:3px 10px;font-size:0.7rem;font-weight:600;">Etsy: $17–$47</span>
            @if($isKdpType)
            <span style="background:#FFFBEB;color:#92400E;border-radius:999px;padding:3px 10px;font-size:0.7rem;font-weight:600;">Amazon Kindle: $9.99</span>
            <span style="background:#FFFBEB;color:#92400E;border-radius:999px;padding:3px 10px;font-size:0.7rem;font-weight:600;">Paperback: $19.99–$24.99</span>
            @endif
        </div>
        @endif

        {{-- Native platform upload steps --}}
        @if(isset($pk['platform_upload_steps']))
        <div style="background:#FFFBEB;border-left:3px solid #F59E0B;border-radius:8px;padding:0.85rem 1rem;margin-top:1rem;">
            <p style="font-size:0.8rem;font-weight:700;color:#92400E;margin:0 0 0.4rem;">🟧 Your action needed — Upload to {{ ucfirst($product->recommended_platform ?: 'Gumroad') }}</p>
            <ol style="margin:0;padding-left:1.2rem;font-size:0.75rem;color:#5C5470;line-height:1.7;">
                @foreach($pk['platform_upload_steps'] as $step)
                <li>{{ $step }}</li>
                @endforeach
            </ol>
        </div>
        @endif

        {{-- Etsy upload steps --}}
        @if($product->product_type !== 'notion_business_os')
        <div style="background:#FFFBEB;border-left:3px solid #F59E0B;border-radius:8px;padding:0.85rem 1rem;margin-top:0.75rem;">
            <p style="font-size:0.8rem;font-weight:700;color:#92400E;margin:0 0 0.4rem;">🟧 Your action needed — Upload to Etsy</p>
            <ol style="margin:0;padding-left:1.2rem;font-size:0.75rem;color:#5C5470;line-height:1.7;">
                <li>Go to <strong>etsy.com/sell</strong> and create a free seller account</li>
                <li>Click <strong>Add a listing → Digital download</strong></li>
                <li>Upload your <strong>premium.pdf</strong> file (Etsy accepts up to 5 files, max 20MB each)</li>
                <li>Add your product title from the listing above</li>
                <li>Write your description — copy from the sales page above</li>
                <li>Add tags — use the 7 keywords from your listing as Etsy search tags</li>
                <li>Set your price between <strong>$17–$47</strong> (Etsy buyers expect slightly lower than Gumroad)</li>
                <li>Etsy charges <strong>$0.20 listing fee</strong> per product</li>
                <li>Set up <strong>Payoneer</strong> at payoneer.com to receive Etsy payouts</li>
                <li>Click <strong>Publish</strong> — your product is live immediately</li>
            </ol>
        </div>
        @endif

        {{-- Amazon KDP upload steps (only for KDP-eligible types) --}}
        @if($isKdpType)
        <div style="background:#FFFBEB;border-left:3px solid #F59E0B;border-radius:8px;padding:0.85rem 1rem;margin-top:0.75rem;">
            <p style="font-size:0.8rem;font-weight:700;color:#92400E;margin:0 0 0.4rem;">🟧 Your action needed — Upload to Amazon KDP</p>
            <ol style="margin:0;padding-left:1.2rem;font-size:0.75rem;color:#5C5470;line-height:1.7;">
                <li>Go to <strong>kdp.amazon.com</strong> and sign in</li>
                <li>Click <strong>Add new title → Kindle ebook</strong> or <strong>Paperback</strong></li>
                <li>Fill title and description from the listing generated above</li>
                <li>Upload <strong>kdp-version.docx</strong> as the manuscript</li>
                <li>Upload a cover image (use Canva or Midjourney)</li>
                <li>Set Kindle price <strong>$9.99</strong> · Paperback <strong>$19.99–$24.99</strong></li>
                <li>Click <strong>Publish</strong> — live within 24–72 hours</li>
                <li>Set up <strong>Payoneer</strong> at payoneer.com to receive royalties</li>
            </ol>
        </div>
        @endif
    </div>
    @endif

</x-app-layout>
