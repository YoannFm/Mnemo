<x-app-layout>
    <x-slot name="pageTitle">{{ $post->title }}</x-slot>

    <div class="container py-4" style="max-width:800px;">
        @if ($post->image)
            <img src="{{ $post->imageUrl() }}" class="img-fluid rounded mb-4 w-100"
                 style="max-height:350px;object-fit:cover;" alt="{{ $post->title }}">
        @endif

        <h1 class="mb-2">{{ $post->title }}</h1>

        <div class="mb-4" style="font-size:.85rem;color:var(--text-muted);">
            <i class="bi bi-calendar3 me-1"></i> {{ $post->published_at->format('d/m/Y') }}
            &nbsp;·&nbsp;
            <i class="bi bi-person me-1"></i> {{ $post->author->name }}
            @if ($post->is_pinned)
                &nbsp;·&nbsp;<i class="bi bi-pin-angle text-primary"></i> Epinglé
            @endif
        </div>

        @if ($post->description)
            <p class="lead mb-4">{{ $post->description }}</p>
        @endif

        <div class="card">
            <div class="card-body">
                {!! $post->content !!}
            </div>
        </div>
    </div>
</x-app-layout>
