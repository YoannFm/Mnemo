<x-admin-layout>
    <x-slot name="pageTitle">Ajouter un utilisateur</x-slot>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-9">
                        <div class="mb-3">
                            <label class="form-label" for="nameInput">Nom *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="nameInput" name="name" value="{{ old('name') }}" required>
                            @error('name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="emailInput">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="emailInput" name="email" value="{{ old('email') }}" required>
                            @error('email')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                        </div>
                    </div>

                    <div class="col-md-3 text-center d-flex align-items-center justify-content-center">
                        <div style="width:80px;height:80px;border-radius:50%;background:#266fd9;display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;font-weight:700;">
                            <i class="bi bi-person-plus"></i>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="passwordInput">Mot de passe *</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                           id="passwordInput" name="password" required>
                    @error('password')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="passwordConfirm">Confirmer le mot de passe *</label>
                    <input type="password" class="form-control" id="passwordConfirm" name="password_confirmation" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="roleSelect">Role</label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="roleSelect" name="role_id">
                        <option value="">- Aucun role -</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}@if($role->is_admin_role) (Admin)@endif
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Creer
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Annuler</a>
            </form>
        </div>
    </div>
</x-admin-layout>
