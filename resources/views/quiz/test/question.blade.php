<x-app-layout>
    <x-slot name="pageTitle">Question {{ $question['current'] }} / {{ $question['total'] }} - {{ $module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- Barre de progression - affiche visuellement l'avancement du test --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:.85rem;">
                    <span style="color:var(--text-muted);">Progression</span>
                    <span style="font-weight:600;">{{ $question['current'] }} / {{ $question['total'] }}</span>
                </div>
                {{-- Barre de progression visuelle avec pourcentage calculé --}}
                <div class="progress" style="height:8px;background:var(--card-border);">
                    <div class="progress-bar" role="progressbar"
                         style="width:{{ ($question['current'] / $question['total']) * 100 }}%;background:var(--accent);">
                    </div>
                </div>
            </div>

            {{-- Carte de la question - contient l'énoncé et le contenu à répondre --}}
            <div class="card mb-4">
                <div class="card-body p-4">

                    {{-- Type de question (Q1-Q4) - aide l'utilisateur à comprendre la difficulté --}}
                    <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem;">
                        <i class="bi bi-tag me-1"></i>{{ $question['question_type'] }}
                    </div>

                    {{-- Énoncé de la question - explique ce qu'il faut faire --}}
                    <h6 class="mb-3" style="font-size:1.1rem;">{{ $question['question_text'] }}</h6>

                    {{-- Affichage du contenu de la question selon le type (photo ou texte) --}}
                    @if ($question['field_question'] === 'photo_path')
                        {{-- Si c'est une photo, l'afficher avec dimensions limitées --}}
                        <div style="text-align:center;margin:1.5rem 0;">
                            <img src="{{ $question['question_content'] }}"
                                 alt="Question"
                                 style="max-width:100%;max-height:300px;border-radius:12px;object-fit:cover;">
                        </div>
                    @else
                        {{-- Si c'est du texte (fonction/description), l'afficher dans une boîte stylisée --}}
                        <div class="card" style="background:#0f1117;border:1px solid var(--card-border);margin:1.5rem 0;">
                            <div class="card-body p-3">
                                <p style="margin:0;font-size:.95rem;">{{ $question['question_content'] }}</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Formulaire des réponses - les 4 options en grille 2x2 pour faciliter le choix --}}
            <form method="POST" action="{{ route('test.submit', $module) }}">
                @csrf

                {{-- Grille de 4 boutons (2x2) - l'utilisateur clique sur une option --}}
                <div class="mb-4" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                    @foreach ($question['options'] as $index => $option)
                        {{-- Chaque option est un bouton radio stylisé en carte cliquable --}}
                        <label for="option_{{ $index }}"
                               style="cursor:pointer;background:var(--card-bg);border:2px solid var(--card-border);
                                      border-radius:12px;padding:.75rem;display:flex;flex-direction:column;
                                      align-items:center;justify-content:center;gap:.5rem;min-height:80px;
                                      transition:.2s;"
                               onclick="this.style.borderColor='var(--accent)'">

                            {{-- Input radio caché (la couleur du label indique la sélection) --}}
                            <input type="radio"
                                   id="option_{{ $index }}"
                                   name="answer"
                                   value="{{ $index }}"
                                   class="form-check-input"
                                   style="display:none;"
                                   required>

                            {{-- Affichage de l'option selon son type (photo ou texte) --}}
                            @if ($question['field_answer'] === 'photo_path')
                                {{-- Option = photo (ex: Q3 - la photo correspond à la description) --}}
                                <img src="{{ $option }}"
                                     alt="Option {{ $index + 1 }}"
                                     style="width:100%;height:120px;border-radius:8px;object-fit:cover;">
                            @else
                                {{-- Option = texte (ex: Q1 ou Q4 - le nom français ou anglais) --}}
                                <span style="font-size:.9rem;text-align:center;color:var(--text-primary);word-break:break-word;">
                                    {{ $option }}
                                </span>
                            @endif

                        </label>
                    @endforeach
                </div>

                {{-- Bouton de validation - texte change selon si c'est la dernière question --}}
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-lg me-1"></i>
                    @if ($question['current'] < $question['total'])
                        {{-- Affiche "Question suivante" si ce n'est pas la dernière --}}
                        Question suivante
                    @else
                        {{-- Affiche "Terminer le test" si c'est la dernière question --}}
                        Terminer le test
                    @endif
                </button>

            </form>

        </div>
    </div>

</x-app-layout>
