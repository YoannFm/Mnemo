<x-app-layout>
    <x-slot name="pageTitle">{{ $page->title }}</x-slot>

    <div class="container py-4">
        <h1 class="mb-4">{{ $page->title }}</h1>
        <div class="card shadow">
            <div class="card-body">
                {!! $page->content !!}
            </div>
        </div>
    </div>
</x-app-layout>
