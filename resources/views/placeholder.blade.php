<x-app-layout>
    <x-slot name="title">{{ $page }}</x-slot>
    <div style="max-width:480px;text-align:center;margin:4rem auto;">
        <div style="width:56px;height:56px;background:#F5F4FF;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
            <svg width="24" height="24" fill="none" stroke="#6C3CE1" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        </div>
        <p style="font-size:1.1rem;font-weight:700;color:#0F0A1E;margin:0 0 0.4rem;">{{ $page }}</p>
        <p style="font-size:0.85rem;color:#9B93B0;margin:0;">Coming in a future phase.</p>
    </div>
</x-app-layout>
