<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="publishai">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} — PublishAI</title>
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
        <div style="padding: 1.5rem 1.25rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.08);">
            <a href="{{ route('dashboard') }}" style="display:flex; align-items:center; gap:0.625rem; text-decoration:none;">
                <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#6C3CE1,#4C2CA1);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(108,60,225,0.4);">
                    <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <div style="color:#fff;font-weight:800;font-size:1rem;line-height:1.1;">PublishAI</div>
                    <div style="color:rgba(255,255,255,0.35);font-size:0.7rem;margin-top:1px;">Publishing Engine</div>
                </div>
            </a>
        </div>

        {{-- Navigation --}}
        <nav style="flex:1; padding: 0.75rem 0.75rem; overflow-y:auto;">
            @php
                $navItems = [
                    ['route' => 'dashboard',          'label' => 'Dashboard',       'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'voice.index',         'label' => 'My Voice',        'icon' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
                    ['route' => 'research.index',      'label' => 'Research',        'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
                    ['route' => 'outline.index',       'label' => 'Outline',         'icon' => 'M4 6h16M4 10h16M4 14h10'],
                    ['route' => 'manuscript.index',    'label' => 'Write',           'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                    ['route' => 'illustrations.index', 'label' => 'Illustrations',   'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['route' => 'kdp.index',           'label' => 'KDP Listing',     'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                    ['route' => 'digital.index',       'label' => 'Digital Product', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                    ['route' => 'launch.index',        'label' => 'Launch',          'icon' => 'M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7a6 6 0 01-2.7 2.699'],
                    ['route' => 'series.index',        'label' => 'My Series',       'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                    ['route' => 'projects.index',      'label' => 'My Projects',     'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                    ['route' => 'settings.index',      'label' => 'Settings',        'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
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

        {{-- User footer --}}
        @auth
        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding: 1rem 1rem;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#6C3CE1,#4C2CA1);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:#fff;flex-shrink:0;">
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
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                message: "{{ addslashes(session('toast.message')) }}",
                type: "{{ session('toast.type') }}"
            }
        }));
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
