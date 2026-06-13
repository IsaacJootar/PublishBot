<x-app-layout>
    <x-slot name="title">New series</x-slot>

    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('series.index') }}" style="font-size:0.78rem;color:#6C3CE1;text-decoration:none;font-weight:600;">← All series</a>
    </div>

    <div style="max-width:720px;">
        <div style="margin-bottom:1.5rem;">
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">Create a new series</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">Lock characters and style once. Every future book inherits them automatically.</p>
        </div>

        @include('series._form')
    </div>
</x-app-layout>
