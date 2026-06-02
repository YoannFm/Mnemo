<x-app-layout>
    <x-slot name="pageTitle">Question {{ $question['current'] }} / {{ $question['total'] }} — {{ $module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- Progression --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:.85rem;">
                    <span style="color:var(--text-muted);">Progression</span>
                    <span style="font-weight:600;">{{ $question['current'] }} / {{ $question['total'] }}</span>
                </div>
                <div class="progress" style="height:8px;background:var(--card-border);">
                    <div class="progress-bar" role="progressbar"
                         style="width:{{ ($question['current'] / $question['total']) * 100 }}%;background:var(--accent);">
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
                    @if ($question['field_question'] === 'photo_path')
                        {{-- Question = Photo --}}
                        <div style="text-align:center;margin:1.5rem 0;">
                            <img src="{{ $question['question_content'] }}"
                                 alt="Question"
                                 style="max-width:100%;max-height:300px;border-radius:12px;object-fit:cover;">
                        </div>
                    @else
                        {{-- Question = Texte --}}
                        <div class="card" style="background:#0f1117;border:1px solid var(--card-border);margin:1.5rem 0;">
                            <div class="card-body p-3">
                                <p style="margin:0;font-size:.95rem;">{{ $question['question_content'] }}</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Formulaire des réponses --}}
            <form method="POST" action="{{ route('test.submit', $module) }}">
                @csrf

                {{-- Options (boutons radio) --}}
                <div class="mb-4">
                    @foreach ($question['options'] as $index => $option)
                        <div class="form-check mb-3">
                            <input type="radio"
                                   id="option_{{ $index }}"
                                   name="answer"
                                   value="{{ $index }}"
                                   class="form-check-input"
                                   required>
                            <label for="option_{{ $index }}" class="form-check-label" style="cursor:pointer;font-size:.95rem;display:flex;align-items:center;">
                                {{-- Affichage basique ou photo selon le type de réponse --}}
                                @if ($question['field_answer'] === 'photo_path')
                                    <img src="{{ $option }}"
                                         alt="Option {{ $index + 1 }}"
                                         style="width:80px;height:80px;border-radius:8px;object-fit:cover;margin-right:1rem;flex-shrink:0;">
                                @else
                                    <span>{{ $option }}</span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>

                {{-- Bouton de soumission --}}
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-lg me-1"></i>
                    @if ($question['current'] < $question['total'])
                        Question suivante
                    @else
                        Terminer le test
                    @endif
                </button>

            </form>

        </div>
    </div>

</x-app-layout>
