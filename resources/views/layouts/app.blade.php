<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="publishai">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — PublishAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-base-200 min-h-screen">

<div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"
        x-transition
    ></div>

    {{-- Sidebar --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed top-0 left-0 h-full w-64 z-30 transition-transform duration-300 flex flex-col"
        style="background: linear-gradient(165deg, #1A0D33 0%, #0D0719 100%);"
    >
        {{-- Logo --}}
        <div class="px-6 py-5 border-b border-white/10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: #6C3CE1;">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg">PublishAI</span>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @php
                $navItems = [
                    ['route' => 'dashboard',         'label' => 'Dashboard',       'icon' => 'home'],
                    ['route' => 'voice.index',        'label' => 'My Voice',        'icon' => 'microphone'],
                    ['route' => 'research.index',     'label' => 'Research',        'icon' => 'magnifying-glass'],
                    ['route' => 'outline.index',      'label' => 'Outline',         'icon' => 'list-bullet'],
                    ['route' => 'manuscript.index',   'label' => 'Write',           'icon' => 'pencil-square'],
                    ['route' => 'illustrations.index','label' => 'Illustrations',   'icon' => 'photo'],
                    ['route' => 'kdp.index',          'label' => 'KDP Listing',     'icon' => 'shopping-bag'],
                    ['route' => 'digital.index',      'label' => 'Digital Product', 'icon' => 'cube'],
                    ['route' => 'launch.index',       'label' => 'Launch',          'icon' => 'rocket-launch'],
                    ['route' => 'series.index',       'label' => 'My Series',       'icon' => 'book-open'],
                    ['route' => 'projects.index',     'label' => 'My Projects',     'icon' => 'folder'],
                    ['route' => 'settings.index',     'label' => 'Settings',        'icon' => 'cog-6-tooth'],
                ];
            @endphp

            @foreach($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['route'] . '*');
                @endphp
                @if(\Illuminate\Support\Facades\Route::has($item['route']))
                <a
                    href="{{ route($item['route']) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors text-sm font-medium"
                    style="{{ $isActive ? 'background: rgba(108,60,225,0.35); border-left: 3px solid #6C3CE1; color: #ffffff;' : 'border-left: 3px solid transparent; color: rgba(255,255,255,0.5);' }}"
                >
                    @if($item['icon'] === 'home')
                        <x-heroicon-o-home class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'microphone')
                        <x-heroicon-o-microphone class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'magnifying-glass')
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'list-bullet')
                        <x-heroicon-o-list-bullet class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'pencil-square')
                        <x-heroicon-o-pencil-square class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'photo')
                        <x-heroicon-o-photo class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'shopping-bag')
                        <x-heroicon-o-shopping-bag class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'cube')
                        <x-heroicon-o-cube class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'rocket-launch')
                        <x-heroicon-o-rocket-launch class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'book-open')
                        <x-heroicon-o-book-open class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'folder')
                        <x-heroicon-o-folder class="w-5 h-5 shrink-0" />
                    @elseif($item['icon'] === 'cog-6-tooth')
                        <x-heroicon-o-cog-6-tooth class="w-5 h-5 shrink-0" />
                    @endif
                    {{ $item['label'] }}
                </a>
                @endif
            @endforeach
        </nav>

        {{-- User footer --}}
        @auth
        <div class="px-4 py-4 border-t border-white/10">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white shrink-0" style="background: #6C3CE1;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-white/50 hover:text-white transition-colors">
                        <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </aside>

    {{-- Main content area --}}
    <div class="flex-1 flex flex-col lg:ml-64 min-w-0">

        {{-- Mobile topbar --}}
        <header class="lg:hidden flex items-center justify-between px-4 py-3 bg-white border-b border-base-300 sticky top-0 z-10">
            <button @click="sidebarOpen = true" class="btn btn-ghost btn-sm">
                <x-heroicon-o-bars-3 class="w-5 h-5" />
            </button>
            <span class="font-bold text-base-content">PublishAI</span>
            <div class="w-8"></div>
        </header>

        {{-- Page --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>
</div>

{{-- Flash toast from controller redirect --}}
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

@livewireScripts
</body>
</html>
