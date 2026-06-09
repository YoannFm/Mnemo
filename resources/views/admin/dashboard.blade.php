<x-admin-layout>
    <x-slot name="pageTitle">Tableau de bord</x-slot>

    {{-- Ligne 1 : 4 stats principales --}}
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Utilisateurs</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-primary h3">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['users'] }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Modules (total)</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-primary h3">
                                <i class="bi bi-collection"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['modules'] }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Items</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-primary h3">
                                <i class="bi bi-card-list"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['items'] }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Tests realises</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-primary h3">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['tests'] }}</h1>
                </div>
            </div>
        </div>
    </div>

    {{-- Ligne 2 : modules publics, privés, avis --}}
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Modules publics</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-success h3">
                                <i class="bi bi-globe2"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['public_modules'] }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Modules prives</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-secondary h3">
                                <i class="bi bi-lock"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['private_modules'] }}</h1>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0">
                            <h5 class="card-title mb-0">Avis</h5>
                        </div>
                        <div class="col-auto">
                            <div class="stat text-warning h3">
                                <i class="bi bi-star"></i>
                            </div>
                        </div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $stats['ratings'] }}</h1>
                </div>
            </div>
        </div>
    </div>

    {{-- Ligne 3 : signalements en attente + mutes actifs --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="card border-danger">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="h2 text-danger mb-0"><i class="bi bi-flag"></i></div>
                    <div>
                        <h5 class="card-title mb-0">Signalements en attente</h5>
                        <span class="fs-2 fw-bold text-danger">{{ $stats['pending_reports'] }}</span>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-danger btn-sm">Voir</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card border-warning">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="h2 text-warning mb-0"><i class="bi bi-mic-mute"></i></div>
                    <div>
                        <h5 class="card-title mb-0">Mutes actifs</h5>
                        <span class="fs-2 fw-bold text-warning">{{ $stats['active_mutes'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Activite recente --}}
        <div class="col-12 col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Activite recente</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Utilisateur</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                    <tr>
                                        <td style="white-space:nowrap;font-size:.8rem;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                        <td style="font-size:.85rem;">
                                            @if($log->user)
                                                <a href="{{ route('admin.users.edit', $log->user) }}">{{ $log->user->name }}</a>
                                            @else
                                                <em class="text-muted">-</em>
                                            @endif
                                        </td>
                                        <td class="text-{{ $log->getActionFormat()['color'] }}" style="font-size:.85rem;">
                                            <i class="bi bi-{{ $log->getActionFormat()['icon'] }}"></i>
                                            {{ $log->getActionMessage() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Aucune activite.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.logs.index') }}">
                            <i class="bi bi-journal-text"></i> Voir tous les logs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Derniers inscrits --}}
        <div class="col-12 col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Derniers inscrits</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>Role</th>
                                    <th>Inscription</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestUsers as $user)
                                    <tr>
                                        <th scope="row">{{ $user->id }}</th>
                                        <td style="font-size:.85rem;">{{ $user->name }}</td>
                                        <td>
                                            @if ($user->is_admin)
                                                <span class="badge bg-primary">Admin</span>
                                            @else
                                                <span class="badge bg-secondary">Utilisateur</span>
                                            @endif
                                        </td>
                                        <td style="font-size:.8rem;white-space:nowrap;">{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.edit', $user) }}" title="Modifier" data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">Aucun utilisateur.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">
                        <a class="btn btn-sm btn-primary" href="{{ route('admin.users.index') }}">
                            <i class="bi bi-people"></i> Voir tous les utilisateurs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-6">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-bar-chart me-2"></i>Tests par jour (30 derniers jours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartTests" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-person-plus me-2"></i>Inscriptions par jour (30 derniers jours)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartUsers" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    (function() {
        var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        var gridColor = isDark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';
        var textColor = isDark ? '#aaa' : '#555';
        var labels = @json($chartLabels);

        function makeChart(id, data, label, color) {
            var ctx = document.getElementById(id);
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: data,
                        backgroundColor: color + '33',
                        borderColor: color,
                        borderWidth: 1.5,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: textColor, maxTicksLimit: 10 } },
                        y: { grid: { color: gridColor }, ticks: { color: textColor, precision: 0 }, beginAtZero: true }
                    }
                }
            });
        }

        makeChart('chartTests', @json($chartTests), 'Tests', '#6366f1');
        makeChart('chartUsers', @json($chartUsers), 'Inscriptions', '#22c55e');
    })();
    </script>
</x-admin-layout>
