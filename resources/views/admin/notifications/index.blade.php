<x-admin-layout>
    <x-slot name="pageTitle">Notifications</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
            <i class="bi bi-megaphone"></i> Envoyer une notification
        </a>
    </div>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Utilisateur</th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Lu</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $notification)
                            @php
                                $badgeClass = match($notification->type) {
                                    'success' => 'bg-success',
                                    'warning' => 'bg-warning text-dark',
                                    'danger'  => 'bg-danger',
                                    default   => 'bg-info',
                                };
                                $typeLabel = match($notification->type) {
                                    'success' => 'Succes',
                                    'warning' => 'Avertissement',
                                    'danger'  => 'Danger',
                                    default   => 'Information',
                                };
                            @endphp
                            <tr>
                                <td>{{ $notification->id }}</td>
                                <td>{{ $notification->user->name ?? '-' }}</td>
                                <td>{{ $notification->title }}</td>
                                <td><span class="badge {{ $badgeClass }}">{{ $typeLabel }}</span></td>
                                <td>
                                    @if($notification->read_at)
                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Oui</span>
                                    @else
                                        <span class="text-muted"><i class="bi bi-circle"></i> Non</span>
                                    @endif
                                </td>
                                <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}"
                                          onsubmit="return confirm('Supprimer cette notification ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Aucune notification.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</x-admin-layout>
