<x-app-layout>
    <x-slot name="title">Sales</x-slot>

    <div style="max-width:760px;">

        <div style="margin-bottom:1.75rem;">
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">Sales</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">Manually log your revenue from Amazon, Etsy, and Gumroad.</p>
        </div>

        {{-- Metric cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-bottom:1.75rem;">
            <div class="metric-card metric-violet">
                <div class="metric-label">This Month</div>
                <div class="metric-value">{{ number_format($totals['month'], 2) }}</div>
                <div class="metric-sub">{{ $totals['currency'] }}</div>
            </div>
            <div class="metric-card metric-green">
                <div class="metric-label">All Time</div>
                <div class="metric-value">{{ number_format($totals['allTime'], 2) }}</div>
                <div class="metric-sub">{{ $totals['currency'] }}</div>
            </div>
            <div class="metric-card metric-amber">
                <div class="metric-label">Sold This Month</div>
                <div class="metric-value">{{ $totals['soldMonth'] }}</div>
                <div class="metric-sub">products</div>
            </div>
        </div>

        {{-- Log a sale --}}
        <div class="pai-card" style="margin-bottom:1.5rem;">
            <p style="font-size:0.9rem;font-weight:700;color:#0F0A1E;margin:0 0 1rem;">Log a sale</p>
            <form method="POST" action="{{ route('sales.store') }}"
                  onsubmit="document.getElementById('btn-log').textContent='Saving...';document.getElementById('btn-log').style.opacity='0.7';">
                @csrf

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.875rem;margin-bottom:0.875rem;">
                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.35rem;">Topic <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="topic" placeholder="e.g. Parenting toddlers" class="pai-input" required value="{{ old('topic') }}" />
                    </div>
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.35rem;">Product type</label>
                        <select name="product_type" class="pai-input" style="cursor:pointer;">
                            @foreach(['ebook'=>'Ebook','workbook'=>'Workbook','printable'=>'Printable','bundle'=>'Bundle'] as $v => $l)
                            <option value="{{ $v }}" {{ old('product_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.35rem;">Platform</label>
                        <select name="platform" class="pai-input" style="cursor:pointer;">
                            @foreach(['amazon'=>'Amazon KDP','etsy'=>'Etsy','gumroad'=>'Gumroad'] as $v => $l)
                            <option value="{{ $v }}" {{ old('platform') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.35rem;">Amount</label>
                        <input type="number" name="amount" step="0.01" min="0" placeholder="9.99" class="pai-input" required value="{{ old('amount') }}" />
                    </div>
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.35rem;">Currency</label>
                        <select name="currency" class="pai-input" style="cursor:pointer;">
                            @foreach(['USD','NGN','GBP','EUR','GHS','KES','ZAR'] as $c)
                            <option value="{{ $c }}" {{ old('currency', 'USD') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.35rem;">Date</label>
                        <input type="date" name="sale_date" class="pai-input" required value="{{ old('sale_date', today()->toDateString()) }}" />
                    </div>
                    <div>
                        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.35rem;">Linked run <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <select name="run_id" class="pai-input" style="cursor:pointer;">
                            <option value="">— none —</option>
                            @foreach($runs as $run)
                            <option value="{{ $run->id }}">{{ Str::limit($run->topic, 40) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="grid-column:span 2;">
                        <label style="display:block;font-size:0.78rem;font-weight:600;color:#0F0A1E;margin-bottom:0.35rem;">Notes <span style="color:#9B93B0;font-weight:400;">(optional)</span></label>
                        <input type="text" name="notes" placeholder="e.g. First sale after launch" class="pai-input" value="{{ old('notes') }}" />
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="btn-log">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Log sale
                </button>
            </form>
        </div>

        {{-- By topic + by platform --}}
        @if($byTopic->isNotEmpty())
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
            <div class="pai-card">
                <p style="font-size:0.88rem;font-weight:700;color:#0F0A1E;margin:0 0 0.875rem;">Revenue by topic</p>
                @foreach($byTopic as $row)
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                    <p style="font-size:0.8rem;color:#0F0A1E;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;padding-right:0.5rem;">{{ $row->topic }}</p>
                    <p style="font-size:0.8rem;font-weight:700;color:#5A2EC9;margin:0;flex-shrink:0;">{{ number_format($row->total, 2) }}</p>
                </div>
                @endforeach
            </div>
            <div class="pai-card">
                <p style="font-size:0.88rem;font-weight:700;color:#0F0A1E;margin:0 0 0.875rem;">Revenue by platform</p>
                @foreach($byPlatform as $row)
                @php $icons = ['amazon'=>'📦','etsy'=>'🛍️','gumroad'=>'💾']; @endphp
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                    <p style="font-size:0.8rem;color:#0F0A1E;margin:0;">{{ $icons[$row->platform] ?? '' }} {{ ucfirst($row->platform) }}</p>
                    <p style="font-size:0.8rem;font-weight:700;color:#5A2EC9;margin:0;">{{ number_format($row->total, 2) }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Sales table --}}
        @if($sales->isNotEmpty())
        <div class="pai-card" style="padding:0;overflow:hidden;">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid #F5F4FF;">
                <p style="font-size:0.88rem;font-weight:700;color:#0F0A1E;margin:0;">All sales</p>
            </div>
            @foreach($sales as $sale)
            <div style="display:flex;align-items:center;gap:0.875rem;padding:0.75rem 1.25rem;border-bottom:1px solid #F5F4FF;">
                <div style="flex:1;min-width:0;">
                    <p style="font-size:0.82rem;font-weight:600;color:#0F0A1E;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $sale->topic }}</p>
                    <p style="font-size:0.72rem;color:#9B93B0;margin:0.1rem 0 0;">{{ ucfirst($sale->product_type) }} · {{ ucfirst($sale->platform) }} · {{ $sale->sale_date->format('M j, Y') }}</p>
                </div>
                <p style="font-size:0.85rem;font-weight:700;color:#5A2EC9;margin:0;flex-shrink:0;">{{ $sale->currency }} {{ number_format($sale->amount, 2) }}</p>
                <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Remove this sale entry?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none;border:none;color:#E4E0F0;cursor:pointer;font-size:1rem;line-height:1;" onmouseover="this.style.color='#EF4444'" onmouseout="this.style.color='#E4E0F0'">✕</button>
                </form>
            </div>
            @endforeach
        </div>
        <div style="margin-top:1rem;">{{ $sales->links() }}</div>
        @endif

    </div>

    <style>
        @media (max-width:640px) {
            [style*="grid-template-columns:1fr 1fr"] { grid-template-columns:1fr !important; }
        }
    </style>

</x-app-layout>
