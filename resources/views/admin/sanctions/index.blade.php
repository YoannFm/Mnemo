<x-admin-layout>
    <x-slot name="pageTitle">Sanctions</x-slot>
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Historique des sanctions</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Admin</th>
                            <th>Utilisateur sanctionné</th>
                            <th>Type</th>
                            <th>Raison</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sanctions as $sanction)
                            <tr>
                                <td style="font-size:.8rem;white-space:nowrap;">{{ $sanction->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($sanction->admin)
                                        <a href="{{ route('admin.users.edit', $sanction->admin) }}">{{ $sanction->admin->name }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sanction->user)
                                        <a href="{{ route('admin.users.edit', $sanction->user) }}">{{ $sanction->user->name }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $types = ['warning' => ['label' => 'Avertissement', 'class' => 'bg-warning text-dark'], 'ban' => ['label' => 'Bannissement', 'class' => 'bg-danger'], 'other' => ['label' => 'Autre', 'class' => 'bg-secondary']];
                                        $t = $types[$sanction->type] ?? ['label' => $sanction->type, 'class' => 'bg-secondary'];
                                    @endphp
                                    <span class="badge {{ $t['class'] }}">{{ $t['label'] }}</span>
                                </td>
                                <td style="font-size:.8rem;max-width:250px;">{{ $sanction->reason }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucune sanction.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $sanctions->links() }}
        </div>
    </div>
</x-admin-layout>
