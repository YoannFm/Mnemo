{{-- Tags filter for the public library --}}
@if (isset($libraryTags) && $libraryTags->isNotEmpty())
<div class="mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <span style="font-size:.8rem;color:var(--text-muted);white-space:nowrap;">
            <i class="bi bi-tags me-1"></i>Filtrer :
        </span>
        @php $activeTags = request()->has('tags') ? (array) request('tags') : []; @endphp

        <a href="{{ route('library.index', array_merge(request()->except('tags', 'page'), ['q' => request('q')])) }}"
           class="btn btn-sm {{ empty($activeTags) ? 'btn-primary' : 'btn-outline-secondary' }}"
           style="font-size:.78rem;padding:.2em .6em;">
            Tous
        </a>

        @foreach ($libraryTags as $tag)
            @php
                $isActive = in_array((string)$tag->id, array_map('strval', $activeTags));
                if ($isActive) {
                    $newTags = array_values(array_filter($activeTags, fn($t) => (string)$t !== (string)$tag->id));
                } else {
                    $newTags = array_merge($activeTags, [$tag->id]);
                }
                $params = array_merge(request()->except('tags', 'page'), ['q' => request('q')]);
                if (!empty($newTags)) $params['tags'] = $newTags;
            @endphp
            <a href="{{ route('library.index', $params) }}"
               class="btn btn-sm"
               style="font-size:.78rem;padding:.2em .6em;
                      background-color:{{ $isActive ? ($tag->color ?? '#6b7280') : 'transparent' }};
                      color:{{ $isActive ? '#fff' : ($tag->color ?? 'var(--text-muted)') }};
                      border:1px solid {{ $tag->color ?? '#6b7280' }};">
                {{ $tag->name }}
            </a>
        @endforeach
    </div>
</div>
@endif
