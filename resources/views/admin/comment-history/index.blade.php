<x-admin-layout>
    <x-slot name="pageTitle">Historique des commentaires</x-slot>

    {{-- Avis sur les modules --}}
    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Avis sur les modules</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Module</th>
                            <th>Note</th>
                            <th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ratings as $rating)
                            <tr>
                                <td style="font-size:.8rem;white-space:nowrap;">{{ $rating->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($rating->user)
                                        <a href="{{ route('admin.users.edit', $rating->user) }}">{{ $rating->user->name }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rating->module)
                                        <a href="{{ route('modules.show', $rating->module) }}" target="_blank">
                                            {{ Str::limit($rating->module->title, 40) }}
                                        </a>
                                    @else
                                        <span class="text-muted">Module supprimé</span>
                                    @endif
                                </td>
                                <td>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $rating->rating ? 'bi-star-fill' : 'bi-star' }}" style="color:#fbbf24;font-size:.85rem;"></i>
                                    @endfor
                                </td>
                                <td style="font-size:.8rem;max-width:300px;">{{ Str::limit($rating->comment, 150) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Aucun avis avec commentaire.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $ratings->links() }}
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header"><h5 class="card-title mb-0">Historique des modifications et suppressions</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Article</th>
                            <th>Texte avant</th>
                            <th>Texte apres</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td style="font-size:.8rem;white-space:nowrap;">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($entry->postComment && $entry->postComment->user)
                                        <a href="{{ route('admin.users.edit', $entry->postComment->user) }}">
                                            {{ $entry->postComment->user->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($entry->postComment && $entry->postComment->post)
                                        <a href="{{ route('posts.show', $entry->postComment->post) }}" target="_blank">
                                            {{ Str::limit($entry->postComment->post->title, 30) }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td style="font-size:.8rem;max-width:200px;">
                                    {{ Str::limit($entry->content, 100) }}
                                </td>
                                <td style="font-size:.8rem;max-width:200px;">
                                    @if($entry->postComment && $entry->postComment->is_deleted)
                                        <span class="text-muted fst-italic">[Supprimé]</span>
                                    @elseif($entry->postComment)
                                        {{ Str::limit($entry->postComment->content, 100) }}
                                    @else
                                        <span class="text-muted fst-italic">[Supprimé]</span>
                                    @endif
                                </td>
                                <td>
                                    @if($entry->postComment && $entry->postComment->is_deleted)
                                        <span class="badge bg-danger">Supprimé</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Modifié</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucun historique.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $entries->links() }}
        </div>
    </div>
</x-admin-layout>
