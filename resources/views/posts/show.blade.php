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
                    <div class="d-flex gap-2 flex-wrap align-items-center" id="reactions-bar">
                        @foreach(['👍','❤️','😂','😮','😢','🔥'] as $emoji)
                            @php $count = $reactions[$emoji] ?? 0; $active = in_array($emoji, $userReactions); @endphp
                            <button type="button"
                                    class="btn btn-sm reaction-btn {{ $active ? 'btn-primary' : 'btn-outline-secondary' }}"
                                    data-emoji="{{ $emoji }}"
                                    data-url="{{ route('posts.react', $post) }}"
                                    {{ Auth::check() ? '' : 'disabled' }}>
                                {{ $emoji }} <span class="reaction-count">{{ $count > 0 ? $count : '' }}</span>
                            </button>
                        @endforeach

                        {{-- Bouton emoji-picker-element --}}
                        <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
                        @auth
                        <div class="position-relative" id="emoji-picker-wrapper">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="emoji-more-btn" title="Plus de réactions">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                            <div id="emoji-picker-container" class="position-absolute d-none"
                                 style="bottom:110%;left:0;z-index:200;">
                                <emoji-picker id="the-picker"></emoji-picker>
                            </div>
                        </div>
                        @endauth
                    </div>
                    @guest
                        <small class="text-muted mt-2 d-block">Connectez-vous pour réagir.</small>
                    @endguest
                </div>
            </div>

            <script>
            var reactUrl = "{{ route('posts.react', $post) }}";
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            function sendReaction(emoji, btnEl) {
                fetch(reactUrl, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body: JSON.stringify({emoji: emoji})
                })
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    // Cherche le bouton existant dans la barre principale
                    var existing = document.querySelector('#reactions-bar .reaction-btn[data-emoji="' + emoji + '"]');
                    if (existing && existing !== btnEl) {
                        existing.classList.toggle('btn-primary', data.active);
                        existing.classList.toggle('btn-outline-secondary', !data.active);
                        existing.querySelector('.reaction-count').textContent = data.count > 0 ? data.count : '';
                    } else if (!existing) {
                        // Ajoute un nouveau bouton dans la barre si emoji inexistant
                        var bar = document.getElementById('reactions-bar');
                        var newBtn = document.createElement('button');
                        newBtn.type = 'button';
                        newBtn.className = 'btn btn-sm reaction-btn ' + (data.active ? 'btn-primary' : 'btn-outline-secondary');
                        newBtn.dataset.emoji = emoji;
                        newBtn.dataset.url = reactUrl;
                        newBtn.innerHTML = emoji + ' <span class="reaction-count">' + (data.count > 0 ? data.count : '') + '</span>';
                        newBtn.addEventListener('click', function(){ sendReaction(this.dataset.emoji, this); });
                        bar.insertBefore(newBtn, document.getElementById('emoji-more-btn').parentElement);
                    } else {
                        btnEl.classList.toggle('btn-primary', data.active);
                        btnEl.classList.toggle('btn-outline-secondary', !data.active);
                        var countEl = btnEl.querySelector('.reaction-count');
                        if (countEl) countEl.textContent = data.count > 0 ? data.count : '';
                    }
                    if (document.getElementById('emoji-picker-container')) document.getElementById('emoji-picker-container').classList.add('d-none');
                });
            }

            document.querySelectorAll('.reaction-btn').forEach(function(btn) {
                btn.addEventListener('click', function() { sendReaction(this.dataset.emoji, this); });
            });

            var moreBtn = document.getElementById('emoji-more-btn');
            var pickerContainer = document.getElementById('emoji-picker-container');
            var picker = document.getElementById('the-picker');
            if (moreBtn && picker) {
                moreBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    pickerContainer.classList.toggle('d-none');
                });
                picker.addEventListener('emoji-click', function(e) {
                    sendReaction(e.detail.unicode, null);
                    pickerContainer.classList.add('d-none');
                });
                document.addEventListener('click', function() { pickerContainer.classList.add('d-none'); });
                pickerContainer.addEventListener('click', function(e) { e.stopPropagation(); });
            }
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
                            {{-- Comment header --}}
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong style="font-size:.875rem;">{{ $comment->user->name }}</strong>
                                <div class="d-flex align-items-center gap-2">
                                    <small style="color:var(--text-muted);">
                                        {{ $comment->created_at->format('d/m/Y H:i') }}
                                        @if($comment->edited_at)
                                            · <em style="font-size:.75rem;">modifié</em>
                                        @endif
                                    </small>
                                    @auth
                                        @if(Auth::id() === $comment->user_id && !$comment->is_deleted)
                                            <button type="button" class="btn btn-link p-0 edit-comment-btn"
                                                    data-id="{{ $comment->id }}"
                                                    data-content="{{ $comment->content }}"
                                                    data-url="{{ route('posts.comment.update', $comment) }}"
                                                    title="Modifier" style="font-size:.8rem;color:var(--text-muted);">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('posts.comment.delete', $comment) }}" class="d-inline"
                                                  onsubmit="return confirm('Supprimer ce commentaire ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-link p-0" title="Supprimer"
                                                        style="font-size:.8rem;color:var(--text-muted);">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @elseif(Auth::id() !== $comment->user_id && !$comment->is_deleted)
                                            <button type="button" class="btn btn-link p-0 report-btn"
                                                    data-url="{{ route('posts.comment.report', $comment) }}"
                                                    title="Signaler" style="font-size:.8rem;color:var(--text-muted);">
                                                <i class="bi bi-flag"></i>
                                            </button>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                            {{-- Comment content --}}
                            @if($comment->is_deleted)
                                <p style="font-size:.875rem;margin:0;color:var(--text-muted);font-style:italic;">[Commentaire supprimé]</p>
                            @else
                                <p style="font-size:.875rem;margin:0;" id="comment-text-{{ $comment->id }}">{{ $comment->content }}</p>
                            @endif

                            {{-- Replies --}}
                            @if($comment->replies->isNotEmpty())
                                <div class="mt-2 ps-3" style="border-left:2px solid var(--card-border);">
                                    @foreach($comment->replies as $reply)
                                        <div class="mb-2 pt-2 {{ !$loop->last ? 'border-bottom pb-2' : '' }}" style="border-color:var(--card-border)!important;">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <strong style="font-size:.8rem;">{{ $reply->user->name }}</strong>
                                                <div class="d-flex align-items-center gap-2">
                                                    <small style="color:var(--text-muted);font-size:.75rem;">
                                                        {{ $reply->created_at->format('d/m/Y H:i') }}
                                                        @if($reply->edited_at) · <em>modifié</em> @endif
                                                    </small>
                                                    @auth
                                                        @if(Auth::id() === $reply->user_id && !$reply->is_deleted)
                                                            <button type="button" class="btn btn-link p-0 edit-comment-btn"
                                                                    data-id="{{ $reply->id }}"
                                                                    data-content="{{ $reply->content }}"
                                                                    data-url="{{ route('posts.comment.update', $reply) }}"
                                                                    title="Modifier" style="font-size:.75rem;color:var(--text-muted);">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <form method="POST" action="{{ route('posts.comment.delete', $reply) }}" class="d-inline"
                                                                  onsubmit="return confirm('Supprimer ?')">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="btn btn-link p-0"
                                                                        style="font-size:.75rem;color:var(--text-muted);">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        @elseif(Auth::id() !== $reply->user_id && !$reply->is_deleted)
                                                            <button type="button" class="btn btn-link p-0 report-btn"
                                                                    data-url="{{ route('posts.comment.report', $reply) }}"
                                                                    style="font-size:.75rem;color:var(--text-muted);">
                                                                <i class="bi bi-flag"></i>
                                                            </button>
                                                        @endif
                                                    @endauth
                                                </div>
                                            </div>
                                            @if($reply->is_deleted)
                                                <p style="font-size:.8rem;margin:0;color:var(--text-muted);font-style:italic;">[Réponse supprimée]</p>
                                            @else
                                                <p style="font-size:.8rem;margin:0;">{{ $reply->content }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Reply button + form --}}
                            @auth
                                @if(!$comment->is_deleted)
                                    <button type="button" class="btn btn-link p-0 mt-1 toggle-reply-btn"
                                            data-target="reply-form-{{ $comment->id }}"
                                            style="font-size:.8rem;color:var(--text-muted);">
                                        <i class="bi bi-reply me-1"></i>Répondre
                                    </button>
                                    <div id="reply-form-{{ $comment->id }}" class="d-none mt-2">
                                        <form method="POST" action="{{ route('posts.reply', $post) }}">
                                            @csrf
                                            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                            <div class="d-flex gap-2">
                                                <input type="text" name="content" class="form-control form-control-sm"
                                                       placeholder="Votre réponse..." required maxlength="1000">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif
                            @endauth
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

            <script>
            document.querySelectorAll('.report-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var url = this.dataset.url;
                    var el = this;
                    fetch(url, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json'}
                    })
                    .then(function(r){ return r.json(); })
                    .then(function() {
                        el.innerHTML = '<i class="bi bi-flag-fill text-danger"></i>';
                        el.disabled = true;
                        el.title = 'Signalement envoyé';
                    });
                });
            });

            document.querySelectorAll('.toggle-reply-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var target = document.getElementById(this.dataset.target);
                    if (target) target.classList.toggle('d-none');
                });
            });
            </script>
        @endif
    </div>

    {{-- Modale modification commentaire --}}
    <div class="modal fade" id="editCommentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--card-bg);color:var(--text-primary);">
                <form method="POST" id="editCommentForm">
                    @csrf @method('PATCH')
                    <div class="modal-header" style="border-color:var(--card-border);">
                        <h6 class="modal-title">Modifier le commentaire</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <textarea name="content" id="editCommentContent" class="form-control" rows="4" required maxlength="1000"></textarea>
                    </div>
                    <div class="modal-footer" style="border-color:var(--card-border);">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.edit-comment-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editCommentContent').value = this.dataset.content;
            document.getElementById('editCommentForm').action = this.dataset.url;
            new bootstrap.Modal(document.getElementById('editCommentModal')).show();
        });
    });
    </script>
</x-app-layout>
