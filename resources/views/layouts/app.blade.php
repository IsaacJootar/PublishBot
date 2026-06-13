<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="publishai">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — PublishBot</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased" style="font-family: 'Figtree', sans-serif;">

<div class="pai-shell">

    {{-- Mobile scrim --}}
    <div class="pai-scrim" id="pai-scrim" onclick="closeSidebar()"></div>

    {{-- Sidebar --}}
    <aside class="pai-sidebar" id="pai-sidebar">

        {{-- Close btn (mobile) --}}
        <button class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Close menu">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Logo --}}
        <div style="padding: 1.5rem 1.25rem 1.25rem; border-bottom: 1px solid rgba(245,158,11,0.1);">
            <a href="{{ route('dashboard') }}" style="display:flex; align-items:center; gap:0.625rem; text-decoration:none;">
                <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#D97706,#B45309);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(217,119,6,0.45);">
                    <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <div style="color:#fff;font-weight:800;font-size:1rem;line-height:1.1;">PublishBot</div>
                    <div style="color:rgba(255,210,100,0.4);font-size:0.7rem;margin-top:1px;">Publishing Engine</div>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav style="flex:1; padding: 0.75rem 0.75rem; overflow-y:auto;">
            @php
                $navItems = [
                    ['route' => 'dashboard',    'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'voice.index',  'label' => 'My Voice',  'icon' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
                    ['route' => 'runs.index',   'label' => 'History',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'sales.index',  'label' => 'Sales',     'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'settings.index','label' => 'Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php $active = \Illuminate\Support\Facades\Route::has($item['route']) && request()->routeIs($item['route'] . '*'); @endphp
                @if(\Illuminate\Support\Facades\Route::has($item['route']))
                <a href="{{ route($item['route']) }}" class="pai-nav-item {{ $active ? 'active' : '' }}">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
                @endif
            @endforeach
        </nav>

        {{-- Recent topics --}}
        @auth
        @php $recentRuns = Auth::user()->runs()->orderByDesc('created_at')->limit(5)->get(['id','topic']); @endphp
        @if($recentRuns->isNotEmpty())
        <div style="padding:0.75rem 0.75rem 0;border-top:1px solid rgba(245,158,11,0.06);">
            <p style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(245,158,11,0.4);margin:0 0 0.4rem;padding:0 0.375rem;">Recent topics</p>
            @foreach($recentRuns as $recentRun)
            <a href="{{ route('runs.show', $recentRun) }}" style="display:block;font-size:0.75rem;color:rgba(255,255,255,0.35);text-decoration:none;padding:0.3rem 0.375rem;border-radius:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;transition:color 0.15s,background 0.15s;"
               onmouseover="this.style.color='rgba(255,230,150,0.8)';this.style.background='rgba(245,158,11,0.07)'"
               onmouseout="this.style.color='rgba(255,255,255,0.35)';this.style.background='transparent'">
                {{ \Illuminate\Support\Str::limit($recentRun->topic, 28) }}
            </a>
            @endforeach
        </div>
        @endif
        @endauth

        {{-- User footer --}}
        @auth
        <div style="border-top: 1px solid rgba(245,158,11,0.1); padding: 1rem 1rem;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#D97706,#B45309);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:#fff;flex-shrink:0;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="color:#fff;font-size:0.8rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->name }}</div>
                    <div style="color:rgba(255,255,255,0.35);font-size:0.7rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Auth::user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,0.3);padding:4px;" title="Log out"
                        onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,0.3)'">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </aside>

    {{-- Main content --}}
    <div class="pai-main">

        {{-- Topbar --}}
        <header class="pai-topbar">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <button class="pai-hamburger" onclick="openSidebar()" aria-label="Open menu">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <span style="font-weight:700;color:#0F0A1E;font-size:0.95rem;">
                    {{ $title ?? 'Dashboard' }}
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:0.5rem;">
                <a href="{{ route('settings.index') }}" style="text-decoration:none;">
                    <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6C3CE1,#4C2CA1);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:#fff;">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                </a>
            </div>
        </header>

        {{-- Page --}}
        <main class="pai-page">
            {{ $slot }}
        </main>
    </div>
</div>

{{-- Flash toast --}}
@if(session('toast'))
<script>
    window.addEventListener('load', () => {
        window.showToast({ message: "{{ addslashes(session('toast.message')) }}", type: "{{ session('toast.type') }}" });
    });
</script>
@endif

<x-toast />

<script>
function openSidebar() {
    document.getElementById('pai-sidebar').classList.add('is-open');
    document.getElementById('pai-scrim').classList.add('is-open');
}
function closeSidebar() {
    document.getElementById('pai-sidebar').classList.remove('is-open');
    document.getElementById('pai-scrim').classList.remove('is-open');
}
</script>

@livewireScripts
</body>
</html>
