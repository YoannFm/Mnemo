<x-app-layout>
    <x-slot name="pageTitle">Mode Anki - {{ $module->title }}</x-slot>

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
                    <p style="font-size:.8rem;color:var(--text-muted);margin:0;">Mode Anki - Questions infinies</p>
                </div>
                <form method="POST" action="{{ route('anki.quit', $module) }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="color:var(--text-muted);border:1px solid var(--card-border);">
                        <i class="bi bi-x-lg me-1"></i>Quitter
                    </button>
                </form>
            </div>

            @if(isset($question['learn_total']) && $question['learn_total'] > 0)
                @php
                    $done = $question['learn_total'] - $question['learn_remaining'];
                    $pct = round(($done / $question['learn_total']) * 100);
                @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:.78rem;color:var(--text-muted);">
                        <span><i class="bi bi-mortarboard me-1"></i>Mode apprentissage</span>
                        <span>{{ $done }} / {{ $question['learn_total'] }} réussis</span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar" style="width:{{ $pct }}%;background:var(--accent);"></div>
                    </div>
                </div>
            @endif

            {{-- Barre de progression de cet item - affiche les stats personnalisées pour le mode Anki --}}
            {{-- La progression persiste entre les questions, montrant l'évolution de la maîtrise --}}
            <div class="card mb-4" style="background:var(--accent-light);border:none;">
                <div class="card-body p-3" style="font-size:.85rem;">
                    <div class="row g-3 text-center">
                        {{-- Compteur des bonnes réponses cumulées pour cet item --}}
                        <div class="col">
                            <div style="font-weight:600;color:var(--accent);">{{ $question['progress']['success_count'] }}</div>
                            <div style="color:var(--text-muted);">Bonnes réponses</div>
                        </div>

                        {{-- Compteur des mauvaises réponses cumulées pour cet item --}}
                        <div class="col">
                            <div style="font-weight:600;color:#ef4444;">{{ $question['progress']['fail_count'] }}</div>
                            <div style="color:var(--text-muted);">Mauvaises réponses</div>
                        </div>

                        {{-- Série actuelle (bonnes réponses consécutives) - réinitialisée à 0 si erreur --}}
                        <div class="col">
                            <div style="font-weight:600;color:#fbbf24;">{{ $question['progress']['streak'] }}</div>
                            <div style="color:var(--text-muted);">Série actuelle</div>
                        </div>

                        {{-- Badge "Maîtrisé" apparaît si streak >= 3 (maîtrise atteinte) --}}
                        @if ($question['progress']['is_mastered'])
                            <div class="col">
                                <div style="font-weight:600;color:var(--success-color);">✓</div>
                                <div style="color:var(--text-muted);">Maîtrisé</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Carte de la question - même structure que le test, mais répond à l'infini en Anki --}}
            <div class="card mb-4">
                <div class="card-body p-4">

                    {{-- Type de question aléatoire (Q1-Q4) - varie à chaque nouvelle question --}}
                    <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem;">
                        <i class="bi bi-tag me-1"></i>{{ $question['question_type'] }}
                    </div>

                    {{-- Énoncé de la question - explique ce qu'il faut faire --}}
                    <h6 class="mb-3" style="font-size:1.1rem;">{{ $question['question_text'] }}</h6>

                    {{-- Contenu de la question selon le type - photo ou texte --}}
                    @if ($question['field_question'] === 'photo_path')
                        {{-- Si question est une photo, l'afficher avec zoom possible --}}
                        <div style="text-align:center;margin:1.5rem 0;display:inline-block;position:relative;">
                            <img src="{{ $question['question_content'] }}"
                                 alt="Question"
                                 style="max-width:300px;width:100%;aspect-ratio:1/1;border-radius:12px;object-fit:cover;display:block;">
                            <button type="button" onclick="setZoomImage('{{ $question['question_content'] }}')"
                                    style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.65);border:none;border-radius:8px;padding:5px 10px;color:#fff;cursor:pointer;font-size:.85rem;">
                                <i class="bi bi-zoom-in"></i>
                            </button>
                        </div>
                    @else
                        {{-- Si question est du texte (fonction/description), l'afficher stylisé --}}
                        <div class="card" style="background:#0f1117;border:1px solid var(--card-border);margin:1.5rem 0;">
                            <div class="card-body p-3">
                                <p style="margin:0;font-size:.95rem;">{{ $question['question_content'] }}</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Grille d'options interactives (2x2) - clics directs sans formulaire --}}
            {{-- Chaque clic envoie la réponse via AJAX et affiche le feedback immédiatement --}}
            <div id="options-container" class="mb-4"
                 style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                @foreach ($question['options'] as $index => $option)
                    {{-- Bouton d'option cliquable - appelle submitAnswer() en JavaScript --}}
                    <button type="button"
                            class="btn anki-option"
                            data-index="{{ $index }}"
                            onclick="submitAnswer({{ $index }}, event)"
                            style="padding:.5rem;background:var(--card-bg);border:2px solid var(--card-border);
                                   color:var(--text-primary);transition:.2s;border-radius:12px;
                                   display:flex;flex-direction:column;align-items:center;
                                   justify-content:center;gap:.5rem;min-height:80px;position:relative;">

                        @if ($question['field_answer'] === 'photo_path')
                            <img src="{{ $option }}"
                                 alt="Option {{ $index + 1 }}"
                                 style="width:100%;aspect-ratio:1/1;border-radius:6px;object-fit:cover;display:block;">
                            <button type="button"
                                    onclick="event.stopPropagation();setZoomImage('{{ $option }}')"
                                    style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.6);border:none;border-radius:6px;padding:3px 7px;cursor:pointer;color:#fff;font-size:.75rem;z-index:2;">
                                <i class="bi bi-arrows-fullscreen"></i>
                            </button>
                        @else
                            {{-- Option = texte (ex: Q1 ou Q4 - répondre par le nom) --}}
                            <span style="font-size:.9rem;text-align:center;word-break:break-word;">
                                {{ $option }}
                            </span>
                        @endif

                    </button>
                @endforeach
            </div>

            {{-- Zone feedback (caché au départ) - affiche le résultat après validation --}}
            {{-- Change dynamiquement en vert (correct) ou rouge (incorrect) avec détails --}}
            <div id="feedback" style="display:none;margin-bottom:1.5rem;">
                <div id="feedback-content" class="card"></div>
                {{-- Bouton "Suivant" qui recharge une nouvelle question --}}
                <button type="button" class="btn btn-primary w-100 mt-3" onclick="nextQuestion()" id="next-btn">
                    <i class="bi bi-arrow-right me-1"></i> Question suivante
                </button>
            </div>

            {{-- Spinner de chargement (affichée pendant la génération de la prochaine question) --}}
            <div id="loading" style="display:none;text-align:center;color:var(--text-muted);">
                <i class="bi bi-hourglass-split" style="font-size:1.5rem;"></i>
                <p>Génération de la prochaine question…</p>
            </div>

        </div>
    </div>

    {{-- Modal de zoom pour les images --}}
    <div class="modal fade" id="zoomModal" tabindex="-1" aria-labelledby="zoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="background:var(--card-bg);border:1px solid var(--card-border);">
                <div class="modal-header" style="border-bottom:1px solid var(--card-border);">
                    <h5 class="modal-title" id="zoomModalLabel" style="color:var(--text-primary);">Aperçu de l'image</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="text-align:center;">
                    <img id="zoomImage" src="" alt="Zoom" style="max-width:100%;max-height:70vh;border-radius:12px;object-fit:contain;">
                </div>
            </div>
        </div>
    </div>

    <script>
        var zoomModal;
        function setZoomImage(src) {
            document.getElementById('zoomImage').src = src;
            if (!zoomModal) zoomModal = new bootstrap.Modal(document.getElementById('zoomModal'));
            zoomModal.show();
        }
    </script>

    <script>
        /**
         * Soumet la réponse au serveur en AJAX et affiche le feedback immédiatement.
         * La réponse est envoyée sans rechargement de page (fetch API).
         *
         * Processus:
         * 1. Désactiver les boutons pour éviter les double-clics
         * 2. Envoyer l'indice de l'option au serveur via POST/JSON
         * 3. Recevoir le feedback du serveur (correct/incorrect, streak, etc.)
         * 4. Afficher le feedback visuellement (couleur verte ou rouge)
         *
         * @param {number} optionIndex - Indice de l'option cliquée (0, 1, 2, ou 3)
         * @param {Event} event - L'événement click du bouton
         */
        function submitAnswer(optionIndex, event) {
            event.preventDefault();

            console.log('=== DEBUG: Soumission réponse ===');
            console.log('optionIndex:', optionIndex);
            console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]').content);

            // Désactiver tous les boutons d'option pour éviter les soumissions multiples
            // pendant que la requête est en cours
            document.querySelectorAll('.anki-option').forEach(btn => {
                btn.disabled = true;
            });

            const payload = {
                item_id: {{ $question['item_id'] }},
                answer: optionIndex,
                question: @json($question),
            };

            console.log('Payload envoyé:', JSON.stringify(payload, null, 2));

            // Envoyer la réponse au serveur via requête AJAX (POST JSON)
            // Le serveur va mettre à jour la progression et retourner le feedback
            fetch("{{ route('anki.submit', $module) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Token CSRF requis pour les requêtes POST sécurisées
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(payload),
            })
            .then(response => {
                console.log('=== DEBUG: Réponse reçue ===');
                console.log('Status:', response.status);
                console.log('OK:', response.ok);
                console.log('Headers:', {
                    'Content-Type': response.headers.get('Content-Type'),
                    'Content-Length': response.headers.get('Content-Length'),
                });

                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Erreur serveur (status ' + response.status + '):', text);
                        throw new Error('Erreur serveur: ' + response.status);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('=== DEBUG: Données JSON reçues ===');
                console.log('Data:', JSON.stringify(data, null, 2));
                // Afficher le feedback avec le résultat du serveur
                displayFeedback(data, optionIndex);
            })
            .catch(error => {
                // En cas d'erreur, afficher un message et réactiver les boutons
                console.error('=== DEBUG: Erreur dans le fetch ===');
                console.error('Error:', error);
                console.error('Stack:', error.stack);
                alert('Une erreur est survenue. Veuillez réessayer.\n\nConsole (F12) pour détails.');
                document.querySelectorAll('.anki-option').forEach(btn => {
                    btn.disabled = false;
                });
            });
        }

        /**
         * Affiche le feedback visuel après validation de la réponse.
         * Le feedback change de couleur selon la correctness:
         * - VERT si bonne réponse
         * - ROUGE si mauvaise réponse
         *
         * Infos affichées:
         * - Statut (Correct ! ou Incorrect)
         * - Bonne réponse
         * - Votre réponse (si incorrect)
         * - Streak actuel (bonnes réponses consécutives)
         * - Badge "Maîtrisé" si streak >= 3
         *
         * @param {Object} data - Réponse du serveur contenant is_correct, correct_answer, streak, etc.
         * @param {number} optionIndex - Indice de la réponse de l'utilisateur (non utilisé ici)
         */
        function displayFeedback(data, optionIndex) {
            const container = document.getElementById('feedback');
            const content = document.getElementById('feedback-content');

            // Choisir les couleurs selon le résultat
            // Vert (#22c55e) pour correct, rouge (#ef4444) pour incorrect
            const bgColor = data.is_correct ? 'rgba(34,197,94,.1)' : 'rgba(239,68,68,.1)';
            const textColor = data.is_correct ? '#22c55e' : '#ef4444';
            const icon = data.is_correct ? '✓' : '✕';

            // Construire le HTML du feedback avec les données du serveur
            let feedbackHtml = `
                <div class="card-body p-3" style="border-left:4px solid ${textColor};background:${bgColor};">
                    {{-- Titre du feedback (Correct ! ou Incorrect) --}}
                    <div style="font-weight:600;color:${textColor};margin-bottom:.5rem;">
                        <span style="font-size:1.2rem;">${icon}</span>
                        ${data.is_correct ? 'Correct !' : 'Incorrect'}
                    </div>

                    {{-- Afficher toujours la bonne réponse --}}
                    <div style="font-size:.85rem;color:var(--text-muted);">
                        <strong>Bonne réponse :</strong> ${data.correct_answer}
                    </div>

                    {{-- Si incorrect, afficher aussi la réponse de l'utilisateur --}}
                    ${!data.is_correct ? `
                        <div style="font-size:.85rem;color:var(--text-muted);">
                            <strong>Vous avez répondu :</strong> ${data.user_answer}
                        </div>
                    ` : ''}

                    {{-- Afficher le streak actuel et le statut "Maîtrisé" si applicable --}}
                    <div style="margin-top:.75rem;font-size:.8rem;color:var(--text-muted);">
                        Série : <strong>${data.streak}</strong>
                        ${data.is_mastered ? ' - <span style="color:var(--success-color);">✓ Maîtrisé !</span>' : ''}
                    </div>
                </div>
            `;

            // Injecter le HTML et afficher la zone feedback
            content.innerHTML = feedbackHtml;
            container.style.display = 'block';
        }

        /**
         * Charge la prochaine question en redirigeant vers anki.question.
         * Affiche le spinner de chargement pendant la génération de la nouvelle question.
         *
         * Processus:
         * 1. Afficher le spinner
         * 2. Masquer le feedback
         * 3. Rediriger vers anki.question (qui génère une nouvelle question aléatoire)
         * 4. La page se recharge avec la prochaine question
         */
        function nextQuestion() {
            // Afficher le spinner "Génération de la prochaine question…"
            document.getElementById('loading').style.display = 'block';
            // Masquer le feedback
            document.getElementById('feedback').style.display = 'none';
            // Redirection vers la page anki.question pour charger une nouvelle question
            // Cette route appellera le contrôleur AnkiController@question
            window.location.href = "{{ route('anki.question', $module) }}";
        }
    </script>

</x-app-layout>
