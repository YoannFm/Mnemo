<x-app-layout>
    <x-slot name="pageTitle">Notifications</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-bell-fill me-2"></i>Mes notifications</h2>
        @if($notifications->total() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-check-all"></i> Tout marquer comme lu
                </button>
            </form>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-bell-slash" style="font-size:3rem;color:var(--text-muted);"></i>
                <p class="mt-3" style="color:var(--text-muted);">Aucune notification pour le moment.</p>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach($notifications as $notification)
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
                <div class="card {{ $notification->isRead() ? 'opacity-75' : '' }}"
                     style="{{ !$notification->isRead() ? 'border-color:var(--accent);' : '' }}">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge {{ $badgeClass }}">{{ $typeLabel }}</span>
                                    @if(!$notification->isRead())
                                        <span class="badge bg-primary" style="font-size:.6rem;">Nouveau</span>
                                    @endif
                                    <small style="color:var(--text-muted);">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </small>
                                </div>
                                <div class="fw-bold">{{ $notification->title }}</div>
                                @if($notification->message)
                                    <div class="mt-1" style="color:var(--text-muted);font-size:.9rem;">
                                        {!! $notification->message !!}
                                    </div>
                                @endif
                            </div>
                            @if(!$notification->isRead())
                                <form method="POST" action="{{ route('notifications.read', $notification) }}" class="flex-shrink-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm" title="Marquer comme lu">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </form>
                            @else
                                <i class="bi bi-check-all flex-shrink-0" style="color:var(--text-muted);"></i>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</x-app-layout>
