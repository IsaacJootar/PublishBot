<x-app-layout>
    <x-slot name="title">Edit series</x-slot>

    <div style="margin-bottom:1.25rem;">
        <a href="{{ route('series.show', $series) }}" style="font-size:0.78rem;color:#6C3CE1;text-decoration:none;font-weight:600;">← Back to series</a>
    </div>

    <div style="max-width:720px;">
        <div style="margin-bottom:1.5rem;">
            <h1 style="font-size:1.375rem;font-weight:800;color:#0F0A1E;margin:0 0 0.2rem;">Edit {{ $series->name }}</h1>
            <p style="font-size:0.875rem;color:#5C5470;margin:0;">Changes apply to every book you start next — existing books are not regenerated.</p>
        </div>

        @include('series._form')
    </div>
</x-app-layout>
