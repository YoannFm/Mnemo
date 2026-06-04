<x-admin-layout>
    <x-slot name="pageTitle">Signalements de modules</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Signalements de modules</h5></div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Signalé par</th>
                            <th>Module signalé</th>
                            <th>Auteur du module</th>
                            <th>Motif</th>
                            <th>Note</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $report->id }}</td>
                                <td>
                                    @if ($report->user)
                                        <a href="{{ route('admin.users.edit', $report->user) }}">{{ $report->user->name }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($report->module)
                                        <a href="{{ route('modules.show', $report->module) }}" target="_blank">
                                            {{ \Illuminate\Support\Str::limit($report->module->title, 40) }}
                                        </a>
                                    @else
                                        <em class="text-muted">[Module supprimé]</em>
                                    @endif
                                </td>
                                <td>
                                    @if ($report->module && $report->module->owner)
                                        <a href="{{ route('admin.users.edit', $report->module->owner) }}">{{ $report->module->owner->name }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $motifs = [
                                            'spam'          => 'Spam',
                                            'inappropriate' => 'Inapproprié',
                                            'copyright'     => 'Droits d\'auteur',
                                            'other'         => 'Autre',
                                        ];
                                    @endphp
                                    <span class="badge bg-warning text-dark">{{ $motifs[$report->reason] ?? $report->reason }}</span>
                                </td>
                                <td style="font-size:.8rem;max-width:200px;">{{ $report->note ?: '-' }}</td>
                                <td>
                                    @if ($report->status === 'pending')
                                        <span class="badge bg-danger">En attente</span>
                                    @elseif ($report->status === 'treated')
                                        <span class="badge bg-success">Traité</span>
                                    @else
                                        <span class="badge bg-secondary">Rejeté</span>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        @if ($report->status === 'pending')
                                            <form method="POST" action="{{ route('admin.module-reports.treated', $report) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Marquer traité">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.module-reports.rejected', $report) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Rejeter">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if ($report->module && $report->module->owner)
                                            <a href="{{ route('admin.users.edit', $report->module->owner) }}"
                                               class="btn btn-sm btn-outline-danger" title="Gérer l'auteur du module">
                                                <i class="bi bi-person-x"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-muted">Aucun signalement de module.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $reports->links() }}
        </div>
    </div>
</x-admin-layout>
