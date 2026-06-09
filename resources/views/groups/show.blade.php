<x-app-layout>
    <x-slot name="pageTitle">{{ $group->name }}</x-slot>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb" style="font-size:.85rem;">
            <li class="breadcrumb-item"><a href="{{ route('groups.index') }}" style="color:var(--accent);">Mes groupes</a></li>
            <li class="breadcrumb-item active" style="color:var(--text-muted);">{{ $group->name }}</li>
        </ol>
    </nav>

    @if (session('success'))
        <div class="alert alert-success mb-3" style="font-size:.875rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-warning mb-3" style="font-size:.875rem;">{{ session('error') }}</div>
    @endif

    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h4 class="mb-1">{{ $group->name }}</h4>
            @if ($group->description)
                <p style="color:var(--text-muted);font-size:.875rem;margin:0;">{{ $group->description }}</p>
            @endif
        </div>
    </div>

    {{-- Ajouter un membre --}}
    <div class="card mb-4">
        <div class="card-body p-3">
            <form method="POST" action="{{ route('groups.members.add', $group) }}" class="d-flex gap-2 flex-wrap align-items-end">
                @csrf
                <div style="flex:1;min-width:200px;">
                    <label class="form-label" for="member_email" style="font-size:.85rem;">Ajouter un membre par e-mail</label>
                    <input type="email" id="member_email" name="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                           placeholder="adresse@email.com" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-person-plus me-1"></i>Ajouter
                </button>
            </form>
        </div>
    </div>

    {{-- Liste des membres --}}
    <div class="card">
        <div class="card-body p-0">
            <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--card-border);">
                <h6 class="mb-0"><i class="bi bi-people me-1"></i>{{ $group->members->count() }} membre{{ $group->members->count() > 1 ? 's' : '' }}</h6>
            </div>
            @if ($group->members->isEmpty())
                <div class="text-center py-5" style="color:var(--text-muted);">
                    <i class="bi bi-inbox" style="font-size:2rem;"></i>
                    <p class="mt-2 mb-0" style="font-size:.9rem;">Aucun membre. Ajoutez des élèves par e-mail.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.875rem;">
                        <thead>
                            <tr style="color:var(--text-muted);">
                                <th style="padding:.75rem 1.25rem;">Nom</th>
                                <th>E-mail</th>
                                <th>Ajouté le</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group->members as $member)
                                <tr>
                                    <td style="padding:.75rem 1.25rem;font-weight:500;">{{ $member->name }}</td>
                                    <td style="color:var(--text-muted);">{{ $member->email }}</td>
                                    <td style="color:var(--text-muted);font-size:.8rem;">
                                        {{ $member->pivot->joined_at ? \Carbon\Carbon::parse($member->pivot->joined_at)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('groups.members.remove', [$group, $member]) }}"
                                              onsubmit="return confirm('Retirer {{ $member->name }} du groupe ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm" style="color:#ef4444;border:1px solid var(--card-border);font-size:.78rem;">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</x-app-layout>
