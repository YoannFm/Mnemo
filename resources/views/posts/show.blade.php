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
                        @foreach($reactions as $slug => $count)
                            @php $active = in_array($slug, $userReactions); $emojiModel = \App\Models\Emoji::where('slug', $slug)->first(); @endphp
                            @if($emojiModel)
                            <button type="button"
                                    class="btn btn-sm reaction-btn d-flex align-items-center gap-1 {{ $active ? 'btn-primary' : 'btn-outline-secondary' }}"
                                    data-emoji="{{ $slug }}"
                                    {{ Auth::check() ? '' : 'disabled' }}>
                                <img src="{{ $emojiModel->imageUrl() }}" alt="{{ $emojiModel->name }}" style="width:18px;height:18px;object-fit:contain;">
                                <span class="reaction-count">{{ $count > 0 ? $count : '' }}</span>
                            </button>
                            @endif
                        @endforeach

                        @auth
                        <div class="position-relative" id="emoji-picker-wrapper">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="emoji-more-btn" title="Réagir">
                                <i class="bi bi-emoji-smile"></i>
                            </button>
                            <div id="emoji-picker-container" class="position-absolute d-none"
                                 style="bottom:110%;right:0;z-index:200;background:var(--card-bg);border:1px solid var(--card-border);border-radius:10px;padding:12px;width:min(320px, calc(100vw - 1.5rem));box-shadow:0 8px 24px rgba(0,0,0,.2);">
                                <input type="search" id="emoji-search" class="form-control form-control-sm mb-2" placeholder="Rechercher...">
                                <div id="emoji-recent-section" class="d-none mb-2">
                                    <p class="text-muted mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;">Récents</p>
                                    <div id="emoji-recent-grid" style="display:grid;grid-template-columns:repeat(6,1fr);gap:6px;"></div>
                                    <hr class="my-2" style="border-color:var(--card-border);">
                                </div>
                                <div id="emoji-grid" style="display:grid;grid-template-columns:repeat(6,1fr);gap:6px;max-height:200px;overflow-y:auto;"></div>
                                <p id="emoji-empty" class="text-muted text-center small mt-2 d-none">Aucun emoji.</p>
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
            var emojisUrl = "{{ route('emojis.json') }}";
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            var allEmojis = [];
            var RECENT_KEY = 'emoji_recent';
            var MAX_RECENT = 12;

            function getRecent() {
                try { return JSON.parse(localStorage.getItem(RECENT_KEY) || '[]'); } catch(e) { return []; }
            }
            function addRecent(slug) {
                var list = getRecent().filter(s => s !== slug);
                list.unshift(slug);
                localStorage.setItem(RECENT_KEY, JSON.stringify(list.slice(0, MAX_RECENT)));
            }
            function renderRecentSection() {
                var recent = getRecent();
                var section = document.getElementById('emoji-recent-section');
                var grid = document.getElementById('emoji-recent-grid');
                var matching = recent.map(s => allEmojis.find(e => e.slug === s)).filter(Boolean);
                if (!matching.length) { section.classList.add('d-none'); return; }
                section.classList.remove('d-none');
                grid.innerHTML = '';
                matching.forEach(e => grid.appendChild(makeEmojiBtn(e)));
            }
            function makeEmojiBtn(e) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.title = e.name;
                btn.style = 'background:none;border:1px solid transparent;border-radius:6px;padding:4px;cursor:pointer;transition:.15s;';
                btn.innerHTML = '<img src="' + e.url + '" alt="' + e.name + '" style="width:32px;height:32px;object-fit:contain;">';
                btn.addEventListener('mouseenter', () => btn.style.borderColor = 'var(--card-border)');
                btn.addEventListener('mouseleave', () => btn.style.borderColor = 'transparent');
                btn.addEventListener('click', () => { addRecent(e.slug); sendReaction(e.slug, e.url, e.name); });
                return btn;
            }

            function sendReaction(slug, imgUrl, imgAlt) {
                if (pickerContainer) pickerContainer.classList.add('d-none');
                fetch(reactUrl, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body: JSON.stringify({emoji: slug})
                })
                .then(r => {
                    if (!r.ok) return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + t); });
                    return r.json();
                })
                .then(data => {
                    if (data.status === 'muted') { showMuteAlert(data.message); return; }
                    if (data.active) addRecent(slug);
                    var existing = document.querySelector('#reactions-bar .reaction-btn[data-emoji="' + slug + '"]');
                    if (existing) {
                        existing.classList.toggle('btn-primary', data.active);
                        existing.classList.toggle('btn-outline-secondary', !data.active);
                        existing.querySelector('.reaction-count').textContent = data.count > 0 ? data.count : '';
                        if (!data.active && data.count === 0) existing.remove();
                    } else if (data.active) {
                        var bar = document.getElementById('reactions-bar');
                        var newBtn = document.createElement('button');
                        newBtn.type = 'button';
                        newBtn.className = 'btn btn-sm reaction-btn d-flex align-items-center gap-1 btn-primary';
                        newBtn.dataset.emoji = slug;
                        newBtn.innerHTML = '<img src="' + imgUrl + '" alt="' + imgAlt + '" style="width:18px;height:18px;object-fit:contain;"> <span class="reaction-count">' + (data.count > 0 ? data.count : '') + '</span>';
                        newBtn.addEventListener('click', function(){ sendReaction(this.dataset.emoji, imgUrl, imgAlt); });
                        bar.insertBefore(newBtn, document.getElementById('emoji-picker-wrapper'));
                    }
                })
                .catch(err => {
                    console.error('Erreur reaction:', err);
                    var toast = document.createElement('div');
                    toast.className = 'alert alert-danger position-fixed bottom-0 end-0 m-3';
                    toast.style = 'z-index:9999;font-size:.85rem;';
                    toast.textContent = 'Erreur : ' + err.message;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 5000);
                });
            }

            document.querySelectorAll('.reaction-btn').forEach(btn => {
                btn.addEventListener('click', function(){
                    var img = this.querySelector('img');
                    sendReaction(this.dataset.emoji, img ? img.src : '', img ? img.alt : '');
                });
            });

            function renderEmojiGrid(emojis) {
                var grid = document.getElementById('emoji-grid');
                var empty = document.getElementById('emoji-empty');
                grid.innerHTML = '';
                if (!emojis.length) { empty.classList.remove('d-none'); return; }
                empty.classList.add('d-none');
                emojis.forEach(e => grid.appendChild(makeEmojiBtn(e)));
            }

            function filterAndRender() {
                var q = (document.getElementById('emoji-search').value || '').toLowerCase();
                var filtered = allEmojis.filter(e => !q || e.name.toLowerCase().includes(q) || e.slug.toLowerCase().includes(q));
                renderRecentSection();
                renderEmojiGrid(filtered);
            }

            var moreBtn = document.getElementById('emoji-more-btn');
            var pickerContainer = document.getElementById('emoji-picker-container');
            var loaded = false;

            if (moreBtn && pickerContainer) {
                moreBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    pickerContainer.classList.toggle('d-none');
                    if (!loaded) {
                        loaded = true;
                        fetch(emojisUrl).then(r => r.json()).then(data => {
                            allEmojis = data;
                            filterAndRender();
                        });
                    }
                });
                document.addEventListener('click', () => pickerContainer.classList.add('d-none'));
                pickerContainer.addEventListener('click', e => e.stopPropagation());

                document.getElementById('emoji-search').addEventListener('input', filterAndRender);
            }
            </script>
        @endif

        {{-- Commentaires --}}
        @if ($post->allow_comments)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0" id="comments-count-header">Commentaires ({{ $comments->filter(fn($c) => !$c->is_deleted)->count() }})</h6>
                </div>
                <div class="card-body" id="comments-list">
                    @forelse($comments->filter(fn($c) => !$c->is_deleted) as $comment)
                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--card-border)!important;" id="comment-{{ $comment->id }}">
                            {{-- Comment header --}}
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong style="font-size:.875rem;">{{ $comment->user->name }}</strong>
                                <div class="d-flex align-items-center gap-2">
                                    <small style="color:var(--text-muted);" id="comment-meta-{{ $comment->id }}">
                                        {{ $comment->created_at->format('d/m/Y H:i') }}
                                        @if($comment->edited_at)
                                            · <em style="font-size:.75rem;">modifié</em>
                                        @endif
                                    </small>
                                    @auth
                                        @if((Auth::id() === $comment->user_id || Auth::user()?->is_admin) && !$comment->is_deleted)
                                            <button type="button" class="btn btn-link p-0 edit-comment-btn"
                                                    data-id="{{ $comment->id }}"
                                                    data-content="{{ $comment->content }}"
                                                    data-url="{{ route('posts.comment.update', $comment) }}"
                                                    title="Modifier" style="font-size:.8rem;color:var(--text-muted);">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="{{ route('posts.comment.delete', $comment) }}" class="d-inline delete-comment-form"
                                                  data-comment-id="{{ $comment->id }}"
                                                  onsubmit="return handleDeleteComment(event, this)">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-link p-0" title="Supprimer"
                                                        style="font-size:.8rem;color:var(--text-muted);">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @elseif(Auth::id() !== $comment->user_id && !Auth::user()?->is_admin && !$comment->is_deleted)
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
                                <p style="font-size:.875rem;margin:0;color:var(--text-muted);font-style:italic;" id="comment-text-{{ $comment->id }}">[Commentaire supprimé]</p>
                            @else
                                <p style="font-size:.875rem;margin:0;" id="comment-text-{{ $comment->id }}">{{ $comment->content }}</p>
                            @endif

                            {{-- Replies --}}
                            @php $visibleReplies = $comment->replies->filter(fn($r) => !$r->is_deleted); @endphp
                            <div class="mt-2 ps-3 replies-container-{{ $comment->id }}" style="{{ $visibleReplies->isNotEmpty() ? '' : 'display:none;' }} border-left:2px solid var(--card-border);">
                                @foreach($visibleReplies as $reply)
                                    <div class="mb-2 pt-2 {{ !$loop->last ? 'border-bottom pb-2' : '' }}" style="border-color:var(--card-border)!important;" id="comment-{{ $reply->id }}">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <strong style="font-size:.8rem;">{{ $reply->user->name }}</strong>
                                            <div class="d-flex align-items-center gap-2">
                                                <small style="color:var(--text-muted);font-size:.75rem;" id="comment-meta-{{ $reply->id }}">
                                                    {{ $reply->created_at->format('d/m/Y H:i') }}
                                                    @if($reply->edited_at) · <em>modifié</em> @endif
                                                </small>
                                                @auth
                                                    @if((Auth::id() === $reply->user_id || Auth::user()?->is_admin) && !$reply->is_deleted)
                                                        <button type="button" class="btn btn-link p-0 edit-comment-btn"
                                                                data-id="{{ $reply->id }}"
                                                                data-content="{{ $reply->content }}"
                                                                data-url="{{ route('posts.comment.update', $reply) }}"
                                                                title="Modifier" style="font-size:.75rem;color:var(--text-muted);">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <form method="POST" action="{{ route('posts.comment.delete', $reply) }}" class="d-inline delete-comment-form"
                                                              data-comment-id="{{ $reply->id }}"
                                                              onsubmit="return handleDeleteComment(event, this)">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-link p-0"
                                                                    style="font-size:.75rem;color:var(--text-muted);">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    @elseif(Auth::id() !== $reply->user_id && !Auth::user()?->is_admin && !$reply->is_deleted)
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
                                            <p style="font-size:.8rem;margin:0;color:var(--text-muted);font-style:italic;" id="comment-text-{{ $reply->id }}">[Réponse supprimée]</p>
                                        @else
                                            <p style="font-size:.8rem;margin:0;" id="comment-text-{{ $reply->id }}">{{ $reply->content }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Reply button + form --}}
                            @auth
                                @if(!$comment->is_deleted)
                                    <button type="button" class="btn btn-link p-0 mt-1 toggle-reply-btn"
                                            data-target="reply-form-{{ $comment->id }}"
                                            style="font-size:.8rem;color:var(--text-muted);">
                                        <i class="bi bi-reply me-1"></i>Répondre
                                    </button>
                                    <div id="reply-form-{{ $comment->id }}" class="d-none mt-2">
                                        <form class="reply-form" data-parent-id="{{ $comment->id }}" data-url="{{ route('posts.reply', $post) }}">
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
                        <p style="color:var(--text-muted);font-size:.875rem;margin:0;" id="no-comments-msg">Aucun commentaire pour l'instant.</p>
                    @endforelse
                </div>
                @auth
                    <div class="card-footer">
                        <div id="comment-success-msg" class="alert alert-success py-2 mb-3 d-none"></div>
                        <form id="new-comment-form" data-url="{{ route('posts.comment', $post) }}">
                            @csrf
                            <div class="mb-2">
                                <textarea name="content" id="new-comment-content" class="form-control"
                                          rows="3" placeholder="Votre commentaire..." required maxlength="1000"></textarea>
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

            {{-- Modal signalement --}}
            <div class="modal fade" id="reportModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content" style="background:var(--card-bg);color:var(--text-primary);">
                        <div class="modal-header" style="border-color:var(--card-border);">
                            <h6 class="modal-title">Signaler ce commentaire</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Motif</label>
                                <select class="form-select" id="reportReason">
                                    <option value="spam">Spam</option>
                                    <option value="harassment">Harcèlement</option>
                                    <option value="inappropriate">Contenu inapproprié</option>
                                    <option value="misinformation">Désinformation</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Précision (optionnel)</label>
                                <textarea class="form-control" id="reportNote" rows="2" maxlength="500" placeholder="Détails..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer" style="border-color:var(--card-border);">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn btn-danger btn-sm" id="reportSubmitBtn">Signaler</button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            var _csrf = document.querySelector('meta[name="csrf-token"]').content;

            function showMuteAlert(message) {
                var existing = document.getElementById('mute-alert-banner');
                if (existing) existing.remove();
                var el = document.createElement('div');
                el.id = 'mute-alert-banner';
                el.className = 'alert alert-warning alert-dismissible fade show';
                el.setAttribute('role', 'alert');
                el.innerHTML = '<i class="bi bi-mic-mute-fill me-2"></i>' + message +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                var commentsSection = document.getElementById('comments');
                if (commentsSection) commentsSection.prepend(el);
                el.scrollIntoView({behavior: 'smooth', block: 'center'});
            }

            // ── New comment (AJAX) ──
            var newCommentForm = document.getElementById('new-comment-form');
            if (newCommentForm) {
                newCommentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    var url = this.dataset.url;
                    var content = document.getElementById('new-comment-content').value;
                    fetch(url, {
                        method: 'POST',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':_csrf,'Accept':'application/json'},
                        body: JSON.stringify({content: content})
                    })
                    .then(function(r){ return r.json(); })
                    .then(function(data) {
                        if (data.status === 'muted') {
                            showMuteAlert(data.message);
                            return;
                        }
                        if (data.status === 'ok') {
                            var noMsg = document.getElementById('no-comments-msg');
                            if (noMsg) noMsg.remove();
                            var list = document.getElementById('comments-list');
                            var html = buildCommentHtml(data.comment);
                            list.insertAdjacentHTML('beforeend', html);
                            document.getElementById('new-comment-content').value = '';
                            updateCommentCount(1);
                        }
                    });
                });
            }

            // ── Reply forms (AJAX) ──
            document.addEventListener('submit', function(e) {
                if (!e.target.classList.contains('reply-form')) return;
                e.preventDefault();
                var form = e.target;
                var url = form.dataset.url;
                var parentId = form.dataset.parentId;
                var contentInput = form.querySelector('input[name="content"]');
                fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':_csrf,'Accept':'application/json'},
                    body: JSON.stringify({content: contentInput.value, parent_id: parentId})
                })
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    if (data.status === 'muted') {
                        showMuteAlert(data.message);
                        return;
                    }
                    if (data.status === 'ok') {
                        var container = document.querySelector('.replies-container-' + parentId);
                        if (container) {
                            container.style.display = '';
                            var html = buildReplyHtml(data.comment);
                            container.insertAdjacentHTML('beforeend', html);
                        }
                        contentInput.value = '';
                        var replyFormDiv = document.getElementById('reply-form-' + parentId);
                        if (replyFormDiv) replyFormDiv.classList.add('d-none');
                    }
                });
            });

            // ── Delete comment (AJAX) ──
            function handleDeleteComment(e, form) {
                e.preventDefault();
                if (!confirm('Supprimer ce commentaire ?')) return false;
                var action = form.action;
                var commentId = form.dataset.commentId;
                fetch(action, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':_csrf,'Accept':'application/json'},
                    body: JSON.stringify({'_method': 'DELETE'})
                })
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    if (data.status === 'ok') {
                        var commentEl = document.getElementById('comment-' + commentId);
                        if (commentEl) {
                            commentEl.remove();
                            updateCommentCount(-1);
                        }
                    }
                });
                return false;
            }

            // ── Toggle reply form ──
            document.querySelectorAll('.toggle-reply-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var target = document.getElementById(this.dataset.target);
                    if (target) target.classList.toggle('d-none');
                });
            });

            // ── Report modal ──
            var currentReportUrl = null;
            var currentReportBtn = null;

            document.querySelectorAll('.report-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    currentReportUrl = this.dataset.url;
                    currentReportBtn = this;
                    document.getElementById('reportReason').value = 'spam';
                    document.getElementById('reportNote').value = '';
                    new bootstrap.Modal(document.getElementById('reportModal')).show();
                });
            });

            document.getElementById('reportSubmitBtn').addEventListener('click', function() {
                if (!currentReportUrl) return;
                fetch(currentReportUrl, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':_csrf,'Accept':'application/json'},
                    body: JSON.stringify({
                        reason: document.getElementById('reportReason').value,
                        note: document.getElementById('reportNote').value
                    })
                })
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
                    if (currentReportBtn) {
                        currentReportBtn.innerHTML = '<i class="bi bi-flag-fill text-danger"></i>';
                        currentReportBtn.disabled = true;
                        currentReportBtn.title = 'Signalement envoyé';
                    }
                });
            });

            // ── Helper: build comment HTML ──
            function buildCommentHtml(comment) {
                return '<div class="mb-3 pb-3" style="border-color:var(--card-border)!important;" id="comment-' + comment.id + '">' +
                    '<div class="d-flex justify-content-between align-items-start mb-1">' +
                    '<strong style="font-size:.875rem;">' + escHtml(comment.user_name) + '</strong>' +
                    '<div class="d-flex align-items-center gap-2">' +
                    '<small style="color:var(--text-muted);" id="comment-meta-' + comment.id + '">' + escHtml(comment.created_at) + '</small>' +
                    '</div></div>' +
                    '<p style="font-size:.875rem;margin:0;" id="comment-text-' + comment.id + '">' + escHtml(comment.content) + '</p>' +
                    '<div class="mt-2 ps-3 replies-container-' + comment.id + '" style="display:none;border-left:2px solid var(--card-border);"></div>' +
                    '</div>';
            }

            // ── Helper: build reply HTML ──
            function buildReplyHtml(comment) {
                return '<div class="mb-2 pt-2" style="border-color:var(--card-border)!important;" id="comment-' + comment.id + '">' +
                    '<div class="d-flex justify-content-between align-items-start mb-1">' +
                    '<strong style="font-size:.8rem;">' + escHtml(comment.user_name) + '</strong>' +
                    '<div class="d-flex align-items-center gap-2">' +
                    '<small style="color:var(--text-muted);font-size:.75rem;" id="comment-meta-' + comment.id + '">' + escHtml(comment.created_at) + '</small>' +
                    '</div></div>' +
                    '<p style="font-size:.8rem;margin:0;" id="comment-text-' + comment.id + '">' + escHtml(comment.content) + '</p>' +
                    '</div>';
            }

            function escHtml(str) {
                var d = document.createElement('div');
                d.appendChild(document.createTextNode(str));
                return d.innerHTML;
            }

            function updateCommentCount(delta) {
                var header = document.getElementById('comments-count-header');
                if (!header) return;
                var match = header.textContent.match(/(\d+)/);
                if (match) {
                    var count = parseInt(match[1]) + delta;
                    header.textContent = 'Commentaires (' + count + ')';
                }
            }
            </script>
        @endif
    </div>

    {{-- Modale modification commentaire --}}
    <div class="modal fade" id="editCommentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--card-bg);color:var(--text-primary);">
                <div class="modal-header" style="border-color:var(--card-border);">
                    <h6 class="modal-title">Modifier le commentaire</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea id="editCommentContent" class="form-control" rows="4" required maxlength="1000"></textarea>
                </div>
                <div class="modal-footer" style="border-color:var(--card-border);">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary btn-sm" id="editCommentSaveBtn">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    var _editCommentUrl = null;
    var _editCommentId = null;

    document.querySelectorAll('.edit-comment-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            _editCommentUrl = this.dataset.url;
            _editCommentId = this.dataset.id;
            document.getElementById('editCommentContent').value = this.dataset.content;
            new bootstrap.Modal(document.getElementById('editCommentModal')).show();
        });
    });

    document.getElementById('editCommentSaveBtn').addEventListener('click', function() {
        if (!_editCommentUrl) return;
        var content = document.getElementById('editCommentContent').value;
        fetch(_editCommentUrl, {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
            body: JSON.stringify({content: content, '_method': 'PATCH'})
        })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (data.status === 'ok') {
                var textEl = document.getElementById('comment-text-' + _editCommentId);
                if (textEl) textEl.textContent = data.content;
                var metaEl = document.getElementById('comment-meta-' + _editCommentId);
                if (metaEl && !metaEl.querySelector('em')) {
                    metaEl.insertAdjacentHTML('beforeend', ' · <em style="font-size:.75rem;">modifié</em>');
                }
                // Update data-content on the edit btn
                var editBtn = document.querySelector('.edit-comment-btn[data-id="' + _editCommentId + '"]');
                if (editBtn) editBtn.dataset.content = data.content;
                bootstrap.Modal.getInstance(document.getElementById('editCommentModal')).hide();
            }
        });
    });
    </script>
</x-app-layout>
