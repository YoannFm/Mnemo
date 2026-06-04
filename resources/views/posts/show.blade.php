<x-app-layout>
    <x-slot name="pageTitle">{{ $post->title }}</x-slot>

    <div style="max-width:800px;margin:0 auto;padding:2rem 1rem;">
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

        <div class="card mb-4">
            <div class="card-body">
                {!! $post->content !!}
            </div>
        </div>

        {{-- Réactions --}}
        @if ($post->allow_reactions)
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap" id="reactions-bar">
                        @foreach(['👍','❤️','😂','😮','😢','🔥'] as $emoji)
                            @php
                                $count = $reactions[$emoji] ?? 0;
                                $active = in_array($emoji, $userReactions);
                            @endphp
                            <button type="button"
                                    class="btn btn-sm reaction-btn {{ $active ? 'btn-primary' : 'btn-outline-secondary' }}"
                                    data-emoji="{{ $emoji }}"
                                    data-url="{{ route('posts.react', $post) }}"
                                    {{ Auth::check() ? '' : 'disabled' }}>
                                {{ $emoji }} <span class="reaction-count">{{ $count > 0 ? $count : '' }}</span>
                            </button>
                        @endforeach
                    </div>
                    @guest
                        <small class="text-muted mt-2 d-block">Connectez-vous pour réagir.</small>
                    @endguest
                </div>
            </div>

            <script>
            document.querySelectorAll('.reaction-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var emoji = this.dataset.emoji;
                    var url = this.dataset.url;
                    var btnEl = this;
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({emoji: emoji})
                    })
                    .then(function(r){ return r.json(); })
                    .then(function(data) {
                        btnEl.classList.toggle('btn-primary', data.active);
                        btnEl.classList.toggle('btn-outline-secondary', !data.active);
                        btnEl.querySelector('.reaction-count').textContent = data.count > 0 ? data.count : '';
                    });
                });
            });
            </script>
        @endif

        {{-- Commentaires --}}
        @if ($post->allow_comments)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Commentaires ({{ $comments->count() }})</h6>
                </div>
                <div class="card-body">
                    @forelse($comments as $comment)
                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--card-border)!important;">
                            <div class="d-flex justify-content-between mb-1">
                                <strong style="font-size:.875rem;">{{ $comment->user->name }}</strong>
                                <small style="color:var(--text-muted);">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <p style="font-size:.875rem;margin:0;">{{ $comment->content }}</p>
                        </div>
                    @empty
                        <p style="color:var(--text-muted);font-size:.875rem;margin:0;">Aucun commentaire pour l'instant.</p>
                    @endforelse
                </div>
                @auth
                    <div class="card-footer">
                        @if(session('success'))
                            <div class="alert alert-success py-2 mb-3">{{ session('success') }}</div>
                        @endif
                        <form method="POST" action="{{ route('posts.comment', $post) }}">
                            @csrf
                            <div class="mb-2">
                                <textarea name="content" class="form-control @error('content') is-invalid @enderror"
                                          rows="3" placeholder="Votre commentaire..." required maxlength="1000"></textarea>
                                @error('content')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-send me-1"></i>Envoyer
                            </button>
                        </form>
                    </div>
                @else
                    <div class="card-footer">
                        <small class="text-muted">
                            <a href="{{ route('login') }}">Connectez-vous</a> pour commenter.
                        </small>
                    </div>
                @endauth
            </div>
        @endif
    </div>
</x-app-layout>
