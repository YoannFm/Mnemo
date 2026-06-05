<x-app-layout>
    <x-slot name="pageTitle">Résultat - {{ $module->title }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card text-center mb-4 p-4">
                <div style="font-size:3.5rem;font-weight:800;color:{{ $percentage >= 80 ? 'var(--success-color)' : ($percentage >= 60 ? 'var(--accent)' : ($percentage >= 40 ? '#f59e0b' : 'var(--danger-color)')) }};">
                    {{ $percentage }}%
                </div>
                <div style="font-size:1.1rem;color:var(--text-muted);">{{ $score }} / {{ $total }} réponses correctes</div>
                <div style="font-size:2rem;margin-top:.5rem;">
                    @if($percentage >= 80) Excellent ! @elseif($percentage >= 60) Bien @elseif($percentage >= 40) A améliorer @else Insuffisant @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><strong>Détail des réponses</strong></div>
                <div class="card-body p-0">
                    @foreach($answers as $i => $a)
                    <div class="p-3 d-flex gap-3 align-items-start {{ !$loop->last ? 'border-bottom' : '' }}" style="border-color:var(--card-border)!important;">
                        <span class="badge {{ $a['is_correct'] ? 'bg-success' : 'bg-danger' }}" style="min-width:28px;">{{ $i + 1 }}</span>
                        <div class="flex-grow-1" style="font-size:.85rem;">
                            <div class="text-muted mb-1">{{ $a['question_text'] }}</div>
                            @if(!$a['is_correct'])
                                <div style="color:var(--danger-color);">
                                    <i class="bi bi-x-circle me-1"></i>Votre réponse :
                                    @if($a['field_answer'] === 'photo_path')
                                        <img src="{{ $a['user_answer'] }}" style="height:40px;border-radius:4px;vertical-align:middle;">
                                    @else
                                        {{ $a['user_answer'] }}
                                    @endif
                                </div>
                                <div style="color:var(--success-color);">
                                    <i class="bi bi-check-circle me-1"></i>Bonne réponse :
                                    @if($a['field_answer'] === 'photo_path')
                                        <img src="{{ $a['correct_answer'] }}" style="height:40px;border-radius:4px;vertical-align:middle;">
                                    @else
                                        {{ $a['correct_answer'] }}
                                    @endif
                                </div>
                            @else
                                <div style="color:var(--success-color);"><i class="bi bi-check-circle me-1"></i>Correct !</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('exam.show', $module) }}" class="btn btn-primary">
                    <i class="bi bi-arrow-repeat me-1"></i>Recommencer
                </a>
                <a href="{{ route('modules.show', $module) }}" class="btn" style="color:var(--text-muted);border:1px solid var(--card-border);">
                    Retour au module
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
