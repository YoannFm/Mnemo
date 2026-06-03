<x-app-layout>
    <x-slot name="header">
        <h2 class="fw-bold">{{ $page->title }}</h2>
    </x-slot>

    <div class="container py-4">
        <div class="card shadow">
            <div class="card-body">
                {!! $page->content !!}
            </div>
        </div>
    </div>
</x-app-layout>
