<x-admin-layout>
    <x-slot name="pageTitle">Tableau de bord</x-slot>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ $stats['users'] }}</div>
                    <div class="stat-label">Utilisateurs</div>
                </div>
                <i class="bi bi-people"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ $stats['modules'] }}</div>
                    <div class="stat-label">Modules</div>
                </div>
                <i class="bi bi-collection"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ $stats['items'] }}</div>
                    <div class="stat-label">Items</div>
                </div>
                <i class="bi bi-card-list"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-value">{{ $stats['tests'] }}</div>
                    <div class="stat-label">Tests réalisés</div>
                </div>
                <i class="bi bi-clipboard-check"></i>
            </div>
        </div>
    </div>

    {{-- Latest users --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people me-2"></i>Derniers inscrits</span>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Voir tous</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Admin</th>
                        <th>Inscription</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($latestUsers as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td class="text-muted">{{ $user->email }}</td>
                            <td>
                                @if ($user->is_admin)
                                    <span class="badge-admin">Admin</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:.8rem;">{{ $user->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Aucun utilisateur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
