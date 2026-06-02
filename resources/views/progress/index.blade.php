<x-app-layout>
    <x-slot name="pageTitle">Ma progression</x-slot>

    <div class="mb-4">
        <h4 class="mb-1">Ma progression</h4>
        <p style="color:var(--text-muted);font-size:.875rem;">
            Suivez votre avancement et votre historique de tests.
        </p>
    </div>

    {{-- ── Statistiques globales ── --}}
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-3">
            <div class="card p-3 text-center">
                <div style="font-size:2rem;font-weight:700;color:var(--success-color);">
                    {{ $stats['total_mastered'] }}
                </div>
                <div style="font-size:.78rem;color:var(--text-muted);">Items maîtrisés</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card p-3 text-center">
                <div style="font-size:2rem;font-weight:700;color:var(--accent);">
                    {{ $stats['total_practiced'] }}
                </div>
                <div style="font-size:.78rem;color:var(--text-muted);">Items pratiqués</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card p-3 text-center">
                <div style="font-size:2rem;font-weight:700;color:#fbbf24;">
                    {{ $stats['total_tests'] }}
                </div>
                <div style="font-size:.78rem;color:var(--text-muted);">Tests effectués</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card p-3 text-center">
                <div style="font-size:2rem;font-weight:700;color:#a855f7;">
                    {{ $stats['avg_score'] }}%
                </div>
                <div style="font-size:.78rem;color:var(--text-muted);">Score moyen</div>
            </div>
        </div>

    </div>

    {{-- ── Progression par module ── --}}
    <h5 class="mb-3">Progression par module</h5>

    @if ($modules->isEmpty())
        <div class="card text-center py-4 mb-4">
            <p style="color:var(--text-muted);margin:0;">Aucun module créé.</p>
        </div>
    @else
        <div class="row g-3 mb-4">
            @foreach ($modules as $module)
                <div class="col-12 col-md-6">
                    <div class="card p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-semibold">{{ $module->title }}</div>
                                <div style="font-size:.78rem;color:var(--text-muted);">
                                    {{ $module->mastered_count }} / {{ $module->items_count }} maîtrisés
                                </div>
                            </div>
                            <span style="font-size:1.2rem;font-weight:700;
                                         color:{{ $module->percent >= 80 ? 'var(--success-color)' : ($module->percent >= 40 ? 'var(--accent)' : '#ef4444') }};">
                                {{ $module->percent }}%
                            </span>
                        </div>
                        {{-- Barre de progression --}}
                        <div class="progress" style="height:8px;background:var(--card-border);">
                            <div class="progress-bar" role="progressbar"
                                 style="width:{{ $module->percent }}%;
                                        background:{{ $module->percent >= 80 ? 'var(--success-color)' : ($module->percent >= 40 ? 'var(--accent)' : '#ef4444') }};">
                            </div>
                        </div>
                        {{-- Actions --}}
                        @if ($module->items_count >= 4)
                            <div class="d-flex gap-2 mt-3">
                                <a href="{{ route('anki.show', $module) }}"
                                   class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="bi bi-arrow-repeat me-1"></i>S'entraîner (Anki)
                                </a>
                                <a href="{{ route('test.show', $module) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-lightning-charge"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Historique des scores ── --}}
    <h5 class="mb-3">Historique des tests</h5>

    @if ($scores->isEmpty())
        <div class="card text-center py-4">
            <p style="color:var(--text-muted);margin:0;">Aucun test effectué pour l'instant.</p>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" style="color:var(--text-primary);">
                        <thead style="background:var(--card-border);font-size:.8rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">
                            <tr>
                                <th class="px-3 py-2">Module</th>
                                <th class="px-3 py-2">Score</th>
                                <th class="px-3 py-2">Pourcentage</th>
                                <th class="px-3 py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scores as $score)
                                <tr style="border-color:var(--card-border);">
                                    <td class="px-3 py-2" style="font-size:.875rem;">
                                        {{ $score->module?->title ?? 'Module supprimé' }}
                                    </td>
                                    <td class="px-3 py-2" style="font-size:.875rem;">
                                        {{ $score->score }} / {{ $score->total }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{-- Badge couleur selon le pourcentage --}}
                                        @php $pct = $score->percentage; @endphp
                                        <span style="font-weight:600;
                                                     color:{{ $pct >= 80 ? 'var(--success-color)' : ($pct >= 50 ? 'var(--accent)' : '#ef4444') }};">
                                            {{ $pct }}%
                                        </span>
                                    </td>
                                    <td class="px-3 py-2" style="font-size:.8rem;color:var(--text-muted);">
                                        {{ $score->created_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $scores->links('pagination::bootstrap-5') }}
        </div>
    @endif

</x-app-layout>
