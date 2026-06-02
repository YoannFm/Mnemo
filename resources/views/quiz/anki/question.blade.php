<x-app-layout>
    <x-slot name="pageTitle">Mode Anki — {{ $module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- Fil d'Ariane --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb" style="font-size:.85rem;">
                    <li class="breadcrumb-item">
                        <a href="{{ route('modules.index') }}" style="color:var(--accent);">Mes modules</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('modules.show', $module) }}" style="color:var(--accent);">{{ $module->title }}</a>
                    </li>
                    <li class="breadcrumb-item active" style="color:var(--text-muted);">Anki</li>
                </ol>
            </nav>

            {{-- Barre info du module --}}
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--card-border);">
                <div>
                    <h5 class="mb-0">{{ $module->title }}</h5>
                    <p style="font-size:.8rem;color:var(--text-muted);margin:0;">Mode Anki — Questions infinies</p>
                </div>
                <form method="POST" action="{{ route('anki.quit', $module) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="color:var(--text-muted);border:1px solid var(--card-border);">
                        <i class="bi bi-x-lg me-1"></i>Quitter
                    </button>
                </form>
            </div>

            {{-- Progression de l'item --}}
            <div class="card mb-4" style="background:var(--accent-light);border:none;">
                <div class="card-body p-3" style="font-size:.85rem;">
                    <div class="row g-3 text-center">
                        <div class="col">
                            <div style="font-weight:600;color:var(--accent);">{{ $question['progress']['success_count'] }}</div>
                            <div style="color:var(--text-muted);">Bonnes réponses</div>
                        </div>
                        <div class="col">
                            <div style="font-weight:600;color:#ef4444;">{{ $question['progress']['fail_count'] }}</div>
                            <div style="color:var(--text-muted);">Mauvaises réponses</div>
                        </div>
                        <div class="col">
                            <div style="font-weight:600;color:#fbbf24;">{{ $question['progress']['streak'] }}</div>
                            <div style="color:var(--text-muted);">Série actuelle</div>
                        </div>
                        @if ($question['progress']['is_mastered'])
                            <div class="col">
                                <div style="font-weight:600;color:var(--success-color);">✓</div>
                                <div style="color:var(--text-muted);">Maîtrisé</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Carte de la question --}}
            <div class="card mb-4">
                <div class="card-body p-4">

                    {{-- Type de question --}}
                    <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem;">
                        <i class="bi bi-tag me-1"></i>{{ $question['question_type'] }}
                    </div>

                    {{-- Énoncé --}}
                    <h6 class="mb-3" style="font-size:1.1rem;">{{ $question['question_text'] }}</h6>

                    {{-- Affichage de la question (photo ou texte) --}}
                    @if ($question['field_answer'] === 'photo_path')
                        <div style="text-align:center;margin:1.5rem 0;">
                            <img src="{{ $question['question_content'] }}"
                                 alt="Question"
                                 style="max-width:100%;max-height:300px;border-radius:12px;object-fit:cover;">
                        </div>
                    @else
                        <div class="card" style="background:#0f1117;border:1px solid var(--card-border);margin:1.5rem 0;">
                            <div class="card-body p-3">
                                <p style="margin:0;font-size:.95rem;">{{ $question['question_content'] }}</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Options interactives --}}
            <div id="options-container" class="mb-4">
                @foreach ($question['options'] as $index => $option)
                    <button type="button"
                            class="btn w-100 mb-2 anki-option"
                            data-index="{{ $index }}"
                            onclick="submitAnswer({{ $index }}, event)"
                            style="text-align:left;padding:1rem;background:var(--card-bg);border:1px solid var(--card-border);color:var(--text-primary);transition:.2s;">
                        {{-- Affichage basique ou photo --}}
                        @if ($question['field_answer'] === 'photo_path')
                            <img src="{{ $option }}"
                                 alt="Option {{ $index + 1 }}"
                                 style="max-width:80px;max-height:80px;border-radius:8px;object-fit:cover;margin-right:1rem;vertical-align:middle;">
                        @else
                            {{ $option }}
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Feedback (caché jusqu'à la réponse) --}}
            <div id="feedback" style="display:none;margin-bottom:1.5rem;">
                <div id="feedback-content" class="card"></div>
                <button type="button" class="btn btn-primary w-100 mt-3" onclick="nextQuestion()" id="next-btn">
                    <i class="bi bi-arrow-right me-1"></i> Question suivante
                </button>
            </div>

            {{-- Statut de chargement --}}
            <div id="loading" style="display:none;text-align:center;color:var(--text-muted);">
                <i class="bi bi-hourglass-split" style="font-size:1.5rem;"></i>
                <p>Génération de la prochaine question…</p>
            </div>

        </div>
    </div>

    <script>
        /**
         * Soumet la réponse au serveur et affiche le feedback immédiatement.
         * Utilise fetch() pour requête AJAX sans rechargement.
         *
         * @param {number} optionIndex - Indice de l'option cliquée (0-2)
         * @param {Event} event - L'événement click
         */
        function submitAnswer(optionIndex, event) {
            event.preventDefault();

            // Désactiver tous les boutons d'option
            document.querySelectorAll('.anki-option').forEach(btn => {
                btn.disabled = true;
            });

            // Envoyer la réponse au serveur
            fetch("{{ route('anki.submit', $module) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    item_id: {{ $question['item_id'] }},
                    answer: optionIndex,
                    question: @json($question),
                }),
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur serveur');
                return response.json();
            })
            .then(data => {
                displayFeedback(data, optionIndex);
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue. Veuillez réessayer.');
                document.querySelectorAll('.anki-option').forEach(btn => {
                    btn.disabled = false;
                });
            });
        }

        /**
         * Affiche le feedback visuel (vert pour correct, rouge pour incorrect).
         * Montre aussi la bonne réponse et le streak actuel.
         *
         * @param {Object} data - Réponse du serveur
         * @param {number} optionIndex - Indice de la réponse de l'utilisateur
         */
        function displayFeedback(data, optionIndex) {
            const container = document.getElementById('feedback');
            const content = document.getElementById('feedback-content');

            // Couleur du feedback
            const bgColor = data.is_correct ? 'rgba(34,197,94,.1)' : 'rgba(239,68,68,.1)';
            const textColor = data.is_correct ? '#22c55e' : '#ef4444';
            const icon = data.is_correct ? '✓' : '✕';

            // Contenu du feedback
            let feedbackHtml = `
                <div class="card-body p-3" style="border-left:4px solid ${textColor};background:${bgColor};">
                    <div style="font-weight:600;color:${textColor};margin-bottom:.5rem;">
                        <span style="font-size:1.2rem;">${icon}</span>
                        ${data.is_correct ? 'Correct !' : 'Incorrect'}
                    </div>
                    <div style="font-size:.85rem;color:var(--text-muted);">
                        <strong>Bonne réponse :</strong> ${data.correct_answer}
                    </div>
                    ${!data.is_correct ? `
                        <div style="font-size:.85rem;color:var(--text-muted);">
                            <strong>Vous avez répondu :</strong> ${data.user_answer}
                        </div>
                    ` : ''}
                    <div style="margin-top:.75rem;font-size:.8rem;color:var(--text-muted);">
                        Série : <strong>${data.streak}</strong>
                        ${data.is_mastered ? ' — <span style="color:var(--success-color);">✓ Maîtrisé !</span>' : ''}
                    </div>
                </div>
            `;

            content.innerHTML = feedbackHtml;
            container.style.display = 'block';
        }

        /**
         * Charge la prochaine question via redirection.
         * Récupère une nouvelle question du serveur.
         */
        function nextQuestion() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('feedback').style.display = 'none';
            // Redirection vers anki.question pour charger la prochaine question
            window.location.href = "{{ route('anki.question', $module) }}";
        }
    </script>

</x-app-layout>
