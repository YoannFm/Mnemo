<x-quiz-layout>
    <x-slot name="pageTitle">Anki - {{ $module->title }}</x-slot>

    {{-- En-tête minimal : titre module + bouton quitter --}}
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
    @endif

    {{-- Stats --}}
    <div class="card mb-4" style="background:var(--accent-light);border:none;">
        <div class="card-body p-3" style="font-size:.85rem;">
            <div class="row g-3 text-center">
                <div class="col">
                    <div id="stat-success" style="font-weight:600;color:var(--accent);">{{ $question['session']['correct'] }}</div>
                    <div style="color:var(--text-muted);">Bonnes réponses</div>
                </div>
                <div class="col">
                    <div id="stat-fail" style="font-weight:600;color:#ef4444;">{{ $question['session']['wrong'] }}</div>
                    <div style="color:var(--text-muted);">Mauvaises réponses</div>
                </div>
                <div class="col">
                    <div id="stat-streak" style="font-weight:600;color:#fbbf24;">{{ $question['session']['streak'] }}</div>
                    <div style="color:var(--text-muted);">Série actuelle</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Question --}}
    <div class="card mb-4">
        <div class="card-body p-4">
            @if ($question['field_question'] === 'photo_path')
                <div style="text-align:center;">
                    <div style="display:inline-block;position:relative;max-width:300px;width:100%;">
                        <img src="{{ $question['question_content'] }}" alt="Question" style="width:100%;aspect-ratio:1/1;border-radius:12px;object-fit:cover;display:block;">
                        <button type="button" onclick="setZoomImage('{{ $question['question_content'] }}')" style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.65);border:none;border-radius:8px;padding:5px 10px;color:#fff;cursor:pointer;font-size:.85rem;"><i class="bi bi-zoom-in"></i></button>
                    </div>
                </div>
            @else
                <div class="card" style="background:#0f1117;border:1px solid var(--card-border);">
                    <div class="card-body p-3">
                        <p style="margin:0;font-size:.95rem;">{{ $question['question_content'] }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Options --}}
    <div id="options-container" class="mb-4" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
        @foreach ($question['options'] as $index => $option)
            <button type="button" class="btn anki-option" data-index="{{ $index }}" onclick="submitAnswer({{ $index }}, event)"
                    style="padding:.5rem;background:var(--card-bg);border:2px solid var(--card-border);color:var(--text-primary);transition:.2s;border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;min-height:80px;position:relative;">
                @if ($question['field_answer'] === 'photo_path')
                    <img src="{{ $option }}" alt="Option {{ $index + 1 }}" style="width:100%;aspect-ratio:1/1;border-radius:6px;object-fit:cover;display:block;">
                    <span onclick="event.stopPropagation();setZoomImage('{{ $option }}')" style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.7);border-radius:6px;padding:4px 8px;cursor:pointer;color:#fff;font-size:.8rem;z-index:10;line-height:1;"><i class="bi bi-zoom-in"></i></span>
                @else
                    <span style="font-size:.9rem;text-align:center;word-break:break-word;">{{ $option }}</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Feedback --}}
    <div id="feedback" style="display:none;margin-bottom:1.5rem;">
        <div id="feedback-content" class="card"></div>
        <button type="button" class="btn btn-primary w-100 mt-3" onclick="nextQuestion()" id="next-btn">
            <i class="bi bi-arrow-right me-1"></i> Question suivante
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
        function submitAnswer(optionIndex, event) {
            event.preventDefault();
            document.querySelectorAll('.anki-option').forEach(btn => btn.disabled = true);
            fetch("{{ route('anki.submit', $module) }}", {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({item_id:{{ $question['item_id'] }},answer:optionIndex,question:@json($question)}),
            })
            .then(r => r.ok ? r.json() : r.text().then(t => { throw new Error(t); }))
            .then(data => displayFeedback(data, optionIndex))
            .catch(() => { alert('Erreur. Réessayez.'); document.querySelectorAll('.anki-option').forEach(btn => btn.disabled = false); });
        }

        function displayFeedback(data, optionIndex) {
            const bg = data.is_correct ? 'rgba(34,197,94,.1)' : 'rgba(239,68,68,.1)';
            const color = data.is_correct ? '#22c55e' : '#ef4444';
            document.getElementById('feedback-content').innerHTML = `
                <div class="card-body p-3" style="border-left:4px solid ${color};background:${bg};">
                    <div style="font-weight:600;color:${color};margin-bottom:.5rem;">${data.is_correct ? '✓ Correct !' : '✕ Incorrect'}</div>
                    <div style="font-size:.85rem;color:var(--text-muted);"><strong>Bonne réponse :</strong> ${data.correct_answer}</div>
                    ${!data.is_correct ? `<div style="font-size:.85rem;color:var(--text-muted);"><strong>Vous avez répondu :</strong> ${data.user_answer}</div>` : ''}
                    ${data.is_mastered ? '<div style="margin-top:.5rem;font-size:.8rem;color:var(--success-color);">✓ Maîtrisé !</div>' : ''}
                </div>`;
            document.getElementById('feedback').style.display = 'block';
            if (data.session_correct !== undefined) document.getElementById('stat-success').textContent = data.session_correct;
            if (data.session_wrong   !== undefined) document.getElementById('stat-fail').textContent    = data.session_wrong;
            if (data.session_streak  !== undefined) document.getElementById('stat-streak').textContent  = data.session_streak;
        }

        function nextQuestion() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('feedback').style.display = 'none';
            window.location.href = "{{ route('anki.question', $module) }}";
        }

        @if(\App\Models\Setting::get('feature_keyboard_shortcuts', '1'))
        document.addEventListener('keydown', function(e) {
            var feedback = document.getElementById('feedback');
            if (feedback && feedback.style.display !== 'none') {
                if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); nextQuestion(); }
                return;
            }
            var keyMap = {'1':0,'&':0,'2':1,'é':1,'"':2,'3':2,"'":3,'4':3};
            var idx = keyMap[e.key];
            if (idx !== undefined) { var btns = document.querySelectorAll('.anki-option:not([disabled])'); if (btns[idx]) btns[idx].click(); }
        });
        @endif
    </script>
</x-quiz-layout>
