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
                            <th>Actions</th>
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
                                <td>
                                    @if($sanction->type === 'mute' && $sanction->user)
                                        @php
                                            $isMuted = \App\Models\Mute::where('user_id', $sanction->user_id)
                                                ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                                                ->exists();
                                        @endphp
                                        @if($isMuted)
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                    data-bs-toggle="modal" data-bs-target="#unmuteModal{{ $sanction->id }}">
                                                <i class="bi bi-mic-fill"></i> Démuter
                                            </button>
                                            <div class="modal fade" id="unmuteModal{{ $sanction->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('admin.sanctions.unmute', $sanction) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Démuter {{ $sanction->user->name }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Raison <span class="text-muted fw-normal">(optionnel)</span></label>
                                                                    <textarea name="reason" class="form-control" rows="3" maxlength="500"
                                                                              placeholder="Motif du démute..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                                <button type="submit" class="btn btn-success">Confirmer le démute</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted" style="font-size:.8rem;">Mute expiré</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucune sanction.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $sanctions->links() }}
        </div>
    </div>
</x-admin-layout>
