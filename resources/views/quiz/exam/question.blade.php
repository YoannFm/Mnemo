<x-app-layout>
    <x-slot name="pageTitle">Question {{ $question['current'] }} / {{ $question['total'] }} - {{ $module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            {{-- En-tête avec titre du module et lien quitter --}}
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--card-border);">
                <div>
                    <h5 class="mb-0">{{ $module->title }}</h5>
                    <p style="font-size:.8rem;color:var(--text-muted);margin:0;">Mode Examen</p>
                </div>
                <a href="{{ route('modules.show', $module) }}" class="btn btn-sm" style="color:var(--text-muted);border:1px solid var(--card-border);"
                   onclick="return confirm('Quitter l\'examen ? Votre progression sera perdue.')">
                    <i class="bi bi-x-lg me-1"></i>Quitter
                </a>
            </div>

            {{-- Barre de progression --}}
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

                    <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:1rem;">
                        <i class="bi bi-tag me-1"></i>{{ $question['question_type'] }}
                    </div>

                    <h6 class="mb-3" style="font-size:1.1rem;">{{ $question['question_text'] }}</h6>

                    @if ($question['field_question'] === 'photo_path')
                        <div style="text-align:center;margin:1.5rem 0;">
                            <div style="display:inline-block;position:relative;max-width:300px;width:100%;">
                                <img src="{{ $question['question_content'] }}"
                                     alt="Question"
                                     style="width:100%;aspect-ratio:1/1;border-radius:12px;object-fit:cover;display:block;">
                                <button type="button" onclick="setZoomImage('{{ $question['question_content'] }}')"
                                        style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.65);border:none;border-radius:8px;padding:5px 10px;color:#fff;cursor:pointer;font-size:.85rem;">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                            </div>
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

            {{-- Formulaire des réponses --}}
            <form method="POST" action="{{ route('exam.submit', $module) }}">
                @csrf

                <div class="mb-4" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                    @foreach ($question['options'] as $index => $option)
                        <label for="option_{{ $index }}"
                               style="cursor:pointer;background:var(--card-bg);border:2px solid var(--card-border);
                                      border-radius:12px;padding:.5rem;display:flex;flex-direction:column;
                                      align-items:center;justify-content:center;gap:.5rem;min-height:80px;
                                      transition:.2s;position:relative;"
                               onclick="this.style.borderColor='var(--accent)'">

                            <input type="radio"
                                   id="option_{{ $index }}"
                                   name="answer"
                                   value="{{ $index }}"
                                   class="form-check-input"
                                   style="display:none;"
                                   required>

                            @if ($question['field_answer'] === 'photo_path')
                                <img src="{{ $option }}"
                                     alt="Option {{ $index + 1 }}"
                                     style="width:100%;aspect-ratio:1/1;border-radius:6px;object-fit:cover;display:block;">
                                <span onclick="event.preventDefault();event.stopPropagation();setZoomImage('{{ $option }}')"
                                      style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.7);border-radius:6px;padding:4px 8px;cursor:pointer;color:#fff;font-size:.8rem;z-index:10;line-height:1;">
                                    <i class="bi bi-zoom-in"></i>
                                </span>
                            @else
                                <span style="font-size:.9rem;text-align:center;color:var(--text-primary);word-break:break-word;">
                                    {{ $option }}
                                </span>
                            @endif

                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-lg me-1"></i>
                    @if ($question['current'] < $question['total'])
                        Question suivante
                    @else
                        Terminer l'examen
                    @endif
                </button>

            </form>

        </div>
    </div>

<div id="zoom-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;align-items:center;justify-content:center;" onclick="this.style.display='none'">
    <img id="zoom-img" src="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:10px;">
    <button onclick="document.getElementById('zoom-modal').style.display='none'" style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.15);border:none;border-radius:50%;width:36px;height:36px;color:#fff;font-size:1.1rem;cursor:pointer;">×</button>
</div>
<script>
function setZoomImage(src) {
    document.getElementById('zoom-img').src = src;
    document.getElementById('zoom-modal').style.display = 'flex';
}
</script>
</x-app-layout>
