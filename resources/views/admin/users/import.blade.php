<x-admin-layout>
    <x-slot name="pageTitle">Importer des utilisateurs</x-slot>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Importer depuis un fichier CSV</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Format CSV attendu (avec ligne d'en-tete) :
            </p>
            <pre class="bg-light p-3 rounded mb-4"><code>name,email,password,role_id
Jean Dupont,jean@example.com,motdepasse123,2</code></pre>

            <ul class="mb-4 text-muted small">
                <li>Le mot de passe sera hache automatiquement.</li>
                <li>Si l'email existe deja, la ligne sera ignoree (pas d'erreur fatale).</li>
                <li><code>role_id</code> doit correspondre a un role existant (laisser vide ou mettre 0 pour le role par defaut).</li>
            </ul>

            <form action="{{ route('admin.users.import.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="csvFile">Fichier CSV *</label>
                    <input type="file" class="form-control @error('csv_file') is-invalid @enderror"
                           id="csvFile" name="csv_file" accept=".csv,.txt" required>
                    @error('csv_file')
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Importer
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>
