<x-quiz-layout>
    <x-slot name="pageTitle">Anki - {{ $module->title }}</x-slot>

    {{-- En-tête --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
        <span style="font-weight:700;font-size:1.1rem;">{{ $module->title }}</span>
        <form method="POST" action="{{ route('anki.quit', $module) }}" style="margin:0;">
            @csrf
            <button type="submit" class="btn btn-sm" style="color:var(--text-muted);border:1px solid var(--card-border);">
                <i class="bi bi-x-lg me-1"></i>Quitter
            </button>
        </form>
    </div>

    @if(isset($question['learn_total']) && $question['learn_total'] > 0)
        @php $done = $question['learn_total'] - $question['learn_remaining']; $pct = round(($done / $question['learn_total']) * 100); @endphp
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1" style="font-size:.78rem;color:var(--text-muted);">
                <span><i class="bi bi-mortarboard me-1"></i>Mode apprentissage</span>
                <span>{{ $done }} / {{ $question['learn_total'] }}</span>
            </div>
            <div class="progress" style="height:6px;">
                <div class="progress-bar" style="width:{{ $pct }}%;background:var(--accent);"></div>
            </div>
        </div>
    @elseif(isset($question['total_count']) && $question['total_count'] > 0)
        @php $pct = round($question['learned_count'] / $question['total_count'] * 100); @endphp
        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1" style="font-size:.78rem;color:var(--text-muted);">
                <span><i class="bi bi-check-circle me-1"></i>Maîtrisés</span>
                <span>{{ $question['learned_count'] }} / {{ $question['total_count'] }}</span>
            </div>
            <div class="progress" style="height:6px;">
                <div class="progress-bar" style="width:{{ $pct }}%;background:#22c55e;"></div>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="card mb-4" style="background:var(--accent-light);border:none;">
        <div class="card-body p-3" style="font-size:.85rem;">
            <div class="row g-3 text-center">
                <div class="col">
                    <div id="stat-success" style="font-weight:600;color:var(--accent);">{{ $question['session']['correct'] }}</div>
                    <div style="color:var(--text-muted);">Je sais</div>
                </div>
                <div class="col">
                    <div id="stat-fail" style="font-weight:600;color:#ef4444;">{{ $question['session']['wrong'] }}</div>
                    <div style="color:var(--text-muted);">A revoir</div>
                </div>
                <div class="col">
                    <div id="stat-streak" style="font-weight:600;color:#fbbf24;">{{ $question['session']['streak'] }}</div>
                    <div style="color:var(--text-muted);">Série</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Carte flashcard --}}
    <div id="flashcard" class="card mb-4" style="cursor:pointer;min-height:220px;transition:.2s;border:2px solid var(--card-border);" onclick="revealAnswer()">
        <div class="card-body p-4 d-flex flex-column align-items:center justify-content-center" style="align-items:center;">

            {{-- Recto : question --}}
            <div id="side-question" style="width:100%;text-align:center;">
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:1rem;">
                    <i class="bi bi-question-circle me-1"></i>{{ $question['question_text'] }}
                </div>
                @if ($question['field_question'] === 'photo_path')
                    <div style="display:inline-block;position:relative;max-width:280px;width:100%;">
                        <img src="{{ $question['question_content'] }}" alt="Question"
                             style="width:100%;aspect-ratio:1/1;border-radius:12px;object-fit:cover;display:block;">
                        <button type="button" onclick="event.stopPropagation();setZoomImage('{{ $question['question_content'] }}')"
                                style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.65);border:none;border-radius:8px;padding:5px 10px;color:#fff;cursor:pointer;font-size:.85rem;">
                            <i class="bi bi-zoom-in"></i>
                        </button>
                    </div>
                @elseif ($question['field_question'] === 'audio_path')
                    <audio controls autoplay src="{{ $question['question_content'] }}"
                           style="width:100%;max-width:400px;border-radius:8px;" onclick="event.stopPropagation()"></audio>
                @else
                    <div style="font-size:1.4rem;font-weight:600;color:var(--text-primary);padding:.5rem 0;">
                        {{ $question['question_content'] }}
                    </div>
                @endif
                <div style="margin-top:1.5rem;font-size:.78rem;color:var(--text-muted);">
                    <i class="bi bi-hand-index me-1"></i>Cliquez pour révéler la réponse
                </div>
            </div>

            {{-- Verso : réponse (caché au départ) --}}
            <div id="side-answer" style="display:none;width:100%;text-align:center;">
                <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;color:var(--accent);margin-bottom:1rem;">
                    <i class="bi bi-lightbulb me-1"></i>Réponse
                </div>
                @if ($question['field_answer'] === 'photo_path')
                    <div style="display:inline-block;position:relative;max-width:280px;width:100%;">
                        <img src="{{ $question['correct_answer'] }}" alt="Réponse"
                             style="width:100%;aspect-ratio:1/1;border-radius:12px;object-fit:cover;display:block;">
                        <button type="button" onclick="event.stopPropagation();setZoomImage('{{ $question['correct_answer'] }}')"
                                style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.65);border:none;border-radius:8px;padding:5px 10px;color:#fff;cursor:pointer;font-size:.85rem;">
                            <i class="bi bi-zoom-in"></i>
                        </button>
                    </div>
                @elseif ($question['field_answer'] === 'audio_path')
                    <audio controls autoplay src="{{ $question['correct_answer'] }}"
                           style="width:100%;max-width:400px;border-radius:8px;" onclick="event.stopPropagation()"></audio>
                @else
                    <div style="font-size:1.4rem;font-weight:600;color:var(--accent);padding:.5rem 0;">
                        {{ $question['correct_answer'] }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- Boutons Je sais / A revoir (cachés jusqu'au reveal) --}}
    <div id="action-buttons" style="display:none;gap:.75rem;" class="mb-4">
        <button type="button" class="btn flex-fill py-3" onclick="submitAnswer(false)"
                style="background:rgba(239,68,68,.12);border:2px solid #ef4444;color:#ef4444;font-weight:600;font-size:1rem;border-radius:12px;">
            <i class="bi bi-arrow-repeat me-1"></i> A revoir
        </button>
        <button type="button" class="btn flex-fill py-3" onclick="submitAnswer(true)"
                style="background:rgba(34,197,94,.12);border:2px solid #22c55e;color:#22c55e;font-weight:600;font-size:1rem;border-radius:12px;">
            <i class="bi bi-check-lg me-1"></i> Je sais
        </button>
    </div>

    <div id="loading" style="display:none;text-align:center;color:var(--text-muted);">
        <i class="bi bi-hourglass-split" style="font-size:1.5rem;"></i>
        <p>Chargement…</p>
    </div>

    {{-- Zoom modal --}}
    <div id="zoom-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;align-items:center;justify-content:center;" onclick="this.style.display='none'">
        <img id="zoom-img" src="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:10px;">
        <button onclick="document.getElementById('zoom-modal').style.display='none'" style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.15);border:none;border-radius:50%;width:36px;height:36px;color:#fff;font-size:1.1rem;cursor:pointer;">×</button>
    </div>
    <script>function setZoomImage(src){document.getElementById('zoom-img').src=src;document.getElementById('zoom-modal').style.display='flex';}</script>

    <script>
        var revealed = false;

        function revealAnswer() {
            if (revealed) return;
            revealed = true;
            document.getElementById('side-question').style.display = 'none';
            document.getElementById('side-answer').style.display   = 'block';
            document.getElementById('flashcard').style.cursor      = 'default';
            document.getElementById('flashcard').style.borderColor = 'var(--accent)';
            document.getElementById('action-buttons').style.display = 'flex';
            document.getElementById('action-buttons').style.gap = '.75rem';
        }

        function submitAnswer(knows) {
            document.getElementById('action-buttons').querySelectorAll('button').forEach(b => b.disabled = true);
            document.getElementById('loading').style.display = 'block';
            fetch("{{ route('anki.submit', $module) }}", {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({item_id:{{ $question['item_id'] }}, knows: knows, question:@json($question)}),
            })
            .then(r => r.ok ? r.json() : r.text().then(t => { throw new Error(t); }))
            .then(data => {
                if (data.session_correct !== undefined) document.getElementById('stat-success').textContent = data.session_correct;
                if (data.session_wrong   !== undefined) document.getElementById('stat-fail').textContent    = data.session_wrong;
                if (data.session_streak  !== undefined) document.getElementById('stat-streak').textContent  = data.session_streak;
                window.location.href = "{{ route('anki.question', $module) }}";
            })
            .catch(() => { alert('Erreur. Réessayez.'); document.getElementById('loading').style.display = 'none'; document.getElementById('action-buttons').querySelectorAll('button').forEach(b => b.disabled = false); });
        }

        @if(\App\Models\Setting::get('feature_keyboard_shortcuts', '1'))
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (!revealed && (e.key === ' ' || e.key === 'Enter')) { e.preventDefault(); revealAnswer(); return; }
            if (revealed) {
                if (e.key === '1' || e.key === '&') { e.preventDefault(); submitAnswer(false); }
                if (e.key === '2' || e.key === 'é') { e.preventDefault(); submitAnswer(true); }
            }
        });
        @endif
    </script>
</x-quiz-layout>
